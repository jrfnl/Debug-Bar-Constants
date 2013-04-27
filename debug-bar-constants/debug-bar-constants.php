<?php
/*
Plugin Name: Debug Bar Constants
Plugin URI: http://wordpress.org/extend/plugins/debug-bar-constants/
Description: Debug Bar Constants adds a new panel to Debug Bar that displays all the defined constants for the current request. Requires "Debug Bar" plugin.
Version: 1.0
Author: Juliette Reinders Folmer
Author URI: http://www.adviesenzo.nl/
Text Domain: debug-bar-constants
Domain Path: /languages/

Copyright 2013 Juliette Reinders Folmer
*/
/*
GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Avoid direct calls to this file
if( !function_exists('add_action') ) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

/**
 * Show notice if debug-bar plugin not active
 */
if( is_admin() && !is_plugin_active( 'debug-bar/debug-bar.php' ) ) {
	add_action( 'admin_notices', 'dbc_missing_parent_plugin' );
	
	function dbc_missing_parent_plugin() {
		$activate = admin_url( 'plugins.php#debug-bar' );
		$string = '<div class="error"><p>' . sprintf( __( 'Debug Bar must be activated to use the Debug Bar Constants Plugin. <a href="%s">Visit your plugins page to activate</a>.', 'debug-bar-constants' ), $activate ) . '</p></div>';
		echo $string;
	}
}



if( !function_exists( 'debug_bar_constants' ) ) {

	// Low prio, no need for it to be high up in the list
	add_filter( 'debug_bar_panels', 'debug_bar_constants_panel', 50 );

    function debug_bar_constants_panel( $panels ) {
		if( !class_exists( 'Debug_Bar_WP_Constants' ) && !class_exists( 'Debug_Bar_PHP_Constants' ) ) {
			require_once 'class-debug-bar-constants.php';
		}
        $panels[] = new Debug_Bar_WP_Constants();
        $panels[] = new Debug_Bar_PHP_Constants();
        return $panels;
    }
}