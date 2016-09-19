<?php

namespace Lumi\SecurePostWithLink;


class Config {
	use SingletonTrait;

	/**
	 * @var array   $config Holder for all config values
	 * @var array   $config['allowed_post_types'] Array of post types, which shoud have option to secure by link
	 * @var string  $config['secured_meta_name'] Internal name for token post_meta
	 * @var string  $config['token_length'] Lenght of acces token in bytes! (Chars for non-openssl version)
	 * @var string  $config['use_openssl'] If false, use internal PHP random generator - not crypto secure!
	 */
	private $config = [
		'allowed_post_types' => [ 'blog' ],
		'secured_meta_name' => '_secured_with_link_token',
		'token_length' => 4,
		'use_openssl' => true
	];

	/**
	 * Config constructor.
	 * Setup global atts
	 */
	public function __construct() {

		$this->set( 'assets_url', plugins_url( 'assets', __FILE__ ) );

		add_action( 'init', [ $this, 'setPluginVersion' ], 1 );

	}

	/**
	 * Set Current Plugin version config variable
	 *
	 * get_plugin_data function is not avail on boot, we have to set it on init action
	 *
	 * @wp-action init
	 */
	public function setPluginVersion() {

		$plugin_data = get_plugin_data( __DIR__ . '/secure-post-with-link.php', false, false );
		$this->set( 'static_version', $plugin_data[ 'Version' ] );

	}

	/**
	 * Set config value
	 * 
	 * @param string $attr Attribute name
	 * @param mixed $value Attribute value
	 */
	private function set( $attr, $value ) {
		$this->config[ $attr ] = $value;
	}

	/**
	 * Get config value
	 * 
	 * @param string $attr Attribute name
	 *
	 * @return mixed Value
	 */
	public function get( $attr ) {
		return $this->config[ $attr ];
	}
	
}