<?php
/**
 * Debug Bar Constants - Debug Bar Panels
 *
 * @package WordPress\Plugins\Debug Bar Constants
 * @since 1.0
 * @version 1.2.1
 *
 * @author Juliette Reinders Folmer
 *
 * @copyright 2013 Juliette Reinders Folmer
 * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2
 */

// Avoid direct calls to this file
if( !function_exists( 'add_action' ) ) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}



/**
 * The classes in this file extend the functionality provided by the parent plugin "Debug Bar".
 */
if( !class_exists( 'Debug_Bar_Constants' ) && class_exists( 'Debug_Bar_Panel' ) ) {

	class Debug_Bar_Constants extends Debug_Bar_Panel {

		const DBC_STYLES_VERSION = '1.2';
		const DBC_SCRIPT_VERSION = '1.2dbc-a';
		
		const DBC_NAME = 'debug-bar-constants';
		
		public function init() {
			if( ( !class_exists( 'debug_bar_pretty_output' ) && class_exists( 'Debug_Bar_Panel' ) ) || !class_exists( 'list_php_classes' ) ) {
				require_once 'class-debug-bar-pretty-output.php';
			}

			load_plugin_textdomain( self::DBC_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

			//debug_bar_enqueue_scripts
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		}

		public function enqueue_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '' );
			wp_enqueue_style( self::DBC_NAME, plugins_url( 'css/debug-bar-constants' . $suffix . '.css', __FILE__ ), array(), self::DBC_STYLES_VERSION );
			wp_enqueue_script( self::DBC_NAME, plugins_url( 'js/jquery.ui.totop' . $suffix . '.js', __FILE__ ), array( 'jquery' ), self::DBC_SCRIPT_VERSION, true );
			unset( $suffix );
		}

		public function prerender() {
			$this->set_visible( true );
		}
		
		public function dbc_render_table( $array, $col1 = null, $col2 = null, $class = null ) {
			
			$context = self::DBC_NAME;

			$classes = self::DBC_NAME;
			if( !is_null( $class ) ) {
				if( is_string( $class ) ) {
					$classes .= ' ' . $class;
				}
				else if( is_array( $class ) && count( $class ) > 0 ) {
					$classes = $classes . ' ' . implode( ' ', $class );
				}
			}
			$col1 = ( !is_null( $col1 ) ? $col1 : __( 'Name', self::DBC_NAME ) );
			$col2 = ( !is_null( $col2 ) ? $col2 : __( 'Value', self::DBC_NAME ) );

			uksort( $array, 'strnatcasecmp' );
			debug_bar_pretty_output::render_table( $array, $col1, $col2, $classes, $context );
		}


	} // End of class Debug_Bar_Constants

} // End of if class_exists wrapper



