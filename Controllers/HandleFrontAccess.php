<?php

namespace Lumi\SecurePostWithLink\Controllers;

use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;

class HandleFrontAccess implements ProviderInterface {
	use SingletonTrait;

	/** @var Config $config */
	private $config;

	/**
	 * Register WP hooks and inject dependencies
	 */
	public function boot() {

		$this->config = Config::getInstance();

		add_action( 'init', [ $this, 'registerHooksForPostTypes' ] );
		
		add_action( 'init', [ $this, 'registerRewriteTag' ] );

		/**
		 * Battleplan:
		 * 1. If there is matched access token via rewrite, inject post_status = secured into query vars
		 *      As WP logic dictates, if we are querying single post and specific post_status, it won't fire 404.
		 *      That way, we can use main WP Query to fetch single secured post, but we won't get secured posts
		 *      in any other query - including archives, sitemaps etc.
		 * 2. After fetching single secured post, we have to check for valid token and abort, if it's not valid
		 */

		add_action( 'parse_request', [ $this, 'maybeAddPostStatusQueryVarToRequest' ] );

		add_action( 'wp', [ $this, 'checkForValidAccessToken' ] );
		
		register_activation_hook( $this->config->get( 'main_plugin_file_path' ), [ $this, 'flushRewriteRules' ] );
		register_deactivation_hook( $this->config->get( 'main_plugin_file_path' ), [ $this, 'flushRewriteRules' ] );

	}

	/**
	 * Loop through allowed post types and register action to add RW rules for each
	 *
	 * Have to be executed after init to allow config injection
	 *
	 * @wp-action init
	 */
	public function registerHooksForPostTypes() {

		foreach( $this->config->get( 'allowed_post_types' ) as $post_type ) {
			add_filter( "{$post_type}_rewrite_rules", [ $this, 'registerRewriteRules' ] );
		}

	}

	/**
	 * Register new RW rules for post type
	 *
	 * For each post type, get present rewrite rules, filter out attachments, feeds etc., figure
	 * out new rewrite rule and access token matches variable for new endpoint - access token
	 * at the end of URL. Then register it to bottom of rules, so attachments still be matched
	 * before our new rule.
	 *
	 * @wp-action {$post_type}_rewrite_rules
	 *
	 * @param array $rules
	 *
	 * @return array Rules with new ones
	 */
	public function registerRewriteRules( $rules ) {

		$exclude_terms = [ 'attachment=', 'feed=', 'embed=', 'tb=' ];
		$filtered_rules = array_filter( $rules, function( $rule ) use ( $exclude_terms ) {
			foreach( $exclude_terms as $term ) {
				if( strpos( $rule, $term ) !== false ) return false;
			}
			return true;
		} );

		foreach ( $filtered_rules as $rule => $redirect ) {

			$generated_rule = $this->generateNewRewriteRuleBasedOnCptRule( $rule, $redirect );

			if( $generated_rule !== false ) {
				add_rewrite_rule( $generated_rule[ 'new_rule' ], $generated_rule[ 'new_redirect' ] );
			}

		}

		return $rules;

	}

	/**
	 * Generate rewrite rules with access token from existing rule, which was auto-created
	 * with any post/custom post type
	 *
	 * @param string $rule Regexp of url match
	 * @param string $redirect URL with get parameters after WP rewriting
	 *
	 * @return bool|array False on invalid rule
	 * @return array {
	 *      @type string $new_rule New regexp for URL matching
	 *      @type string $new_redurect New URL witch matching groups
	 * }
	 */
	private function generateNewRewriteRuleBasedOnCptRule( $rule, $redirect ) {

		//Generate rule for token after last slash (we are expecting /?$ at the end)
		$new_rule = substr( $rule, 0, -3 ) . '\/' . $this->config->get( 'url_identifier' ) . '\/([a-z|A-Z|0-9]+)\/?$';

		//Get greatest matches number
		preg_match_all( '/\$matches\[(\d+)\]/', $redirect, $matches );

		if( !isset( $matches[1] ) ) return false; //There has to be at least one existing match pair for CPT

		$numbers = $matches[1];
		$numbers = array_map( function( $number ) {
			return (int)$number;
		}, $numbers );

		$new_matches_number = (string)(max( $numbers ) + 1);
		$new_redirect = $redirect . "&secure_link_token=\$matches[$new_matches_number]";

		return [
			'new_rule' => $new_rule,
			'new_redirect' => $new_redirect
		];

	}

	/**
	 * Register WP query string
	 *
	 * @wp-action init
	 */
	public function registerRewriteTag() {
		
		add_rewrite_tag( '%secure_link_token%', '([^&]+)' );
		
	}

	/**
	 * Add post_status query to WP Query vars if we are accessing secured post
	 *
	 * @param \WP $wp Passed by reference
	 */
	public function maybeAddPostStatusQueryVarToRequest( $wp ) {

		if( isset( $wp->query_vars[ 'secure_link_token' ] )
		    && !empty( $wp->query_vars[ 'secure_link_token' ] )
			&& in_array( $wp->query_vars[ 'post_type' ], $this->config->get( 'allowed_post_types' ) ) ) {

			$wp->query_vars[ 'post_status' ] = 'secured';

		}

	}

	/**
	 * Check for valid token on secured posts and maybe die
	 *
	 * We are applying this logic to all queries - probably on all queries for secured post type, we wnat this
	 * check anyway.
	 *
	 * @param \WP $wp WP Object Passed by reference
	 */
	public function checkForValidAccessToken( $wp ) {
		global $wp_query;

		if( $wp_query->is_singular() && $wp_query->post->post_status === 'secured' ) {

			$post_access_token = get_post_meta( $wp_query->post->ID, $this->config->get( 'secured_meta_name' ), true );

			if( !isset( $wp->query_vars['secure_link_token'] )
			    || $wp->query_vars['secure_link_token'] !== $post_access_token ) {

				wp_die( __( 'Invalid access', $this->config->get( 'textdomain' ) ), null, [ 'response' => 401 ] );

			}

		}

	}

	/**
	 * Flush WP transient for rewrite rules on plugin activation/deactivation
	 *
	 * @wp-action register_activation_hook
	 * @wp-action register_deactivation_hook
	 */
	public function flushRewriteRules() {
		flush_rewrite_rules();
	}

}