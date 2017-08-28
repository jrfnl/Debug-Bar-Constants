<?php
/**
 * Debug Bar Constants.
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


if ( ! class_exists( 'Debug_Bar_Constants' ) ) {

	/**
	 * Plugin controller.
	 */
	class Debug_Bar_Constants {

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
		public function __construct() {
			spl_autoload_register( array( $this, 'auto_load' ) );

			add_action( 'init', array( $this, 'init' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Low priority, no need for it to be high up in the list.
			add_filter( 'debug_bar_panels', array( $this, 'add_panels' ), 12 );
		}


		/**
		 * Auto load our class files.
		 *
		 * @param string $class Class name.
		 *
		 * @return void
		 */
		public function auto_load( $class ) {
			static $classes = null;

			if ( null === $classes ) {
				$classes = array(
					'debug_bar_constants_panel'    => 'class-debug-bar-constants-panel.php',
					'debug_bar_wp_constants'       => 'class-debug-bar-wp-constants.php',
					'debug_bar_wp_class_constants' => 'class-debug-bar-wp-class-constants.php',
					'debug_bar_php_constants'      => 'class-debug-bar-php-constants.php',

					'debug_bar_pretty_output'      => 'inc/debug-bar-pretty-output/class-debug-bar-pretty-output.php',
					'debug_bar_list_php_classes'   => 'inc/debug-bar-pretty-output/class-debug-bar-list-php-classes.php',
				);
			}

			$cn = strtolower( $class );

			if ( isset( $classes[ $cn ] ) ) {
				include_once plugin_dir_path( __FILE__ ) . $classes[ $cn ];
			}
		}


		/**
		 * Add actions which are needed for both front-end and back-end functionality.
		 *
		 * @return void
		 */
		public function init() {
			/*
			 *  Load plugin text strings.
			 * @see http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
			 *
			 * If you'll be hosting your plugin at wordpress.org and using the translations as
			 * provided via GlotPress (translate.wordpress.org), you can simplify this to the
			 * below and you can remove the local `load_textdomain()` function as well:
			 *
			 * `load_plugin_textdomain( 'demo-quotes-plugin' );`
			 *
			 * The net effect of this will be that WP will ignore translations included with the
			 * plugin and will look in the `wp-content/languages/plugins/` folder for translations
			 * instead.
			 */
			$this->load_textdomain( self::DBC_NAME );
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
		 * Add the Debug Bar Constant panels to the Debug Bar.
		 *
		 * @param array $panels Existing debug bar panels.
		 *
		 * @return array
		 */
		public function add_panels( $panels ) {
			$panels[] = new Debug_Bar_WP_Constants();
			$panels[] = new Debug_Bar_WP_Class_Constants();
			$panels[] = new Debug_Bar_PHP_Constants();
			return $panels;
		}

	} // End of class Debug_Bar_Constants.

} // End of if class_exists wrapper.
