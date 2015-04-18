<?php
/**
 * Debug Bar Constants - Debug Bar Panels
 *
 * @package     WordPress\Plugins\Debug Bar Constants
 * @author      Juliette Reinders Folmer <wpplugins_nospam@adviesenzo.nl>
 * @link        https://github.com/jrfnl/Debug-Bar-Constants
 * @since       1.0
 * @version     1.4
 *
 * @copyright   2013-2014 Juliette Reinders Folmer
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 */

// Avoid direct calls to this file
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}



/**
 * The classes in this file extend the functionality provided by the parent plugin "Debug Bar".
 */
if ( ! class_exists( 'Debug_Bar_Constants' ) && class_exists( 'Debug_Bar_Panel' ) ) {
	/**
	 * Base class
	 */
	class Debug_Bar_Constants extends Debug_Bar_Panel {

		const DBC_STYLES_VERSION = '1.3';
		const DBC_SCRIPT_VERSION = '1.2dbc-a-';

		const DBC_NAME = 'debug-bar-constants';


		/**
		 * Constructor
		 */
		public function init() {
			if ( ( ! class_exists( 'Debug_Bar_Pretty_Output' ) && class_exists( 'Debug_Bar_Panel' ) ) || ! class_exists( 'Debug_Bar_List_PHP_Classes' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'inc/debug-bar-pretty-output/class-debug-bar-pretty-output.php';
			}

			load_plugin_textdomain( self::DBC_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
		 * Helper method to render the output in a table
		 *
		 * @param   array           $array  Array to be shown in the table
		 * @param   string          $col1   Label for the first table column
		 * @param   string          $col2   Label for the second table column
		 * @param   string|array    $class  One or more CSS classes to add to the table
		 */
		public function dbc_render_table( $array, $col1 = null, $col2 = null, $class = null ) {

			$classes = self::DBC_NAME;
			if ( isset( $class ) ) {
				if ( is_string( $class ) && $class !== '' ) {
					$classes .= ' ' . $class;
				}
				else if ( is_array( $class ) && $class !== array() ) {
					$classes = $classes . ' ' . implode( ' ', $class );
				}
			}
			$col1 = ( isset( $col1 ) ? $col1 : __( 'Name', self::DBC_NAME ) );
			$col2 = ( isset( $col2 ) ? $col2 : __( 'Value', self::DBC_NAME ) );

			uksort( $array, 'strnatcasecmp' );

			if ( defined( 'Debug_Bar_Pretty_Output::VERSION' ) ) {
				echo Debug_Bar_Pretty_Output::get_table( $array, $col1, $col2, $classes ); // xss: ok
			}
			else {
				// An old version of the pretty output class was loaded
				Debug_Bar_Pretty_Output::render_table( $array, $col1, $col2, $classes );
			}
		}


	} // End of class Debug_Bar_Constants
} // End of if class_exists wrapper



if ( ! class_exists( 'Debug_Bar_WP_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {
	/**
	 * Debug Bar WP Constants
	 */
	class Debug_Bar_WP_Constants extends Debug_Bar_Constants {


		/**
		 * Constructor
		 */
		public function init() {
			parent::init();
			$this->title( __( 'WP Constants', parent::DBC_NAME ) );
		}


		/**
		 * Limit visibility of the output to super admins on multi-site and
		 * admins on non multi-site installations
		 */
		public function prerender() {
			$this->set_visible( is_super_admin() );
		}


		/**
		 * Render the output
		 */
		public function render() {
			$constants = get_defined_constants( true );
			if ( isset( $constants['user'] ) && ( is_array( $constants['user'] ) && $constants['user'] !== array() ) ) {
				echo '
		<h2><span>', esc_html__( 'Constants within WP:', parent::DBC_NAME ), '</span>', absint( count( $constants['user'] ) ), '</h2>';
				$this->dbc_render_table( $constants['user'] );
			}
			else {
				// should never happen
				echo '<p>', esc_html__( 'No constants found... this is really weird...', parent::DBC_NAME ), '</p>';
			}
		}


	} // End of class Debug_Bar_WP_Constants
} // End of if class_exists wrapper



if ( ! class_exists( 'Debug_Bar_WP_Class_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {
	/**
	 * Debug Bar WP Class Constants
	 */
	class Debug_Bar_WP_Class_Constants extends Debug_Bar_Constants {


		/**
		 * Constructor
		 */
		public function init() {
			parent::init();
			$this->title( __( 'WP Class Constants', parent::DBC_NAME ) );
		}

		/**
		 * Render the output
		 */
		public function render() {

			$classes = get_declared_classes();
			if ( class_exists( 'Debug_Bar_List_PHP_Classes' ) && property_exists( 'Debug_Bar_List_PHP_Classes', 'PHP_classes' ) ) {
				$classes = array_udiff( $classes, Debug_Bar_List_PHP_Classes::$PHP_classes, 'strcasecmp' );
			}

			$constants = array();

			if ( is_array( $classes ) && $classes !== array() ) {
				// Get the constants info first
				foreach ( $classes as $class ) {
					$reflector       = new ReflectionClass( $class );
					$class_constants = $reflector->getConstants();

					if ( is_array( $class_constants ) && $class_constants !== array() ) {
						$constants[ $class ] = $class_constants;
					}
					unset( $class_constants, $reflector );
				}
				unset( $class );

				// Generate the output
				if ( is_array( $constants ) && $constants !== array() ) {
					uksort( $constants, 'strnatcasecmp' );

					foreach ( $constants as $class => $set ) {
						$count = count( $set );
						echo '
			<h2><a href="#dbcwpc-', esc_attr( $class ), '"><span>', esc_html( $class ), ':</span>', absint( $count ), '</a></h2>';
						unset( $count );
					}
					unset( $class, $set );

					echo '<p class="dbcwpc-info">', wp_kses_post( __( '<strong>Please note</strong>: these may be both native WordPress classes as well as classes which may be declared by plugins or Themes.<br />You can use these constants in your code using <code>class_name::constant_name</code>.', parent::DBC_NAME ) ), ' ', wp_kses_post( sprintf( __( 'See the <a %s>FAQ</a> for more information.', parent::DBC_NAME ), 'href="http://wordpress.org/extend/plugins/debug-bar-constants/faq/" target="_blank"' ) ), '.</p>';

					foreach ( $constants as $class => $set ) {
						echo '
			<h3 id="dbcwpc-', esc_attr( $class ), '"><em>', esc_html( ucfirst( $class ) ), '</em> ', esc_html__( 'Constants:', parent::DBC_NAME ), '</h3>';
						$this->dbc_render_table( $set );
					}
					unset( $class, $set );
				}
			}
			else {
				// should never happen
				echo '<p>', esc_html__( 'No classes nor class constants found... this is kinda strange...', parent::DBC_NAME ), '</p>';
			}
		}


	} // End of class Debug_Bar_WP_Class_Constants
} // End of if class_exists wrapper



if ( ! class_exists( 'Debug_Bar_PHP_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {
	/**
	 * Debug Bar PHP Constants
	 */
	class Debug_Bar_PHP_Constants extends Debug_Bar_Constants {


		/**
		 * Constructor
		 */
		public function init() {
			parent::init();
			$this->title( __( 'PHP Constants', parent::DBC_NAME ) );
		}

		/**
		 * Render the output
		 */
		public function render() {

			$constants = get_defined_constants( true );
			unset( $constants['user'] );

			if ( is_array( $constants ) && $constants !== array() ) {
				uksort( $constants, 'strnatcasecmp' );

				foreach ( $constants as $category => $set ) {
					echo '
		<h2><a href="#dbcphp-', esc_attr( $category ), '"><span>', esc_html( $category ), ':</span>', absint( count( $set ) ), '</a></h2>';
				}
				unset( $category, $set );

				foreach ( $constants as $category => $set ) {
					if ( is_array( $set ) && $set !== array() ) {
						echo '
		<h3 id="dbcphp-', esc_attr( $category ), '"><em><a href="http://php.net/', rawurlencode( $category ), '.constants" target="_blank" title="', esc_attr( sprintf( __( 'Visit the PHP manual page about the %s constants.', parent::DBC_NAME ), $category ) ), '">', esc_html( ucfirst( $category ) ), '</a></em> ', esc_html__( 'Constants:', parent::DBC_NAME ), '</h3>';
						$this->dbc_render_table( $set );
					}
				}
				unset( $category, $set );
			}
			else {
				// should never happen
				echo '<p>', esc_html__( 'No PHP constants found... this is really weird...', parent::DBC_NAME ), '</p>';
			}
		}


	} // End of class Debug_Bar_PHP_Constants
} // End of if class_exists wrapper
