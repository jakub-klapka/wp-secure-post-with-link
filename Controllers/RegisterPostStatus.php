<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;


class RegisterPostStatus implements ProviderInterface {
	use SingletonTrait;

	/** @var Config */
	private $config;

	/**
	 * Register WP actions and inject deps
	 */
	public function boot() {

		$this->config = Config::getInstance();

		add_action( 'init', [ $this, 'registerWpPostStatus' ] );

	}

	/**
	 * Register WP internal post status
	 *
	 * Setting it to protected=true && show_in_admin_all_list=true will manage to show post on edit.php admin screen
	 *
	 * @wp-action init
	 */
	public function registerWpPostStatus() {

		register_post_status( 'secured', array(
			'label'                     => __( 'Secure link', $this->config->get( 'textdomain' ) ),
			'public'                    => false,
			'private'                   => true,
			'exclude_from_search'       => true,
			'protected'                 => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Secured with link <span class="count">(%s)</span>', 'Secured with link <span class="count">(%s)</span>', $this->config->get( 'textdomain' ) ),
		) );

	}

}