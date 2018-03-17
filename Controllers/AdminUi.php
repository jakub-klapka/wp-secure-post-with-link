<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;


class AdminUi implements ProviderInterface {
	use SingletonTrait;

	/** @var Config $config */
	private $config;

	/**
	 * Register WP actions and inject deps
	 */
	public function boot() {

		$this->config = Config::getInstance();

		add_action( 'current_screen', [ $this, 'maybeEnqueueScript' ] );

		// Custom post types
		add_filter( 'post_type_link', [ $this, 'modifyPermalinkOnSecuredPosts' ], 10, 2 );
		// Post
		add_filter( 'post_link', [ $this, 'modifyPermalinkOnSecuredPosts' ], 10, 2 );
		// Page
		add_filter( 'page_link', [ $this, 'modifyPermalinkOnSecuredPage' ], 10, 2 );

		add_filter( 'display_post_states', [ $this, 'addPostStateToPostsListing' ], 10, 2 );

	}

	/**
	 * Enqueue Admin UI javascript
	 * Only, if we are on edit screen for supported post type
	 *
	 * @wp-action current_screen
	 */
	public function maybeEnqueueScript() {

		if( $this->shouldEnableUiScript() ) {

			add_action( 'admin_enqueue_scripts', [ $this, 'registerScriptsAndStyles' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'pushDataToScript' ] );

		}

	}

	/**
	 * Register and enqueue scripts and styles
	 *
	 * @wp-action admin_enqueue_scripts
	 */
	public function registerScriptsAndStyles() {

		wp_register_script( 'secure-post-with-link--admin-ui',
			$this->config->get( 'assets_url' ) . '/admin-ui.min.js',
			[ 'jquery' ],
			$this->config->get( 'static_version' ),
			true );

		wp_enqueue_script( 'secure-post-with-link--admin-ui');

	}

	/**
	 * Push data to frontend for script
	 *
	 * JS object: securePostWithLink {
	 *      'enable_ui',
	 *		'current_post_status'
	 * }
	 *
	 * @wp-action admin_enqueue_scripts
	 */
	public function pushDataToScript() {

		$enable_ui = $this->shouldEnableUiScript();
		$post = get_post();

		$data = [
			'enable_ui' => $enable_ui,
			'current_post_status' => $post->post_status,
			'lang_secured_link' => __( 'Secured Link', $this->config->get( 'textdomain' ) )
		];

		wp_localize_script( 'secure-post-with-link--admin-ui', 'securePostWithLink', $data );

	}

	/**
	 * Determine, if we want admin UI modifications on current screen
	 *
	 * @return bool
	 */
	private function shouldEnableUiScript() {

		$current_screen = get_current_screen();

		if( $current_screen->base === 'post'
		    && in_array( $current_screen->post_type, $this->config->get( 'allowed_post_types' ) ) ) {
			return true;
		};

		return false;

	}

	/**
	 * Add token to all get_permalink calls
	 * Get_permalink should be called only on places, where secured post will be visible anyway
	 *
	 * @param string $link
	 * @param \WP_Post $wp_post
	 *
	 * @wp-filter post_type_link
	 * @wp-filter post_link
	 *
	 * @return string
	 */
	public function modifyPermalinkOnSecuredPosts( $link, $wp_post ) {

		if( $wp_post->post_status !== 'secured' ) return $link;

		$token = get_post_meta( $wp_post->ID, $this->config->get( 'secured_meta_name' ), true );

		return $link . $this->config->get( 'url_identifier' ) . '/' . $token . '/';

	}

	/**
	 * Since internal Page post type calls page_link with Post ID (as opposed to all other post types), fetch WP_Post first
	 *
	 * @param string $link
	 * @param int $page_id
	 *
	 * @wp-filter page_link
	 *
	 * @return string
	 */
	public function modifyPermalinkOnSecuredPage( $link, $page_id ) {

		$wp_post = get_post( $page_id );

		if( $wp_post === null ) {
			return $link;
		}

		return $this->modifyPermalinkOnSecuredPosts( $link, $wp_post );

	}

	/**
	 * Add states (identifiers after post name) to secured posts on admin screens
	 *
	 * @param array $post_states Array of all states for current post
	 * @param \WP_Post $post Current post
	 *
	 * @wp-filter display_post_states
	 *
	 * @return array
	 */
	public function addPostStateToPostsListing( $post_states, $post ) {
		if( $post->post_status === 'secured' ) {
			$post_states[] = __( 'Secured with link', $this->config->get( 'textdomain' ) );
		}
		return $post_states;
	}

}