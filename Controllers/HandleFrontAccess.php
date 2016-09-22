<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;

class HandleFrontAccess implements ProviderInterface {
	use SingletonTrait;

	/** @var Config */
	private $config;

	/**
	 * Register WP hooks
	 */
	public function boot() {

		$this->config = Config::getInstance();

		add_filter( 'init', [ $this, 'registerHooksForPostTypes' ] );

		//TODO: flush rules on plugin activation

	}

	/**
	 * Loop through allowed post types and register action to add RW rules for each
	 *
	 * Must be run after init to allow config injection
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
	 * @return array Rules with added ones
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

			//Generate rule for token after last slash (we are expecting /?$ at the end)
			$new_rule = substr( $rule, 0, -3) . '\/([a-z|A-Z|0-9]+)\/?$';

			//Get greatest matches number
			preg_match_all( '/\$(\d+)/', $redirect, $matches );
			if( !isset( $matches[1] ) ) continue;

			$numbers = $matches[1];
			$numbers = array_map( function( $number ) {
				return (int)$number;
			}, $numbers );

			$new_redirect = $redirect . '&secure_link_token=$' . (string)( max( $numbers ) + 1 );

			add_rewrite_rule( $new_rule, $new_redirect );
		}

		return $rules;

	}

}