<?php
/*

Plugin Name: Envato Referral
Plugin URI:  http://www.brianlasher.com/
Description: A simple plugin that creates shortcodes and widgets for inserting referrals to envato.com sites
Author:      <a href="http://www.brianlasher.com/">Brian Lasher</a>
Author URI:  http://brianlasher.com
Version:     0.3.000

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
			$this->path            = dirname(__FILE__);
			$this->basename        = plugin_basename(__FILE__);
			$this->folder          = dirname($this->basename);

			// Register general hooks.
			register_activation_hook(__FILE__, array(&$this, 'activate'));

			// Load modules ASAP
			add_action('plugins_loaded', array(&$this, 'init'), 1);
	
			// Admin Menu
			add_action('admin_menu', array(&$this, 'admin'));
	
			// Add actions

			// Add filters

			// Add shortcodes
			add_shortcode( 'envato_referral_link', array($this, 'referral_link_shortcode') ); 
			add_shortcode( 'envato_referral_list', array($this, 'referral_list_shortcode') ); 
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
			$this->scripts();
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

			$this->image_prefix['3docean.net']       = '3d';
			$this->image_prefix['activeden.net']     = 'ad';
			$this->image_prefix['audiojungle.net']   = 'aj';
			$this->image_prefix['codecanyon.net']    = 'cc';
			$this->image_prefix['graphicriver.net']  = 'gr';
			$this->image_prefix['themeforest.net']   = 'tf';
			$this->image_prefix['videohive.net']     = 'vh';
			$this->image_prefix['tutsplus.com']      = 'tutorials';

			$this->image_version['3docean.net']      = 'v1';
			$this->image_version['activeden.net']    = 'v1';
			$this->image_version['audiojungle.net']  = 'v1';
			$this->image_version['codecanyon.net']   = 'v1';
			$this->image_version['graphicriver.net'] = 'v1';
			$this->image_version['themeforest.net']  = 'v1';
			$this->image_version['videohive.net']    = 'v1';
			$this->image_version['tutsplus.com']     = 'v1';

			$this->image_versions['3docean.net']      = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['activeden.net']    = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['audiojungle.net']  = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['codecanyon.net']   = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['graphicriver.net'] = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['themeforest.net']  = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['videohive.net']    = array( 'v1', 'v2', 'v3', 'v4' );
			$this->image_versions['tutsplus.com']     = array( 'v1', 'v2', 'v3', 'v4' );

			$this->image_sizes = array( '125x125', '180x100', '260x120', '300x250', '468x60', '728x90' );

			$this->set_default( 'user_name',      'EnvatoReferralUserName',     'blasher' );
			$this->set_default( 'envato_site',    'EnvatoReferralEnvatoSite',   'codecanyon.net' );
			$this->set_default( 'image_version',  'EnvatoReferralImageVersion', 'v1' );
			$this->set_default( 'image_size',     'EnvatoReferralImageSize',    '125x125' );
			$this->set_default( 'max_width',      'EnvatoReferralMaxWidth',     '535px' );

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

				// Finished creating admin menus
				do_action( 'envato_referral_admin_menu' );
				$this->log .= "EnvatoReferral admin menus created.<br />\n";
			}
		}


		// Load EnvatoReferral js scripts
		function scripts()
		{
			wp_enqueue_script( 'jQ',       ENVATOREFERRAL_URL . '/js/jquery.min.js' );
			wp_enqueue_script( 'jQcycle',  ENVATOREFERRAL_URL . '/js/jquery.cycle.all.latest.js', array(), array('jQ') );
			wp_enqueue_script( 'jQenvato', ENVATOREFERRAL_URL . '/js/envato_referral.js' );
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
		{	$this->defaults[$internal_name]     = $value;
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

		// Generates image for a single envato referral
		// SHOULD I BE BREAKING SOME OF THIS STUFF OUT INTO SEPERATE CLASSES???
		// PROBABLY IF IT WEREN'T SUCH SIMPLE SUBJECT MATTER???
		function referral_image($args)
		{	
			$image_file = $this->referral_image_file_name( $args );
			$image_src  = ENVATOREFERRAL_IMAGES_URL . $image_file;
			$image = '<img src="'. $image_src . '" />';

			return $image;
		}

		// Generates link for a single envato referral
		function referral_link($args)
		{	$default = $this->get_options();

			if ( !empty ($args) )
			{  $args = array_merge($default, $args);
			}
			else
			{  $args = $default;
			}

			$envato_site    = $args['envato_site'];
			$user_name      = $args['user_name'];
			$image_version  = $args['image_version'];

			if($image_version === 'cycle')
			{	$output .= $this->cycle_image_div($args);
			}
			else
			{
				$url      = 'http://'. $envato_site . '?ref=' . $user_name;
				$image    = $this->referral_image( $args );

				$output   = '<a href="'. $url . '" target="_blank">' . $image . '</a>'."\n";
			}

			return $output;
		}

		// Generates image filename for a single envato referral
		function cycle_image_div($args)
		{	$default = $this->get_options();

			if ( !empty ($args) )
			{  $args = array_merge($default, $args);
			}
			else
			{  $args = $default;
			}

			$envato_site         = $args['envato_site'];
			$delay               = 0 - (500 * ( floor( rand(1, 5) ) ) );  // -2500, -2000, -1500, -1000, -500
			$speed               = 1500;
			$effect              = 'scrollDown';
			//			$effect              = 'fade';

			$sc_index            = $this->sc_index++;
			$cycle_div           = 'slideshow_' . $sc_index . '_' . ( md5( time () ) ); // ensure unique id
			$cycle_div           = str_replace('.', '_', $cycle_div);
			$cycle_div_container = $cycle_div.'_container';

			$output .= '<!-- DELAY ' . $delay . ' -->';
			$output .= '<div id="' . $cycle_div_container . '">';
			$output .= '<div id="' . $cycle_div . '">';

			$image_versions = $this->image_versions[$envato_site];

			foreach ($image_versions as $image_version)
			{
				$image_args = $args;
				$image_args['image_version'] = $image_version;
				$output .= '<div>' . $this->referral_link($image_args) . '</div>';
			}

			$output .= '</div>'."\n";
			$output .= '</div>'."\n";

			$pattern             = '/(\d+)x(\d+)$/';
			$image_size          = $args['image_size'];
			preg_match($pattern, $image_size, $matches);
			$width               = $matches[1];
			$height              = $matches[2];

			$output .= '<style>'."\n";
			$output .= 'div#'.$cycle_div_container.' { width: '. ( $width + 10 ).'px; height: '. ( $height + 10 ) .'px; }'."\n";
			$output .= 'div#'.$cycle_div.' { width: '.$width.'px; height: '.$height.'px; }'."\n";
			$output .= 'div#'.$cycle_div.' div { width: '.$width.'px; height: '.$height.'px; }'."\n";
			$output .= 'div#'.$cycle_div.' a { width: '.$width.'px; height: '.$height.'px; }'."\n";
			$output .= 'div#'.$cycle_div.' img { width: '.$width.'px; height: '.$height.'px; }'."\n";
			$output .= '</style>'."\n";
			$output .= <<<EOF

			<script type="text/javascript">
			jQeR = jQuery.noConflict();

			jQeR(document).ready(function() {
			    jQeR('#{$cycle_div}').cycle({
					fx:    '{$effect}',
					delay: '{$delay}',
					speed: '{$speed}'
				});
			});
			</script>
EOF;

			$output = str_replace( '{$cycle_div}', $cycle_div, $output );
			$output = str_replace( '{$delay}',     $delay,     $output );
			$output = str_replace( '{$speed}',     $speed,     $output );
			$output = str_replace( '{$effect}',    $effect,    $output );

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
			$user_name     = $args['user_name'];
			$image_size    = $args['image_size'];
			$image_version = $args['image_version'];

			foreach ( array_keys( $this->image_prefix ) as $envato_site )
			{
				if($image_version !== 'cycle')
				{	$image_version = $this->image_version[$envato_site];
				}

				// can't simply pass $args here because it's looping on the envato_site
				$output        .= $this->referral_link( array( 'user_name'      => $user_name,
																				'envato_site'   => $envato_site,
																				'image_size'    => $image_size,
																			 	'image_version' => $image_version ) );
			}

			$output .= '</div></br />'."\n";

			return $output;
		}

		// Generates link for a single envato referral
		function shortcode_atts_comment($atts)
		{	
			$output = '<!-- ' . "\n";

			foreach ( array_keys($atts) as $att )
			{
				$output .= $att . '=' . $atts[$att] . "\n";
			}

			$output .= ' -->' . "\n";

			return $output;
		}

		// Generates link for a single envato referral
		function referral_link_shortcode($atts)
		{	
			extract( shortcode_atts( array(
				'user_name'      => $this->defaults['user_name'],
				'envato_site'    => $this->defaults['envato_site'],
				'image_version'  => $this->defaults['image_version'],
				'image_size'     => $this->defaults['image_size'],
				'max_width'      => $this->defaults['max_width']
			), $atts ) );

			$output  = $this->shortcode_atts_comment($atts);
			$output .= $this->referral_link($atts);

			return $output;
		}

		// Generates list for a single envato referral
		function referral_list_shortcode($atts)
		{	
			extract( shortcode_atts( array(
				'user_name'      => $this->defaults['user_name'],
				'envato_site'    => $this->defaults['envato_site'],
				'image_version'  => $this->defaults['image_version'],
				'image_size'     => $this->defaults['image_size'],
				'max_width'      => $this->defaults['max_width']
			), $atts ) );

			$output  = $this->shortcode_atts_comment($atts);
			$output .= $this->referral_list($atts);

			return $output;
		}




	}

} // End Class EnvatoReferral

if (class_exists("EnvatoReferral"))
{	$envato_referral = new EnvatoReferral();
}




?>