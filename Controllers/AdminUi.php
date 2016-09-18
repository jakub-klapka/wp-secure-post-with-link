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

		add_action( 'admin_enqueue_scripts', [ $this, 'registerScriptsAndStyles' ] );

	}

	/**
	 * Register plugins scripts and styles
	 */
	public function registerScriptsAndStyles() {

		wp_register_script( 'secure-post-with-link--admin-ui',
			$this->config->get( 'assets_url' ) . '/admin-ui.js',
			[ 'jquery' ],
			$this->config->get( 'static_version' ),
			true );

		//TODO: to view
		wp_enqueue_script( 'secure-post-with-link--admin-ui');
	}

}