<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\SingletonTrait;

class AdminUi {
	use SingletonTrait;

	private $config;

	/**
	 * AdminUi constructor.
	 * Register WP actions
	 */
	public function __construct() {

		$this->config = Config::getInstance();

		add_action( 'current_screen', [ $this, 'maybeEnqueueScript' ] );

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

}