<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\SingletonTrait;

class RegisterPostStatus {
	use SingletonTrait;

	/**
	 * RegisterPostStatus constructor.
	 * Hook to WP actions
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'registerWpPostStatus' ] );

	}

	/**
	 * Register WP internal post status
	 */
	public function registerWpPostStatus() {

		register_post_status( 'secured', array(
			'label'                     => 'Skrytý odkaz', //TODO: textdomain
			'public'                    => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Zabezpečeno odkazem <span class="count">(%s)</span>', 'Zabezpečeno odkazem <span class="count">(%s)</span>' ), //TODO: textdomain
		) );

	}

}