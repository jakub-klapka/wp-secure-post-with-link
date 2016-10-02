=== Secure post with link ===
Contributors: lapak
Tags: secure,post,link,hide,private,URL,status
Requires at least: 4.5
Tested up to: 4.6
Stable tag: 1.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds new post status "Secured with Link". Posts with this status will be accessible only with randomly generated token in URL.

== Description ==
This plugin adds new status for posts (or pages/custom post types) called "Secured with link". If you select this status, that specific post wouldn't be accessible on its standard URL anymore, but you have to access it on URL with random token in it. This URL is displayed on post edit screen in administration.

= Configuration =
For now, there is no administration screen yet. To configure plugin, you can inject your changes to plugin configuration array from your theme or plugin. Just put this code into *functions.php* file in your theme:

`add_filter( 'lumi.secure_post_with_link.config', function( $config ){
    $config[ 'allowed_post_types' ] = [ 'post', 'page' ];
    $config[ 'url_identifier' ] = 'sec';
    // ... Other $config variables
    return $config;
} );`

Preferably, don't include lines with attributes, which you don't want to change.


These are attributes *(with defaults)*, which you can change this way:

* **allowed_post_types** *([ 'post' ])* List of post types, which should be affected by this plugin. Use array notation like: `[ 'post', 'page', 'custom_type' ]`.

* **secured_meta_name** *('_secured_with_link_token')* Name of meta, which stores post token. You can change that in case of conflicts with themes or other plugins.

* **url_identifier** *('s')* Part of URL before token, which identifies secured posts.

* **token_length** *(4)* Length of random token. With default generator, length is in bytes!

* **use_openssl** *(true)* If plugin should use cryptographically secure random generator, or less-secure PHP functions. (See below)

* **assets_url** *(Defaults to [plugin URL]/assets)* URL for static assets (like Javascripts), in case, you want to use your assets for some reason.

* **translations_dir** *(Defaults to [Plugin path]/lang)* You can change path, where plugin looks for translation files. If you want to translate plugin, you should **not** use this setting, but create standard translation in *wp-content/languages* folder.

In case, you have changed any settings after plugin installation, deactivate and reactivate plugin, so all changes are correctly applied.

= Compatibility =
* For now, you need to use WP URL Rewriting for plugin to work correctly
* Plugin should work with PHP 5.4, but at least PHP 5.6 is highly recommended. I'm sorry, but I won't support older, insecure and slow versions of PHP.
* By default, plugin uses OpenSSL library to generate cryptographically secure random tokens. If your hosting provider don't have OpenSSL set up, you can use configuration variable: `$config[ 'use_openssl' ] = false` to use less-secure method of token generating. This should still be secure enough for most use cases.

= Planned features =
* Administration page
* Regenerate token
* Role management - users with privileges should be able to access post without token

= Developers =
Plugin is also hosted on [Github](https://github.com/jakub-klapka/wp-secure-post-with-link).

== Installation ==
Unzip files to **secure-post-with-link** folder in your wp-plugins.

== Screenshots ==
1. URL with token
2. Status setting

== Changelog ==
= 1.0 =
* Initial version with base functionality
