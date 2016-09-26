<?php

namespace Lumi\SecurePostWithLink;


class Config {
	use SingletonTrait;

	/**
	 * @var array $config {
	 *      Holder for all config values with defaults
	 *
	 *      @type array  $allowed_post_types Array of post types, which shoud have option to secure by link
	 *      @type string $secured_meta_name Internal name for token post_meta
	 *      @type string $token_length Lenght of acces token in bytes! (Chars for non-openssl version)
	 *      @type string $use_openssl If false, use internal PHP random generator - not crypto secure!
	 *      @type string $textdomain Plugins textdomain
	 *      @type string $assets_url URL of assets dir - can be used to replace all plugins assets at once
	 *      @type string $static_version Version of assets - which is by default same as plugin version
	 *      @type string $translations_dir Directory for translation files for load_plugins_textdomain
	 * }
	 * //TODO: config injecting
	 */
	private $config = [
		'allowed_post_types' => [ 'blog' ],
		'secured_meta_name' => '_secured_with_link_token',
		'url_identifier' => 's',
		'token_length' => 4,
		'use_openssl' => true,
		'textdomain' => 'lumi-secure-post-with-link',
		'assets_url' => null,
		'static_version' => null,
		'translations_dir' => null
	];

	/**
	 * Attributes, which shouldn't be injectable by theme or other plugin
	 *
	 * @var array
	 */
	private $protected = [ 'textdomain' ];

	/**
	 * Config constructor.
	 * Setup global atts
	 */
	public function __construct() {

		$this->set( 'assets_url', plugins_url( 'assets', __FILE__ ) );

		$this->set( 'translations_dir', dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		if( did_action( 'init' ) ) {
			$this->setPluginVersion();
		} else {
			add_action( 'init', [ $this, 'setPluginVersion' ], 1 );
		}

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