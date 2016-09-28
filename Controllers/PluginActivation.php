<?php

namespace Lumi\SecurePostWithLink\Controllers;

use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;

class PluginActivation implements ProviderInterface {
	use SingletonTrait;

	/** @var Config */
	private $config;

	/**
	 * Method called on plugin load
	 * Use only for attaching WP hooks
	 */
	public function boot() {

		$this->config = Config::getInstance();

		register_activation_hook( $this->config->get( 'main_plugin_file_path' ), [ $this, 'registerForRewriteFlush' ] );

		register_deactivation_hook( $this->config->get( 'main_plugin_file_path' ), [ $this, 'flushRewriteOnDeactivation' ] );

		add_action( 'init', [ $this, 'checkForRewriteFlush' ] );

	}

	/**
	 * On plugin activation, register transient, which would indicate new plugin activation on next load
	 *
	 * @wp-action register_activation_hook
	 */
	public function registerForRewriteFlush() {
		set_transient( 'lumi.secure-post-with-link.flush_rewrite', true, DAY_IN_SECONDS );
	}

	/**
	 * If plugin was just activated (based on transient), flush rewrite rules
	 *
	 * @wp-action init
	 */
	public function checkForRewriteFlush() {
		if( get_transient( 'lumi.secure-post-with-link.flush_rewrite' ) == true ) {
			delete_transient( 'lumi.secure-post-with-link.flush_rewrite' );
			flush_rewrite_rules();
		}
	}

	/**
	 * After deactivation, flush rewrite rules.
	 * Should be safe to do that right on deactivation action, as plugin sould not be loaded by now
	 *
	 * @wp-action register_deactivation_hook
	 */
	public function flushRewriteOnDeactivation() {
		flush_rewrite_rules();
	}


}