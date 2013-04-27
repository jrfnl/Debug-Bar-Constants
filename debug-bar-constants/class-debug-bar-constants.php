<?php
/**
 * Debug Bar Constants - Debug Bar Panels
 *
 * @package WordPress\Plugins\Debug Bar Constants
 * @since 1.0
 * @version 1.0
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
 * This classes in this file extends the functionality provided by the parent plugin "Debug Bar".
 */
if( ( !class_exists( 'Debug_Bar_WP_Constants' ) && !class_exists( 'Debug_Bar_PHP_Constants' ) ) && class_exists( 'Debug_Bar_Panel' ) ) {

    class Debug_Bar_WP_Constants extends Debug_Bar_Panel {
		
		public function init() {
			load_plugin_textdomain( 'debug-bar-constants', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			$this->title( __( 'WP Constants', 'debug-bar-constants' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		}

		public function enqueue_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '' );
			wp_enqueue_style( 'debug-bar-constants', plugins_url( 'css/debug-bar-constants' . $suffix . '.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_script( 'debug-bar-constants', plugins_url( 'js/jquery.ui.totop' . $suffix . '.js', __FILE__ ), array( 'jquery' ), '1.2dbc', true );
			unset( $suffix );
		}

		public function prerender() {
			$this->set_visible( is_super_admin() );
		}

		public function render() {
			$pretty = new debug_bar_pretty_output();
			
			$constants = get_defined_constants( true );

			if( isset( $constants['user'] ) && ( is_array( $constants['user'] ) && count( $constants['user'] ) > 0 ) ) {

				echo '
		<h2><span>' . __( 'Constants within WP:', 'debug-bar-constants' ) . '</span>' . count( $constants['user'] ) . '</h2>
		<table class="debug-bar-constants">
			<tr>
				<th>' . __( 'Name', 'debug-bar-constants' ) . '</th>
				<th>' . __( 'Value', 'debug-bar-constants' ) . '</th>
			</tr>';

				ksort( $constants['user'] );

				foreach( $constants['user'] as $key => $value ) {

					echo '
			<tr>
				<td>
					<strong>' . esc_html( $key ) . '</strong>
				</td>
				<td>';

					$pretty->output( $value, '', true );

                    echo '
				</td>
			</tr>';
				}
				unset( $key, $value );

				echo '
		</table>
';

			}
			else {
				echo '<p>' . __( 'No constants found... this is really weird...', 'debug-bar-constants' ) . '</p>';
			}
			unset( $constants, $pretty );
		}

	} // End of class Debug_Bar_WP_Constants
	
	
	class Debug_Bar_PHP_Constants extends Debug_Bar_Panel {
		
		public function init() {
			load_plugin_textdomain( 'debug-bar-constants', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			$this->title( __( 'PHP Constants', 'debug-bar-constants' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		}
		
		public function enqueue_scripts() {
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.dev' : '' );
			wp_enqueue_style( 'debug-bar-constants', plugins_url( 'css/debug-bar-constants' . $suffix . '.css', __FILE__ ), array(), '1.0' );
			wp_enqueue_script( 'debug-bar-constants', plugins_url( 'js/jquery.ui.totop' . $suffix . '.js', __FILE__ ), array( 'jquery' ), '1.2dbc', true );
			unset( $suffix );
		}
		
		public function prerender() {
			$this->set_visible( true );
		}

		public function render() {
			$pretty = new debug_bar_pretty_output();
			
			$constants = get_defined_constants( true );
			unset( $constants['user'] );
			
			if( is_array( $constants ) && count( $constants ) > 0 ) {
				ksort( $constants );

				foreach( $constants as $category => $set ) {
					echo '
		<h2><a href="#' . esc_attr( $category ) . '"><span>' . esc_html( $category ) . ':</span>' . count( $set ) . '</a></h2>';
				}
				
				foreach( $constants as $category => $set ) {
					
					if( is_array( $set ) && count( $set ) > 0 ) {
						
						echo '
		<h3 id="' . esc_attr( $category ) . '"><em>' . esc_html( ucfirst( $category ) ) . '</em> ' . __( 'Constants:', 'debug-bar-constants' ) . '</h3>
		<table class="debug-bar-constants">
			<tr>
				<th>' . esc_html__( 'Name', 'debug-bar-constants' ) . '</th>
				<th>' . esc_html__( 'Value', 'debug-bar-constants' ) . '</th>
			</tr>';

						ksort( $set );

//                	    $pretty->output( $set, '', true );

						foreach( $set as $key => $value ) {

							echo '
			<tr>
				<td>
					<strong>' . esc_html( $key ) . '</strong>
				</td>
				<td>';

							$pretty->output( $value, '', true );

		                    echo '
				</td>
			</tr>';
						}
						unset( $key, $value );

						echo '
		</table>
';

					}
				}
				unset( $category, $set );
			}
			else {
				echo '<p>' . esc_html__( 'No constants found... this is really weird...', 'debug-bar-constants' ) . '</p>';
			}
			unset( $constants, $pretty );
		}

	} // End of class Debug_Bar_PHP_Constants




	class debug_bar_pretty_output {

		/**
		 * A not-so-pretty method to show pretty output ;-)
		 */
		function output( $var, $title = '', $escape = false, $space = '', $short = false ) {

			if( $space === '' ) { print '<div class="pr_var">'; }
			if ( !empty( $title ) ) {
				print '<h4 style="clear: both;">' . /*( $escape === true ? htmlentities(*/ $title /*, ENT_QUOTES ) : $title )*/ . "</h4>\n";
			}
		
			if ( is_array( $var ) ) {
				print 'Array: <br />' . $space . '(<br />';
				if( $short !== true ) {
					$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				else {
					$spacing = $space . '&nbsp;&nbsp;';
				}
				foreach( $var as $key => $value ) {
					print $spacing . '[' . ( $escape === true ? htmlentities( $key, ENT_QUOTES ): $key );
					if( $short !== true ) {
						print  ' ';
						switch( true ) {
							case ( is_string( $key ) ) :
								print '<span style="color: #336600; background-color: transparent;"><b><i>(string)</i></b></span>';
								break;
							case ( is_int( $key ) ) :
								print '<span style="color: #FF0000; background-color: transparent;"><b><i>(int)</i></b></span>';
								break;
							case ( is_float( $key ) ) :
								print '<span style="color: #990033; background-color: transparent;"><b><i>(float)</i></b></span>';
								break;
							default:
								print '(unknown)';
								break;
						}
					}
					print '] => ';
					pr_var( $value, '', $escape, $spacing, $short );
				}
				print $space . ')<br />';
			}
			elseif ( is_string( $var ) ) {
				print '<span style="color: #336600; background-color: transparent;">';
				if( $short !== true ) {
					print '<b><i>string['
						. strlen( $var )
					. ']</i></b> : ';
				}
				print '&lsquo;'
					. ( $escape === true ? str_replace( '  ', ' &nbsp;', htmlentities( $var, ENT_QUOTES ) ) : str_replace( '  ', ' &nbsp;', $var ) )
					. '&rsquo;</span><br />';
			}
			elseif ( is_bool( $var ) ) {
				print '<span style="color: #000099; background-color: transparent;">';
				if( $short !== true ) {
					print '<b><i>bool</i></b> : '
						. $var
						. ' ( = ';
				}
				else {
					print '<b><i>b</i></b> ';
				}
				print '<i>'
					. ( ( $var === false ) ? '<span style="color: #FF0000; background-color: transparent;">false</span>' : ( ( $var === true ) ? '<span style="color: #336600; background-color: transparent;">true</span>' : 'undetermined' ) );
				if( $short !== true ) {
					print ' </i>)';
				}
				print '</span><br />';
			}
			elseif ( is_int( $var ) ) {
				print '<span style="color: #FF0000; background-color: transparent;">';
				if( $short !== true ) {
					print '<b><i>int</i></b> : ';
				}
				print ( ( $var === 0 ) ? '<b>' . $var . '</b>' : $var )
					. "</span><br />\n";
			}
			elseif ( is_float( $var ) ) {
				print '<span style="color: #990033; background-color: transparent;">';
				if( $short !== true ) {
					print '<b><i>float</i></b> : ';
				}
				print $var
					. '</span><br />';
			}
			elseif ( is_null( $var ) ) {
				print '<span style="color: #666666; background-color: transparent;">';
				if( $short !== true ) {
					print '<b><i>';
				}
				print 'null';
				if( $short !== true ) {
					print '</i></b> : '
					. $var
					. ' ( = <i>NULL</i> )';
				}
				print '</span><br />';
			}
			elseif ( is_resource( $var ) ) {
				print '<span style="color: #666666; background-color: transparent;">';
				if( $short !== true ) {
					print '<b><i>resource</i></b> : ';
				}
				print $var;
				if( $short !== true ) {
					print ' ( = <i>RESOURCE</i> )';
				}
				print '</span><br />';
			}
			else if ( is_object( $var ) ) {
				print 'object: <br />' . $space . '(<br />';
				if( $short !== true ) {
					$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				else {
					$spacing = $space . '&nbsp;&nbsp;';
				}
				object_info( $var, $escape, $spacing, $short );
				print $space . ')<br /><br />';
			}
			else {
				print 'I haven&#39;t got a clue what this is: ' . gettype( $var ) . '<br />';
			}
			if( $space === '' ) { print "</div>"; }
		}



		/**
		 * @todo: get object properties to show the variable type on one line with the 'property'
         */
		public function object_info( $obj, $escape, $space, $short ) {
			print $space . '<b><i>Class</i></b>: ' . get_class( $obj ) . ' (<br />';
			if( $short !== true ) {
				$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			else {
				$spacing = $space . '&nbsp;&nbsp;';
			}
			foreach( get_object_vars( $obj ) as $var => $val ) {
				if ( is_array( $val ) ) {
					print $spacing . '<b><i>property</i></b>: ' . $var . '<b><i> (array)</i></b>';
					pr_var( $val, '' , $escape, $spacing, $short );
				} else {
					print $spacing . '<b><i>property</i></b>: ' . $var . ' = ';
					pr_var( $val, '' , $escape, $spacing, $short );
				}
			}
		
			foreach( get_class_methods( $obj ) as $method ) {
				print $spacing . '<b><i>method</i></b>: ' . $method . '<br />';
			}
			print $space . ')<br /><br />';
		}

	} // End of class debug_bar_pretty_output

} // end of if class_exists