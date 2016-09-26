<?php

namespace Lumi\SecurePostWithLink\Controllers;

use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;

class Translation implements ProviderInterface {
	use SingletonTrait;

	/** @var Config */
	private $config;

	/**
	 * Register hooks and inject deps
	 */
	public function boot() {

		$this->config = Config::getInstance();

		add_action( 'plugins_loaded', [ $this, 'loadTextdomain' ] );

	}

	/**
	 * Load WP textdomain
	 */
	public function loadTextdomain() {

		load_plugin_textdomain( $this->config->get( 'textdomain' ), false, $this->config->get( 'translations_dir' ) );

	}

}