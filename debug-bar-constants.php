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

if ( ! class_exists( 'Debug_Bar_Constants_Init' ) ) {

	/**
	 * Initialize plugin.
	 */
	class Debug_Bar_Constants_Init {

		/**
		 * Initialize the plugin.
		 *
		 * @return void
		 */
		public static function init() {
			/*
			 * Initialize the main class.
			 *
			 * @internal The wp_installing() function was introduced in WP 4.4.
			 */
			if ( ( function_exists( 'wp_installing' ) && wp_installing() === false )
				|| ( ! function_exists( 'wp_installing' )
					&& ( ! defined( 'WP_INSTALLING' ) || WP_INSTALLING === false ) )
			) {
				include_once plugin_dir_path( __FILE__ ) . 'class-debug-bar-constants.php';
				$GLOBALS['debug_bar_constants'] = new Debug_Bar_Constants();
			}

			// Show admin notice & de-activate itself if debug-bar plugin not active.
			add_action( 'admin_init', array( __CLASS__, 'has_debug_bar' ) );
		}


		/**
		 * Check for the Debug Bar plugin being installed & active.
		 *
		 * @return void
		 */
		public static function has_debug_bar() {
			$file = plugin_basename( __FILE__ );

			if ( is_admin()
				&& ( ! class_exists( 'Debug_Bar' ) && current_user_can( 'activate_plugins' ) )
				&& is_plugin_active( $file )
			) {
				add_action( 'admin_notices', array( __CLASS__, 'display_admin_notice' ) );

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

				// Prevent trying to activate again on page reload.
				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}


		/**
		 * Display admin notice about activation failure when dependency not found.
		 *
		 * @return void
		 */
		public static function display_admin_notice() {
			echo '<div class="error"><p>';
			printf(
				/* translators: 1: strong open tag; 2: strong close tag; 3: link to plugin installation page; 4: link close tag. */
				esc_html__( 'Activation failed: Debug Bar must be activated to use the %1$sDebug Bar Constants%2$s Plugin. %3$sVisit your plugins page to install & activate%4$s.', 'debug-bar-constants' ),
				'<strong>',
				'</strong>',
				'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=debug+bar' ) ) . '">',
				'</a>'
			);
			echo '</p></div>';
		}
	}
}

add_action( 'plugins_loaded', array( 'Debug_Bar_Constants_Init', 'init' ) );
