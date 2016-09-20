<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\ProviderInterface;
use Lumi\SecurePostWithLink\SingletonTrait;


class RegisterPostStatus implements ProviderInterface {
	use SingletonTrait;

	/**
	 * Register WP actions and inject deps
	 */
	public function boot() {

		add_action( 'init', [ $this, 'registerWpPostStatus' ] );

	}

	/**
	 * Register WP internal post status
	 *
	 * @wp-action init
	 */
	public function registerWpPostStatus() {

		register_post_status( 'secured', array(
			'label'                     => 'Skrytý odkaz', //TODO: textdomain
			'public'                    => true, //TODO: examine, why we dont see false on edit.php
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Zabezpečeno odkazem <span class="count">(%s)</span>', 'Zabezpečeno odkazem <span class="count">(%s)</span>' ), //TODO: textdomain
		) );

	}

}