if( !class_exists( 'Debug_Bar_WP_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {

    class Debug_Bar_WP_Constants extends Debug_Bar_Constants {

		public function init() {
			parent::init();
			$this->title( __( 'WP Constants', parent::DBC_NAME ) );
		}

		public function prerender() {
			$this->set_visible( is_super_admin() );
		}

		public function render() {
			$constants = get_defined_constants( true );
			if( isset( $constants['user'] ) && ( is_array( $constants['user'] ) && count( $constants['user'] ) > 0 ) ) {
				echo '
		<h2><span>' . esc_html__( 'Constants within WP:', parent::DBC_NAME ) . '</span>' . count( $constants['user'] ) . '</h2>';
				$this->dbc_render_table( $constants['user'] );
			}
			else { // should never happen
				echo '<p>' . esc_html__( 'No constants found... this is really weird...', parent::DBC_NAME ) . '</p>';
			}
			unset( $constants );
		}

	} // End of class Debug_Bar_WP_Constants

} // End of if class_exists wrapper



if( !class_exists( 'Debug_Bar_WP_Class_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {

	class Debug_Bar_WP_Class_Constants extends Debug_Bar_Constants {
		
		public function init() {
			parent::init();
			$this->title( __( 'WP Class Constants', parent::DBC_NAME ) );
		}

		public function render() {

			$classes = get_declared_classes();
			if( class_exists( 'list_php_classes' ) && property_exists( 'list_php_classes', 'PHP_classes') ) {
				$classes = array_udiff( $classes, list_php_classes::$PHP_classes, 'strcasecmp' );
			}

			$constants = array();

			if( is_array( $classes ) && count( $classes ) > 0 ) {

				// Get the constants info first
				foreach( $classes as $class ) {

					$reflector = new ReflectionClass( $class );
					$class_constants = $reflector->getConstants();

					if( is_array( $class_constants ) && count( $class_constants ) > 0 ) {
						$constants[$class] = $class_constants;
					}
					unset( $class_constants );
				}
				unset( $class, $reflector );

				// Generate the output
				if( is_array( $constants ) && count( $constants ) > 0 ) {

					uksort( $constants, 'strnatcasecmp' );

					foreach( $constants as $class => $set ) {
						$count = count( $set );
						echo '
			<h2><a href="#dbcwpc-' . esc_attr( $class ) . '"><span>' . esc_html( $class ) . ':</span>' . $count . '</a></h2>';
						unset( $count );
					}
					unset( $class, $set );

					echo '<p class="dbcwpc-info">' . __( '<strong>Please note</strong>: these may be both native WordPress classes as well as classes which may be declared by plugins or Themes.<br />You can use these constants in your code using <code>class_name::constant_name</code>.', parent::DBC_NAME ) . ' ' . sprintf( __( 'See the <a %s>FAQ</a> for more information.', parent::DBC_NAME ), 'href="http://wordpress.org/extend/plugins/debug-bar-constants/faq/" target="_blank"') . '.</p>';

					foreach( $constants as $class => $set ) {
						echo '
			<h3 id="dbcwpc-' . esc_attr( $class ) . '"><em>' . esc_html( ucfirst( $class ) ) . '</em> ' . esc_html__( 'Constants:', parent::DBC_NAME ) . '</h3>';
						$this->dbc_render_table( $set );
					}
					unset( $class, $set );
				}
			}
			else { // should never happen
				echo '<p>' . esc_html__( 'No classes nor class constants found... this is kinda strange...', parent::DBC_NAME ) . '</p>';
			}
			unset( $classes, $constants );
		}

	} // End of class Debug_Bar_WP_Class_Constants

} // End of if class_exists wrapper



if( !class_exists( 'Debug_Bar_PHP_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {

	class Debug_Bar_PHP_Constants extends Debug_Bar_Constants {
		
		public function init() {
			parent::init();
			$this->title( __( 'PHP Constants', parent::DBC_NAME ) );
		}

		public function render() {

			$constants = get_defined_constants( true );
			unset( $constants['user'] );
			
			if( is_array( $constants ) && count( $constants ) > 0 ) {

				uksort( $constants, 'strnatcasecmp' );

				foreach( $constants as $category => $set ) {
					echo '
		<h2><a href="#dbcphp-' . esc_attr( $category ) . '"><span>' . esc_html( $category ) . ':</span>' . count( $set ) . '</a></h2>';
				}
				unset( $category, $set );

				foreach( $constants as $category => $set ) {
					if( is_array( $set ) && count( $set ) > 0 ) {
						echo '
		<h3 id="dbcphp-' . esc_attr( $category ) . '"><em><a href="http://php.net/' . $category . '.constants" target="_blank" title="' . esc_attr( sprintf( __('Visit the PHP manual page about the %s constants.', parent::DBC_NAME ), $category ) ) . '">' . esc_html( ucfirst( $category ) ) . '</a></em> ' . esc_html__( 'Constants:', parent::DBC_NAME ) . '</h3>';
						$this->dbc_render_table( $set );
					}
				}
				unset( $category, $set );
			}
			else { // should never happen
				echo '<p>' . esc_html__( 'No PHP constants found... this is really weird...', parent::DBC_NAME ) . '</p>';
			}
			unset( $constants );
		}

	} // End of class Debug_Bar_PHP_Constants

} // End of if class_exists wrapper
