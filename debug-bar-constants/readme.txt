=== Debug Bar Constants ===
Contributors: jrf
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=995SSNDTCVBJG
Tags: debugbar, debug-bar, Debug Bar, Constants, Debug Bar Constants
Requires at least: 3.1
Tested up to: 3.5.1
Stable tag: 1.0
Depends: debug-bar
License: GPLv2

Debug Bar Constants adds two new panels to the Debug Bar that display the defined WP and PHP constants for the current request.

== Description ==

Debug Bar Constants adds two new panels to the Debug Bar that display the defined constants available to you as a developer for the current request:

*	WP Constants
*	PHP Contants

= Important =
This plugin requires the [Debug Bar](http://wordpress.org/extend/plugins/debug-bar/) plugin to be installed and activated.

Also note that this plugin should be used solely for debugging and/or on a development environment and is not intended for use in a production site.


== Frequently Asked Questions ==

= Can it be used on live site ? =
**PLEASE DON'T!** Amongst the defined constants are your database credentials, so you really do not want to do this.
This plugin is only meant to be used for development purposes.

= What are constants ? =
[From PHP.net:](http://php.net/language.constants)
> A constant is an identifier (name) for a simple value. As the name suggests, that value cannot change during the execution of the script. A constant is case-sensitive by default. By convention, constant identifiers are always uppercase.

> Like superglobals, the scope of a constant is global. You can access constants anywhere in your script without regard to scope. For more information on scope, read the manual section on [variable scope](http://php.net/language.variables.scope).


== Changelog ==

= 2013-04-30 / 1.0 by jrf =
* Initial release


== Upgrade Notice ==

= 1.0 =
* Initial release


== Installation ==

1. Install Debug Bar if not already installed (http://wordpress.org/extend/plugins/debug-bar/)
1. Extract the .zip file for this plugin and upload its contents to the `/wp-content/plugins/` directory. Alternately, you can install directly from the Plugin directory within your WordPress Install.
1. Activate the plugin through the "Plugins" menu in WordPress.

Don't use this plugin on a live site. This plugin is **only** for development purpose.


== Screenshots ==
1. Debug Bar displaying WP Constants
1. Debug Bar displaying PHP Constants

