<?php
/**
 * Debug Bar Constants, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Constants
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/Debug-Bar-Constants
 * @version     1.7.0
 *
 * @copyright   2013-2017 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Debug Bar Constants
 * Plugin URI:  https://wordpress.org/plugins/debug-bar-constants/
 * Description: Debug Bar Constants adds new panels to Debug Bar that display all the defined constants for the current request. Requires "Debug Bar" plugin.
 * Version:     1.7.0
 * Author:      Juliette Reinders Folmer
 * Author URI:  http://www.adviesenzo.nl/
 * Depends:     Debug Bar
 * Text Domain: debug-bar-constants
 * Domain Path: /languages
 * Copyright:   2013-2017 Juliette Reinders Folmer
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Show admin notice & de-activate itself if debug-bar plugin not active.
 */
add_action( 'admin_init', 'dbc_has_parent_plugin' );

if ( ! function_exists( 'dbc_has_parent_plugin' ) ) {
	/**
	 * Check for parent plugin.
	 */
	function dbc_has_parent_plugin() {
		$file = plugin_basename( __FILE__ );

		if ( is_admin() && ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) ) && is_plugin_active( $file ) ) {
			add_action( 'admin_notices', create_function( null, 'echo \'<div class="error"><p>\', sprintf( __( \'Activation failed: Debug Bar must be activated to use the <strong>Debug Bar Constants</strong> Plugin. %sVisit your plugins page to install & activate.\', \'debug-bar-constants\' ), \'<a href="\' . admin_url( \'plugin-install.php?tab=search&s=debug+bar\' ) . \'">\' ), \'</a></p></div>\';' ) );

			deactivate_plugins( $file, false, is_network_admin() );

			// Add to recently active plugins list.
			$insert = array(
				$file => time(),
			);

			if ( ! is_network_admin() ) {
				update_option( 'recently_activated', ( $insert + (array) get_option( 'recently_activated' ) ) );
			} else {
				update_site_option( 'recently_activated', ( $insert + (array) get_site_option( 'recently_activated' ) ) );
			}

			// Prevent trying again on page reload.
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}


if ( ! function_exists( 'debug_bar_constants_init' ) ) {

	// wp_installing() function was introduced in WP 4.4.
	if ( ( function_exists( 'wp_installing' ) && wp_installing() === false ) || ( ! function_exists( 'wp_installing' ) && ( ! defined( 'WP_INSTALLING' ) || WP_INSTALLING === false ) ) ) {
		add_action( 'plugins_loaded', 'debug_bar_constants_init' );
	}

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	function debug_bar_constants_init() {
		include_once plugin_dir_path( __FILE__ ) . 'class-debug-bar-constants.php';
		$debug_bar_constants = new Debug_Bar_Constants();
	}
}
