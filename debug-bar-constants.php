<?php
/**
 * Debug Bar Constants, a WordPress plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Constants
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/Debug-Bar-Constants
 * @version     1.6.1
 *
 * @copyright   2013-2016 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Debug Bar Constants
 * Plugin URI:  https://wordpress.org/plugins/debug-bar-constants/
 * Description: Debug Bar Constants adds new panels to Debug Bar that display all the defined constants for the current request. Requires "Debug Bar" plugin.
 * Version:     1.6.1
 * Author:      Juliette Reinders Folmer
 * Author URI:  http://www.adviesenzo.nl/
 * Depends:     Debug Bar
 * Text Domain: debug-bar-constants
 * Domain Path: /languages
 * Copyright:   2013-2016 Juliette Reinders Folmer
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
		if ( is_admin() && ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) ) ) {
			add_action( 'admin_notices', create_function( null, 'echo \'<div class="error"><p>\', sprintf( __( \'Activation failed: Debug Bar must be activated to use the <strong>Debug Bar Constants</strong> Plugin. %sVisit your plugins page to activate.\', \'debug-bar-constants\' ), \'<a href="\' . admin_url( \'plugins.php#debug-bar\' ) . \'">\' ), \'</a></p></div>\';' ) );

			deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}



if ( ! function_exists( 'debug_bar_constants_panels' ) ) {
	// Low priority, no need for it to be high up in the list.
	add_filter( 'debug_bar_panels', 'debug_bar_constants_panels', 12 );

	/**
	 * Add the Debug Bar Constant panels to the Debug Bar.
	 *
	 * @param array $panels Existing debug bar panels.
	 *
	 * @return array
	 */
	function debug_bar_constants_panels( $panels ) {
		if ( ( ! class_exists( 'Debug_Bar_WP_Constants' ) && ! class_exists( 'Debug_Bar_WP_Class_Constants' ) ) && ! class_exists( 'Debug_Bar_PHP_Constants' ) ) {
			require_once 'class-debug-bar-constants.php';
		}
		$panels[] = new Debug_Bar_WP_Constants();
		$panels[] = new Debug_Bar_WP_Class_Constants();
		$panels[] = new Debug_Bar_PHP_Constants();
		return $panels;
	}
}
