<?php
/*
Plugin Name: Secure Post with Link [Work In Progress]
Description: Add new status for posts: "Secured with Link". Posts with this status will be accessible only on URL with randomly generated token.
Version:     0.9
Author:      Jakub Klapka
Author URI:  https://www.lumiart.cz/
Text Domain: lumi-secure-post-with-link
Domain Path: /lang
Repository:  https://github.com/jakub-klapka/wp-secure-post-with-link
*/

/*
 * Weird WP security first
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
 * Define Providers
 */
$providers = [
	Lumi\SecurePostWithLink\Controllers\AdminUi::class,
	Lumi\SecurePostWithLink\Controllers\HandlePostSave::class,
	Lumi\SecurePostWithLink\Controllers\RegisterPostStatus::class,
	Lumi\SecurePostWithLink\Controllers\HandleFrontAccess::class,
	Lumi\SecurePostWithLink\Controllers\Translation::class
];

/**
 * Classes autoloader
 */
spl_autoload_register( function( $class_name ) {

	if( strpos( $class_name, "Lumi\\SecurePostWithLink\\" ) !== false ){

		$filename = str_replace( "\\", "/", str_replace( "Lumi\\SecurePostWithLink\\", '', $class_name ) );
		require_once( __DIR__ . '/' . $filename . ".php" );

	}

} );

/**
 * Load App
 */
foreach( $providers as $provider ) {
	$instance = call_user_func( $provider . '::getInstance' );
	$instance->boot();
}
