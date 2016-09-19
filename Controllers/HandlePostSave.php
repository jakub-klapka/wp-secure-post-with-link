<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\SingletonTrait;

class HandlePostSave {
	use SingletonTrait;

	/**
	 * HandlePostSave constructor.
	 * Register hooks
	 */
	public function __construct() {

		add_filter( 'wp_insert_post_data', [ $this, 'maybeChangeStatus' ], 10, 2 );

	}

	/**
	 * Intercept post saving and change post_status, if we have selected secured post
	 *
	 * @wp-filter wp_insert_post_data
	 * @param array $data Post data to save
	 * @param array $postarr Data from $_POST
	 *
	 * @return array
	 */
	public function maybeChangeStatus( $data, $postarr ) {

		//TODO: check for valid post type

		if( $postarr[ 'visibility' ] === 'secured' ) {
			$data[ 'post_status' ] = 'secured';
		}

		return $data;

	}

}