<?php

namespace Lumi\SecurePostWithLink\Controllers;


use Lumi\SecurePostWithLink\Config;
use Lumi\SecurePostWithLink\SingletonTrait;

class HandlePostSave {
	use SingletonTrait;

	private $config;

	/**
	 * HandlePostSave constructor.
	 * Register hooks
	 */
	public function __construct() {

		$this->config = Config::getInstance();

		add_filter( 'wp_insert_post_data', [ $this, 'maybeChangeStatus' ], 10, 2 );

		foreach ( $this->config->get( 'allowed_post_types' ) as $type ) {
			add_action( "save_post_{$type}", [ $this, 'maybeGenerateNewToken' ], 10, 3 );
		}

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

		//Bail on invalid post_types
		if( !in_array( $data[ 'post_type' ], $this->config->get( 'allowed_post_types' ) ) ) return $data;

		if( $postarr[ 'visibility' ] === 'secured' ) {
			$data[ 'post_status' ] = 'secured';
		}

		return $data;

	}

	/**
	 * Check, if currently saved post has token and generate one, if not
	 *
	 * @wp-action save_post_{$post->post_type}
	 * @param int $post_id Post ID
	 * @param \WP_Post $post Post
	 * @param bool $update True, if this is update of existing post
	 */
	public function maybeGenerateNewToken( $post_id, $post, $update ) {

		//Bail on autosaves
		if( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) return;

		$current_token = get_post_meta( $post->ID, $this->config->get( 'secured_meta_name' ), true );

		if( $current_token == false ) {

			update_post_meta( $post_id, $this->config->get( 'secured_meta_name' ), $this->generateRandomToken() );

		}

	}

	/**
	 * Generate secure random URL token
	 * Preferably use OpenSSL random bytes, config value 'use_openssl' might disable that
	 *
	 * @return string
	 */
	private function generateRandomToken() {

		if( $this->config->get( 'use_openssl' ) ) {
			return bin2hex( openssl_random_pseudo_bytes( $this->config->get( 'token_length' ) ) );
		} else {
			return substr( md5( uniqid( rand(), true ) ), 0, $this->config->get( 'token_length' ) );
		}

	}

}