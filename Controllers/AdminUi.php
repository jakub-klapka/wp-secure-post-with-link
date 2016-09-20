<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;


class AdminUi implements ProviderInterface {
	use SingletonTrait;

	private $config;

	/**
	 * Register WP actions and inject deps
	 */
	public function boot() {

		$this->config = Config::getInstance();

		add_action( 'current_screen', [ $this, 'maybeEnqueueScript' ] );

		add_filter( 'get_sample_permalink', [ $this, 'modifySamplePermalinkOnSecuredPosts' ], 10, 5 );

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
			$this->config->get( 'assets_url' ) . '/admin-ui.js',
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
			'current_post_status' => $post->post_status
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
	 * @param string  $permalink Sample permalink.
	 *                  Eg.: $permalink = {array} [2]
	 *                          0 = "https://localhost/linnette_2015/wp/blog/%pagename%/"
	 *                          1 = "tomas-hajzler-krest-knizek"
	 * @param int     $post_id   Post ID.
	 * @param string  $title     Post title.
	 * @param string  $name      Post name (slug).
	 * @param \WP_Post $post      Post object.
	 * @wp-filter get_sample_permalink
	 *
	 * @return string
	 */
	public function modifySamplePermalinkOnSecuredPosts( $permalink, $post_id, $title, $name, $post ) {

		if( $post->post_status !== 'secured' ) return $permalink;

		$token = get_post_meta( $post->ID, $this->config->get( 'secured_meta_name' ), true );

		if( $token != false ) {
			$permalink[ 0 ] = $permalink[ 0 ] . $token . '/';
		}

		return $permalink;

	}

}