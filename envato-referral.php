<?php
/*

Plugin Name: Envato Referral
Plugin URI:  http://www.brianlasher.com/
Description: A simple plugin that creates shortcodes and widgets for inserting referrals to envato.com sites
Author:      <a href="http://www.brianlasher.com/">Brian Lasher</a>
Author URI:  http://brianlasher.com
Version:     0.2.000

**************************************************************************

Copyright 2011  Brian Lasher  ( me@brianlasher.com)

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
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists("EnvatoReferral"))
{	class EnvatoReferral
	{
		var $image_prefix = array();

		/**
		* PHP 4 Compatible Constructor
		*/
		function EnvatoReferral(){$this->__construct();}
		
		/**
		* PHP 5 Constructor
		*/		
		function __construct()
		{
			// Set the directory of the plugin:
			$this->path     = dirname(__FILE__);
			$this->basename = plugin_basename(__FILE__);
			$this->folder   = dirname($this->basename);

			// Register general hooks.
			register_activation_hook(__FILE__, array(&$this, 'activate'));

			// Load modules ASAP
			add_action('plugins_loaded', array(&$this, 'init'), 1);
	
			// Admin Menu
			add_action('admin_menu', array(&$this, 'admin'));
	
			// Add actions

			// Add filters

			// Add shortcodes
			add_shortcode( 'envato_referral_link', array($this, 'referral_link') ); 
			add_shortcode( 'envato_referral_list', array($this, 'referral_list') ); 
		}
		
		// Define EnvatoReferral activation hook
		function activate()
		{	global $wp_version;
			global $wp_error;

			if ( ! version_compare( $wp_version, '3.0', '>=') )
			{
				if ( function_exists('deactivate_plugins') )
					deactivate_plugins(__FILE__);
				die(__('<strong>EnvatoReferral:</strong> Sorry, This plugin requires WordPress 3.0', 'EnvatoReferral'));
			}
		}

		// Main EnvatoReferral initialization script
		function init()
		{
			$this->log = "Initializing....\n\n";

			$this->constants();
			$this->includes();
			$this->admin();
			$this->languages();

			// Finished initializing
			do_action( 'envato_referral_post_init' );
		}

		// Initialize EnvatoReferral Constants
		function constants()
		{
			// Define the plugin's version information
			define( 'ENVATOREFERRAL_VERSION', '0.1');
			define( 'ENVATOREFERRAL_MINOR_VERSION', '000');
			define( 'ENVATOREFERRAL_PRESENTABLE_VERSION', '0.1.000');

			// Define the URL to the plugin folder
			define( 'ENVATOREFERRAL_URL', plugins_url( '', __FILE__ ) );
			define( 'ENVATOREFERRAL_IMAGES_URL', plugins_url( '', __FILE__ ).'/images/envato/' );

			// Define the path to the plugin folder
			define( 'ENVATOREFERRAL_PLUGIN_PATH', dirname( __FILE__ ) );
			define( 'ENVATOREFERRAL_DIR', dirname( plugin_basename( __FILE__ ) ) );

			$this->options_name = 'Envato Referral';

			$this->image_prefix['3docean.net']      = '3d';
			$this->image_prefix['activeden.net']    = 'ad';
			$this->image_prefix['audiojungle.net']  = 'aj';
			$this->image_prefix['codecanyon.net']   = 'cc';
			$this->image_prefix['graphicriver.net'] = 'gr';
			$this->image_prefix['themeforest.net']  = 'tf';
			$this->image_prefix['videohive.net']    = 'vh';
			$this->image_prefix['tutsplus.com']     = 'tutorials';

			$this->image_version['3docean.net']      = 'v1';
			$this->image_version['activeden.net']    = 'v1';
			$this->image_version['audiojungle.net']  = 'v1';
			$this->image_version['codecanyon.net']   = 'v1';
			$this->image_version['graphicriver.net'] = 'v1';
			$this->image_version['themeforest.net']  = 'v1';
			$this->image_version['videohive.net']    = 'v1';
			$this->image_version['tutsplus.com']     = 'v1';

			$this->image_sizes = array( '125x125', '180x100', '260x120', '300x250', '468x60', '728x90' );

			$this->set_default( 'user_name',      'EnvatoReferralUserName',     'blasher' );
			$this->set_default( 'envato_site',    'EnvatoReferralEnvatoSite',   'codecanyon.net' );
			$this->set_default( 'image_version',  'EnvatoReferralImageVersion', 'v2' );
			$this->set_default( 'image_size',     'EnvatoReferralImageSize',    '125x125' );
			$this->set_default( 'cycle',          'EnvatoReferralCycle',        'true' );
			$this->set_default( 'max_width',      'EnvatoReferralCycle',        '540px' );

			do_action( 'envato_referral_constants' );
			$this->log .= "EnvatoReferral constants defined.<br />\n";
		}

		// Load EnvatoReferral admin files
		function admin()
		{
			if ( is_admin() )
			{
				// Setup Admin Page Panels
				$options_page = new SubPage('settings', array(  'page_title' => $this->options_name,
																				'capability' => 'manage_options',
																				'plugin'     => $this ) );

				$options_page->addTitle( 'Configure how you would like your Envato Referral to function. Click the help link above for more usage instructions.' );
				$options_page->addInput( array( 'id' => 'EnvatoReferralUserName', 'label' => 'Envato User Name', 'standard' => 'blasher') );
				$options_page->addSubmit( array() );

				/*  Issue with get_options prevented correct output on first load after save options
				$options_page->addSubTitle( 'Option Values' );
				$options_page->addParagraph( '<pre>'. var_export( $this->get_options(true), true ) .'</pre>' );
				$options_page->addSubTitle( 'Sample Envato Referral Link Output' );
				$options_page->addParagraph( do_shortcode('envato_referral_link' ) );
				$options_page->addParagraph( $this->referral_link( array() ) );
				$options_page->addSubTitle( 'Sample Envato Referral List Output' );
				$options_page->addParagraph( do_shortcode('envato_referral_list') );
				$options_page->addParagraph( $this->referral_list( array() ) );
				*/

				// Finished creating admin menus
				do_action( 'envato_referral_admin_menu' );
				$this->log .= "EnvatoReferral admin menus created.<br />\n";
			}
		}

		// Load EnvatoReferral text domain
		function languages()
		{
		}	

		// Load EnvatoReferral include files
		function includes()
		{
			if ( is_admin() )
			{	include_once( ENVATOREFERRAL_PLUGIN_PATH . '/includes/admin-page/class-admin-page.php' );
			}

			include_once( ENVATOREFERRAL_PLUGIN_PATH . '/widgets/class-envato-referral-list-widget.php' );
			include_once( ENVATOREFERRAL_PLUGIN_PATH . '/widgets/class-envato-referral-link-widget.php' );

		}

		// Returns an array of associated options
		function set_default( $internal_name, $option_name, $value )
		{	$this->defaults[$internal_name]    = $value;
			$this->admin_option[$internal_name] = $option_name;
		}

		function get_options($force = false)
		{
			if ( ( empty( $this->admin_options ) ) || $force )
			{
				$defaults = $this->defaults;

				// Initialize with defaults and override with stored options from admin page
				foreach ($defaults as $key => $option)
				{	$options[$key] = $option;
					$admin_option = get_option( $this->admin_option[$key] );
					if($admin_option)
						$options[$key] = $admin_option;
				}				

				// Set object property
				$this->admin_options = $options;
			}

			$options = $this->admin_options;

			return $options;
		}

		// Appends content with a dump of wpdj log
		function dump_log($content = '')
		{	
			$content .= '<br /><br />EnvatoReferral LOG: <br />' . $this->log . "<br />\n";
			return $content;
		}

		// Generates image filename for a single envato referral
		function referral_image_file_name($args)
		{	$default = $this->get_options();

			if ( !empty ($args) )
			{  $args = array_merge($default, $args);
			}
			else
			{   $args = $default;
			}

			$args = array_merge($default, $args);

			$envato_site   = $args['envato_site'];
			$image_size    = $args['image_size'];
			$image_version = $args['image_version'];

			$image_prefix   = $this->image_prefix[$envato_site];
			$image_filename = $image_prefix . '_' . $image_size . '_' . $image_version . '.gif';

			return $image_filename;
		}

		// Generates link for a single envato referral
		function referral_link($args)
		{	$default = $this->get_options();

			/*  is this code not necessary any more?
			extract(shortcode_atts(array(
				      'foo' => 'no foo',
				      'baz' => 'default baz',
			     ), $atts));
			*/

			if ( !empty ($args) )
			{  $args = array_merge($default, $args);
			}
			else
			{  $args = $default;
			}

			$envato_site   = $args['envato_site'];
			$image_size    = $args['image_size'];
			$image_version = $args['image_version'];
			$user_name     = $args['user_name'];

			$url           = 'http://'. $envato_site . '?ref=' . $user_name;
			$image_file    = $this->referral_image_file_name( array( 'envato_site'   => $envato_site,
																						'image_size'    => $image_size,
																						'image_version' => $image_version ) );

			$image_src     = ENVATOREFERRAL_IMAGES_URL . $image_file;

			$output   = '<a href="'. $url . '"><img src="'. $image_src . '" /></a>'."\n";

			return $output;
		}


		// Generates envato referral list
		function referral_list($args)
		{	$default = $this->get_options();

			if ( !empty ($args) )
			{  $args = array_merge($default, $args);
			}
			else
			{   $args = $default;
			}

			$output = '';

			$output .= '<div class="envato_referral_list" style="max_width:' . $args['max_width'] . ';">';
			$user_name  = $args['user_name'];
			$image_size = $args['image_size'];

			foreach ( array_keys( $this->image_prefix ) as $envato_site )
			{
				$image_version  = $this->image_version[$envato_site];

				$output        .= $this->referral_link( array( 'user_name'      => $user_name,
																				'envato_site'   => $envato_site,
																				'image_size'    => $image_size,
																			 	'image_version' => $image_version ) );
			}

			$output .= '</div></br />'."\n";

			return $output;
		}

	}

} // End Class EnvatoReferral

if (class_exists("EnvatoReferral"))
{	$envato_referral = new EnvatoReferral();
}




?>