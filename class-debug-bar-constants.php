<?php
/**
 * Debug Bar Constants - Base class for a Debug Bar Constants Debug Bar Panel.
 *
 * @package     WordPress\Plugins\Debug Bar Constants
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/Debug-Bar-Constants
 *
 * @copyright   2013-2017 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if ( ! class_exists( 'Debug_Bar_Constants' ) && class_exists( 'Debug_Bar_Panel' ) ) {

	/**
	 * Base class.
	 */
	class Debug_Bar_Constants extends Debug_Bar_Panel {

		const DBC_STYLES_VERSION = '1.7.0';
		const DBC_SCRIPT_VERSION = '1.7.0';

		const DBC_NAME = 'debug-bar-constants';

		/**
		 * Whether or not an attempt has been made to load the textdomain.
		 * If so, no need to try again.
		 *
		 * @var bool
		 */
		private static $textdomain_loaded = false;

		/**
		 * Constructor.
		 */
		public function init() {
			if ( ! class_exists( 'Debug_Bar_Pretty_Output' ) && class_exists( 'Debug_Bar_Panel' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'inc/debug-bar-pretty-output/class-debug-bar-pretty-output.php';
			}

			if ( ! class_exists( 'Debug_Bar_List_PHP_Classes' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'inc/debug-bar-pretty-output/class-debug-bar-list-php-classes.php';
			}

			$this->load_textdomain( self::DBC_NAME );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}


		/**
		 * Load the plugin text strings.
		 *
		 * Compatible with use of the plugin in the must-use plugins directory.
		 *
		 * {@internal No longer needed since WP 4.6, though the language loading in
		 * WP 4.6 only looks at the `wp-content/languages/` directory and disregards
		 * any translations which may be included with the plugin.
		 * This is acceptable for plugins hosted on org, especially if the plugin
		 * is new and never shipped with it's own translations, but not when the plugin
		 * is hosted elsewhere.
		 * Can be removed if/when the minimum required version for this plugin is ever
		 * upped to 4.6. The `languages` directory can be removed in that case too.
		 * See: {@link https://core.trac.wordpress.org/ticket/34213} and
		 * {@link https://core.trac.wordpress.org/ticket/34114} }}
		 *
		 * @param string $domain Text domain to load.
		 */
		protected function load_textdomain( $domain ) {
			if ( function_exists( '_load_textdomain_just_in_time' ) ) {
				return;
			}

			if ( is_textdomain_loaded( $domain ) || self::$textdomain_loaded ) {
				return;
			}

			$lang_path = dirname( plugin_basename( __FILE__ ) ) . '/languages';
			if ( false === strpos( __FILE__, basename( WPMU_PLUGIN_DIR ) ) ) {
				load_plugin_textdomain( $domain, false, $lang_path );
			} else {
				load_muplugin_textdomain( $domain, $lang_path );
			}
			self::$textdomain_loaded = true;
		}


		/**
		 * Enqueue js and css files.
		 */
		public function enqueue_scripts() {
			$suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );
			wp_enqueue_style( self::DBC_NAME, plugins_url( 'css/debug-bar-constants' . $suffix . '.css', __FILE__ ), array( 'debug-bar' ), self::DBC_STYLES_VERSION );
			wp_enqueue_script( self::DBC_NAME, plugins_url( 'js/jquery.ui.totop' . $suffix . '.js', __FILE__ ), array( 'jquery' ), self::DBC_SCRIPT_VERSION, true );
		}


		/**
		 * Should the tab be visible ?
		 * You can set conditions here so something will for instance only show on the front- or the
		 * back-end.
		 */
		public function prerender() {
			$this->set_visible( true );
		}


		/**
		 * Helper method to render the output in a table.
		 *
		 * @param array             $array Array to be shown in the table.
		 * @param string|null       $col1  Label for the first table column.
		 * @param string|null       $col2  Label for the second table column.
		 * @param string|array|null $class One or more CSS classes to add to the table.
		 */
		public function dbc_render_table( $array, $col1 = null, $col2 = null, $class = null ) {

			$classes = self::DBC_NAME;
			if ( isset( $class ) ) {
				if ( is_string( $class ) && '' !== $class ) {
					$classes .= ' ' . $class;
				} elseif ( ! empty( $class ) && is_array( $class ) ) {
					$classes = $classes . ' ' . implode( ' ', $class );
				}
			}
			$col1 = ( isset( $col1 ) ? $col1 : __( 'Name', 'debug-bar-constants' ) );
			$col2 = ( isset( $col2 ) ? $col2 : __( 'Value', 'debug-bar-constants' ) );

			uksort( $array, 'strnatcasecmp' );

			if ( defined( 'Debug_Bar_Pretty_Output::VERSION' ) ) {
				echo Debug_Bar_Pretty_Output::get_table( $array, $col1, $col2, $classes ); // WPCS: xss ok.

			} else {
				// An old version of the pretty output class was loaded.
				Debug_Bar_Pretty_Output::render_table( $array, $col1, $col2, $classes );
			}
		}
	} // End of class Debug_Bar_Constants.

} // End of if class_exists wrapper.
