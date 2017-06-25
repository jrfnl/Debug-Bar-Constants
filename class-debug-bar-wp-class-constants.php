<?php
/**
 * Debug Bar WP Class Constants - Debug Bar Panel.
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


if ( ! class_exists( 'Debug_Bar_WP_Class_Constants' ) && class_exists( 'Debug_Bar_Constants' ) ) {

	/**
	 * Debug Bar WP Class Constants.
	 */
	class Debug_Bar_WP_Class_Constants extends Debug_Bar_Constants {


		/**
		 * Constructor.
		 */
		public function init() {
			parent::init();
			$this->title( __( 'WP Class Constants', 'debug-bar-constants' ) );
		}

		/**
		 * Render the output.
		 */
		public function render() {

			$classes = get_declared_classes();
			if ( class_exists( 'Debug_Bar_List_PHP_Classes' ) && property_exists( 'Debug_Bar_List_PHP_Classes', 'PHP_classes' ) ) {
				$classes = array_udiff( $classes, Debug_Bar_List_PHP_Classes::$PHP_classes, 'strcasecmp' );
			}

			$constants = array();

			if ( ! empty( $classes ) && is_array( $classes ) ) {
				// Get the constants info first.
				foreach ( $classes as $class ) {
					$reflector       = new ReflectionClass( $class );
					$class_constants = $reflector->getConstants();

					if ( ! empty( $class_constants ) && is_array( $class_constants ) ) {
						$constants[ $class ] = $class_constants;
					}
					unset( $class_constants, $reflector );
				}
				unset( $class );

				// Generate the output.
				if ( ! empty( $constants ) && is_array( $constants ) ) {
					uksort( $constants, 'strnatcasecmp' );

					foreach ( $constants as $class => $set ) {
						$count = count( $set );
						echo '
			<h2><a href="#dbcwpc-', esc_attr( $class ), '"><span>', esc_html( $class ), ':</span>', absint( $count ), '</a></h2>';
						unset( $count );
					}
					unset( $class, $set );

					echo '<p class="dbcwpc-info">', wp_kses_post( __( '<strong>Please note</strong>: these may be both native WordPress classes as well as classes which may be declared by plugins or themes.<br />You can use these constants in your code using <code>class_name::constant_name</code>.', 'debug-bar-constants' ) ), ' ',
					/* TRANSLATORS: %s = the "href" element for the link. */
					wp_kses_post( sprintf( __( 'See the <a %s>FAQ</a> for more information.', 'debug-bar-constants' ), 'href="https://wordpress.org/plugins/debug-bar-constants/faq/" target="_blank"' ) ), '.</p>';

					foreach ( $constants as $class => $set ) {
						echo '
			<h3 id="dbcwpc-', esc_attr( $class ), '"><em>', esc_html( ucfirst( $class ) ), '</em> ', esc_html__( 'Constants:', 'debug-bar-constants' ), '</h3>';
						$this->dbc_render_table( $set );
					}
					unset( $class, $set );
				}
			} else {
				// Should never happen.
				echo '<p>', esc_html__( 'No classes nor class constants found... this is kinda strange...', 'debug-bar-constants' ), '</p>';
			}
		}
	} // End of class Debug_Bar_WP_Class_Constants.

} // End of if class_exists wrapper.
