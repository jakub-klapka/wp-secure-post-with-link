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

		//TODO: flush rules on plugin activation

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

}