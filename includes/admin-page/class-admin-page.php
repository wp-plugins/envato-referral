<?php

/**
 * Abstraction of building new option pages in the WordPress ACP
 * 
 * This class deliver a simple way to add new pages to
 * the WordPress ACP and fill them with forms and stuff.
 * It also handles all the database stuff for you.
 * 
 * Based on original adminpage.class.php by Markus Thömmes (merguez@semantifiziert.de)
 * 
 * @author Brian Lasher (me@brianlasher.com) bassed
 * @copyright Copyright 2010, Brian Lasher
 * @version 1.3
 * @since 22.08.2010
 * 
 */

 if(!class_exists('AdminPage')) {
class AdminPage {
	/**
     * Contains the menu_slug for the current TopLeve-Menu
     * @var string
     */
	public $top;
	
	/**
     * Contains all arguments needed to build the page itself
     * @var array
     */
	protected $args;
	
	/**
     * Contains all the information needed to build the form structure of the page
     * @var array
     */
	private $form_rows;
	
	/**
     * True if the table is opened, false if it is not opened
     * @var boolean
     */
	private $table = false;
	
	/**
     * Contains the HTML for building the contextual help
     * @var string
     */
	private $help;
	

	/**
     * not sure
     * @var string
     */
	var $sg = null;

	/**
     * Adds an input field to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Further description for this element
	 *  > standard (string) (optional) - This is the standard value of your input
	 *  > size (string) (optional) - sets the width, can be: small, short, regular and large
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addInput($args) {
		$default = array(
			'size' => 'regular',
		);
		$args = array_merge($default, $args);
		$args['type'] = 'input';
		$this->addField($args);
	}
	
	/**
     * Adds a textarea to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Further description for this element
	 *  > standard (string) (optional) - This is the standard value of your field
	 *  > rows (integer) (optional) - The number of rows you want to have, standard: 5
	 *  > cols (integer) (optional) - The number of cols you want to have, standard: 30
	 *  > width (integer) (optional) - How wide should the textarea be?, standard:500
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addTextarea($args) {
		$default = array(
			'rows' => 5,
			'cols' => 30,
			'width' => 500,
		);
		$args = array_merge($default, $args);
		$args['type'] = 'textarea';
		$this->addField($args);
	}
	
	/**
     * Adds a TinyMCE editor to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Further description for this element
	 *  > standard (string) (optional) - This is the standard value of your input
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addEditor($args) {
		$args['type'] = 'editor';
		$this->addField($args);
	}
	
	/**
     * Adds a heading to the current page
	 *
     * @param string $label simply the text for your heading
     */
	public function addTitle($label) {
		$args['type'] = 'title';
		$args['label'] = $label;
		$this->addField($args);
	}
	
	/**
     * Adds a sub-heading to the current page
	 *
     * @param string $label simply the text for your heading
     */
	public function addSubtitle($label) {
		$args['type'] = 'subtitle';
		$args['label'] = $label;
		$this->addField($args);
	}
	
	/**
     * Adds a paragraph to the current page
	 *
     * @param string $text the text you want to display
     */
	public function addParagraph($text) {
		$args['type'] = 'paragraph';
		$args['text'] = $text;
		$this->addField($args);
	}
	
	/**
     * Adds a checkbox to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) - Further description for this element
	 *  > standard (bool) - Define wether the checkbox should be checked our unchecked
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addCheckbox($args) {
		$args['type'] = 'checkbox';
		$this->addField($args);
	}
	
	/**
     * Adds radiobuttons field to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > standard (string) (optional) - Define which of the options should be checked if there is nothing in the database
	 *  > options (array) - An array containing the options to choose from, written in this style: LABEL => VALUE
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addRadiobuttons($args) {
		$args['type'] = 'radio';
		$this->addField($args);
	}
	
	/**
     * Adds a dropdown field to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Describes your field very shortly
	 *  > standard (string) - Define which of the options should be checked if there is nothing in the database
	 *  > options (array) - An array containing the options to choose from, written in this style: LABEL => VALUE
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addDropdown($args) {
		$args['type'] = 'dropdown';
		$this->addField($args);
	}
	
	/**
     * Adds an upload to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Describes your field very shortly
	 *  > title (string) (optional) - If set, an input is added to the uploader where you can put additional info
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addUpload($args) {
		$args['type'] = 'upload';
		$this->addField($args);
	}
	
	/**
     * Adds a slider to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > standard (integer|array) (optional) - The starting position of your slider, if it is an array, a range slider is build
	 *  > max (integer) - The maximum value of your slider
	 *  > min (integer) - The minimum value of your slider
	 *  > step (integer) - The stepsize of your slider
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addSlider($args) {
		$default = array(
			'standard' => 0,
			'max' => 100,
			'min' => 0,
			'step' => 1,
		);
		$args = array_merge($default,$args);
		$args['type'] = 'slider';
		$this->addField($args);
	}
	
	/**
     * Adds a datepicker to the current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Describes your field very shortly
	 *  > standard (string) (optional) - The standard date in the format: MM/DD/YYYY
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addDate($args) {
		$args['type'] = 'date';
		$date = explode('/', $args['standard']);
		if(isset($date[2])) $args['standard'] = mktime(0,0,0,$date[0],$date[1],$date[2]);
		$this->addField($args);
	}
	
	/**
     * Adds a Submit current page
	 *
	 * Possible keys within $args:
	 *  > id (string) - This is what you need to get your variable from the database
	 *  > label (string) - Describes your field very shortly
	 *  > desc (string) (optional) - Describes your field very shortly
	 *  > standard (string) (optional) - The standard date in the format: MM/DD/YYYY
	 *
     * @param array $args contains everything needed to build the field
     */
	public function addSubmit($args) {
		$args['type'] = 'submit';
		$this->addField($args);
	}
	
	/**
	 * Adds some contextual help (shows when you click the little 'help' tab)
	 *
	 * @param string $html contains the html code for your help
	 */
	public function addHelp($html) {
		$this->help = $html;
		add_action('admin_menu', array($this, 'renderHelp'));
	}
	
	/**
     * Does the repetive tasks of adding a field
	 * @access private
     */
	private function addField($args) {
		$this->buildOptions($args);
		$this->form_rows[] = $args;
	}
	
	/**
     * Builds all the options with their standard values
	 * @access private
     */
	private function buildOptions($args) {
		$default = array(
			'standard' => '',
		);
		$args = array_merge($default, $args);
		add_option($args['id'], $args['standard']);
	}
	
	/**
	 * Builds the contextual help for you
	 * @access private
	 */
	public function renderHelp() {
		add_contextual_help($this->args['slug'], $this->help);
	}

	/**
	 * Builds a box header
	 * @access private
	 */
	private function box_header($id, $title, $right = false)
	{	
		?>
		<div id="<?php echo $id; ?>" class="postbox" style="display:block;">
			<h3 class="hndle"><span><?php echo $title ?></span></h3>
			<div class="inside">
		<?php
	}
	
	/**
	 * Builds a box footer
	 * @access private
	 */
	private function box_footer( $right = false)
	{
		echo "</div></div>\n\n\n";
	}


	/**
     * Outputs all the HTML needed for the plugin_info_box
	 * @access private
     */
	private function plugin_info_box()
	{
		$this->box_header( 'plugin_info', __('About this Plugin:', 'sitemap') );
		?>
		<a class="button pluginHome"     href="<?php // echo $this->GetRedirectLink('sitemap-home'); ?>"><?php _e('Plugin Homepage', 'sitemap'); ?></a>
		<a class="button pluginFeedback" href="<?php // echo $this->GetRedirectLink('sitemap-feedback'); ?>"><?php _e('Suggest a Feature', 'sitemap'); ?></a>
		<a class="button pluginList"     href="<?php // echo $this->GetRedirectLink('sitemap-list'); ?>"><?php _e('Notify List', 'sitemap'); ?></a>
		<a class="button pluginSupport"  href="<?php // echo $this->GetRedirectLink('sitemap-support'); ?>"><?php _e('Support Forum', 'sitemap'); ?></a>
		<a class="button pluginBugs"     href="<?php // echo $this->GetRedirectLink('sitemap-bugs'); ?>"><?php _e('Report a Bug', 'sitemap'); ?></a>
		<a class="button donatePayPal"   href="<?php // echo $this->GetRedirectLink('sitemap-paypal'); ?>"><?php _e('Donate with PayPal', 'sitemap'); ?></a>
		<?php 
		$this->box_footer();
	}

	/**
     * Outputs all the HTML needed for plugin_resources_box
	 * @access private
     */
	private function plugin_resources_box()
	{
		$this->box_header( 'plugin_resources', __('Resources:', 'sitemap') );
		?>
		<a class="button resGoogle"    href="<?php // echo $this->GetRedirectLink('gwt'); ?>"><?php _e('Webmaster Tools', 'sitemap'); ?></a>
		<a class="button resGoogle"    href="<?php // echo $this->GetRedirectLink('gwb'); ?>"><?php _e('Webmaster Blog', 'sitemap'); ?></a>
		
		<a class="button resYahoo"     href="<?php // echo $this->GetRedirectLink('yse'); ?>"><?php _e('Site Explorer', 'sitemap'); ?></a>
		<a class="button resYahoo"     href="<?php // echo $this->GetRedirectLink('ywb'); ?>"><?php _e('Search Blog', 'sitemap'); ?></a>
		
		<a class="button resBing"      href="<?php // echo $this->GetRedirectLink('lwt'); ?>"><?php _e('Webmaster Tools', 'sitemap'); ?></a>
		<a class="button resBing"      href="<?php // echo $this->GetRedirectLink('lswcb'); ?>"><?php _e('Webmaster Center Blog', 'sitemap'); ?></a>
		<br />
		<a class="button resGoogle"    href="<?php // echo $this->GetRedirectLink('prot'); ?>"><?php _e('Sitemaps Protocol', 'sitemap'); ?></a>
		<a class="button resGoogle"    href="<?php // echo $this->GetRedirectLink('ofaq'); ?>"><?php _e('Official Sitemaps FAQ', 'sitemap'); ?></a>
		<a class="button pluginHome"   href="<?php // echo $this->GetRedirectLink('afaq'); ?>"><?php _e('My Sitemaps FAQ', 'sitemap'); ?></a>
		<?php 
		$this->box_footer(true);
	}


	/**
     * Outputs all the HTML needed for recent_donations_box
	 * @access private
     */
	private function recent_donations_box()
	{
		$this->box_header( 'dm_donations', __('Recent Donations:', 'sitemap') );
		?>

		<?php
		// if( $this->sg->GetOption('i_hide_donors')!==true )
		{ ?>
			<iframe border="0" frameborder="0" scrolling="no" allowtransparency="yes" style="width:100%; height:80px;" src="<?php // echo $this->sg->GetRedirectLink('sitemap-donorlist'); ?>">
			<?php _e('List of the donors', 'sitemap'); ?>
			</iframe><br />
			<a href="<?php // echo $this->sg->GetBackLink() . "&amp;hidedonors=true"; ?>"><small><?php _e('Hide this list', 'sitemap'); ?></small></a><br /><br />
		<?php
		} ?>

		<a style="float:left; margin-right:5px; border:none;" href="javascript:document.getElementById('donate_form').submit();"><img style="vertical-align:middle; border:none; margin-top:2px;" src="<?php // echo $this->sg->GetPluginUrl(); ?>img/icon-donate.gif" border="0" alt="PayPal" title="Help me to continue support of this plugin :)" /></a>
		<span><small><?php _e('Thanks for your support!', 'sitemap'); ?></small></span>
		<div style="clear:left; height:1px;"></div>

		<?php
		$this->box_footer();


	}


	/**
	 * Defines a link pointing to a specific page of the authors website
	 * 
	 * @since 3.0
	 * @param The page to link to
	 * @return string The full url
	 */
	function SetRedirectLink($redir) {
		return trailingslashit("http://www.arnebrachhold.de/redir/" . $redir);
	}
	

	/**
	 * Returns a link pointing to a specific page of the authors website
	 * 
	 * @since 3.0
	 * @param The page to link to
	 * @return string The full url
	 */
	function GetRedirectLink($redir) {
		return trailingslashit("http://www.arnebrachhold.de/redir/" . $redir);
	}
	

	/**
     * Outputs all the HTML needed for the sidebar
	 * @access private
     */
	private function inner_sidebar_css()
	{
		$this->url = plugins_url( '', __FILE__ );
		?>

		<style>
		div.inner_sidebar { position:relative; display:block; float:right; clear:right; width:281px; }
		li.hint { color:green;}
		li.optimize { color:orange;}
		li.error { color:red;}
		input.warning:hover { background: #ce0000; color: #fff;}
		a.button         { padding:4px; display:block; padding-left:25px; background-repeat:no-repeat; background-position:5px 50%; text-decoration:none; border:none; }
		a.button:hover   { border-bottom-width:1px; }
		a.pluginHome     { background-image:url(<?php echo $this->url; ?>/images/favicon-wpdj.png); }
		a.pluginFeedback { background-image:url(<?php echo $this->url; ?>/images/favicon-wpdj.png); }
		a.pluginList     { background-image:url(<?php echo $this->url; ?>/images/favicon-email.gif); }
		a.pluginSupport  { background-image:url(<?php echo $this->url; ?>/images/favicon-wordpress.png); }
		a.pluginBugs     { background-image:url(<?php echo $this->url; ?>/images/favicon-trac.gif); }
		a.donatePayPal   { background-image:url(<?php echo $this->url; ?>/images/favicon-paypal.gif); }
		a.donateAmazon   { background-image:url(<?php echo $this->url; ?>/images/favicon-amazon.gif); }
		a.resGoogle      { background-image:url(<?php echo $this->url; ?>/images/favicon-google.gif); }
		a.resYahoo       { background-image:url(<?php echo $this->url; ?>/images/favicon-yahoo.gif); }
		a.resBing        { background-image:url(<?php echo $this->url; ?>/images/favicon-bing.gif); }
		div.sm-update-nag p { margin:5px; }
		.sm-padded .inside { margin:12px!important; }
		.sm-padded .inside ul { margin:6px 0 12px 0; }
		.sm-padded .inside input { padding:1px; margin:0; }
		</style>

		<?php
	}

	
	/**
     * Outputs all the HTML needed for the sidebar
	 * @access private
     */
	private function inner_sidebar()
	{
		?>
		<div class="inner_sidebar">
		<?php
			$this->inner_sidebar_css();
			$this->plugin_info_box();
		  	$this->plugin_resources_box();
		  	$this->recent_donations_box();
		?>
		</div>
		<?php
	}

	
	/**
     * Outputs all the HTML needed for the new page
	 * @access private
     */
	public function outputHTML()
	{
		echo '<div class="wrap">';
		echo '<h2>'.$this->args['page_title'].'</h2>';
		echo '<form method="post" action="" enctype="multipart/form-data">';
		if($_POST['action'] == 'save') {
			echo '<div class="updated settings-error"><p><strong>'.__('Settings saved.').'</strong></p></div>';
			$this->save();
		} elseif($_GET['action'] == 'reset') {
			foreach($this->form_rows as $form_row) {
				update_option($form_row['id'], $form_row['standard']);
			}
			header('Location: '.$_SERVER['HTTP_REFERER']) ;
		}

		echo '<style>.editorcontainer { -webkit-border-radius:6px; border:1px solid #DEDEDE;}</style>';
		echo '<style>#main_form_box { max-width: 700px }</style>';
		echo '<style>.postbox h3 { margin: 0; padding: 7px 10px; line-height: 1; font-size: 15px; font-weight: normal; font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif; }</style>';
		//		echo '<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/base/jquery-ui.css" rel="stylesheet" />';
		//		echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>';
		//		echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js"></script>';

		$this->inner_sidebar();

		$this->box_header('main_form_box', 'Plugin Options');
		foreach($this->form_rows as $form_row)
		{
			if($form_row['type'] != 'title' AND $form_row['type'] != 'paragraph' AND $form_row['type'] != 'subtitle' AND $form_row['type'] != 'submit') {
				if(!$this->table) {
					echo '<table class="form-table">';
					$this->table = true;
				}
				echo '<tr valign="top">';
				echo '<th><label for="'.$form_row['id'].'">'.$form_row['label'].':</label></th>';
			} else {
				if($this->table) {
					echo '</table>';
					$this->table = false;
				}
			}
			
			$data = get_option($form_row['id']);
			switch($form_row['type']) {
				case 'title':
					echo '<h4>'.$form_row['label'].'</h4>';
					break;
				case 'subtitle':
					echo '<h5>'.$form_row['label'].'</h5>';
					break;
				case 'paragraph':
					echo '<p>'.$form_row['text'].'</p>';
					break;
				case 'input':
					$data = htmlspecialchars(stripslashes($data));
					echo '<td><input type="text" class="'.$form_row['size'].'-text" name="'.$form_row['id'].'" id="'.$form_row['id'].'" value="'.$data.'" /> <span class="description">'.$form_row['desc'].'</span></td>';
					break;
					
				case 'textarea':
					$data = stripslashes($data);
					echo '<td><textarea rows="'.$form_row['rows'].'" cols="'.$form_row['cols'].'" style="width:'.$form_row['width'].'px" name="'.$form_row['id'].'" id="'.$form_row['id'].'">'.$data.'</textarea> <br><span class="description">'.$form_row['desc'].'</span></td>';
					break;
					
				case 'editor':
					wp_tiny_mce();
					$data = stripslashes($data);
					echo '<td><div class="editorcontainer"><textarea class="theEditor" id="'.$form_row['id'].'" name="'.$form_row['id'].'">'.$data.'</textarea></div><span class="description">'.$form_row['desc'].'</span></td>';
					break;
				
				case 'checkbox':
					if($data == 'true') {
						$checked = 'checked="checked"';
					} else {
						$checked = '';
					}
					echo '<td><input type="checkbox" name="'.$form_row['id'].'" id="'.$form_row['id'].'" value="true" '.$checked.' /> <label for="'.$form_row['id'].'">'.$form_row['desc'].'</label></td>';
					break;
					
				case 'radio':
					echo '<td>';
					foreach($form_row['options'] as $label=>$value) {
						if($data == $value) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						echo '<input type="radio" name="'.$form_row['id'].'" id="'.$form_row['id'].'_'.$value.'" value="'.$value.'" '.$checked.' /> <label for="'.$form_row['id'].'_'.$value.'">'.$label.'</label><br>';
					}
					echo '</td>';
					break;
					
				case 'dropdown':
					echo '<td>';
					echo '<select name="'.$form_row['id'].'" id="'.$form_row['id'].'">';
					foreach($form_row['options'] as $label=>$value) {
						if($data == $value) {
							$selected = 'selected="selected"';
						} else {
							$selected = '';
						}
						echo '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
					}
					echo '</select> <span class="description">'.$form_row['desc'].'</span>';
					echo '</td>';
					break;
					
				case 'upload':
					echo '<td>';
					echo '<div style="-webkit-border-radius:6px; border:1px solid #DEDEDE; padding:10px; position:relative; background:#FFF;">';
					echo '<div style="float:left"><input type="file" name="'.$form_row['id'].'" id="'.$form_row['id'].'" /> <span class="description">'.$form_row['desc'].'</span>';
					if(isset($form_row['title'])) echo '<br><br><input type="text" class="regular-text" name="'.$form_row['id'].'_title" id="'.$form_row['id'].'_title" value="'.$data['title'].'" /> <span class="description">'.$form_row['title'].'</span>';
					echo '</div>';
					if(strpos($data['type'], 'image') !== false) {
						echo '<img height="75" src="'.$data['url'].'" style="float:right" />';
					} else {
						echo '<p style="float:right"><strong>'.__('Current').':</strong> '.$data['url'].'</p>';
					}
					echo '<div style="clear:both"></div>';
					echo '</div>';
					echo '</td>';
					break;
					
				case 'slider':
					$show = $data;
					if(is_array($show)) $show = implode('-',$show);
					echo '<td>';
					echo '<div style="width:30%" id="'.$form_row['id'].'-slider" class="ui-slider"></div>';
					echo '<div id="'.$form_row['id'].'-handle">'.$show.'</div>';
					echo '<input type="hidden" name="'.$form_row['id'].'" id="'.$form_row['id'].'" value="'.$show.'" />';
					echo '<script type="text/javascript">jQuery("#'.$form_row['id'].'-slider").slider({';
					if(!is_array($data)) {
						echo 'value: '.$data.',';
					} else {
						echo 'range: true,';
						echo 'values: ['.implode(',',$data).'],';
					}
					echo 'step:' .$form_row['step'].',';
					echo 'max: '.$form_row['max'].',';
					echo 'min: '.$form_row['min'].',';
					if(!is_array($data)) {
						echo 'slide: function(e,ui) { jQuery("#'.$form_row['id'].'-handle").text(ui.value); jQuery("#'.$form_row['id'].'").val(ui.value); },';
					} else {
						echo 'slide: function(e,ui) { jQuery("#'.$form_row['id'].'-handle").text(ui.values[0]+"-"+ui.values[1]); jQuery("#'.$form_row['id'].'").val(ui.values[0]+"-"+ui.values[1]); },';
					}
					echo '}); </script>';
					echo '</td>';
					break;
					
				case 'date':
					if(strlen($data) > 0) $data = date('m/d/Y',$data);
					echo '<td><input type="text" name="'.$form_row['id'].'" id="'.$form_row['id'].'" value="'.$data.'" /> <span class="description">'.$form_row['desc'].'</span></td>';
					echo '<script type="text/javascript">jQuery("#'.$form_row['id'].'").datepicker();</script>';
					break;
				case 'submit':
					echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.esc_attr(__('Save Changes')).'" /> <a href="'.$_SERVER['REQUEST_URI'].'&action=reset" class="button">Reset</a></p>';
					echo '<input type="hidden" name="action" value="save" />';
					break;
			}
			if($form_row['type'] != 'title' AND $form_row['type'] != 'paragraph' AND $form_row['type'] != 'subtitle') echo '</tr>';
			if($this->table = true) echo '</table>';
		}
		$this->box_footer();

		echo '</form></div>';

	}
	
	/**
     * Puts all our data to the database
	 * @access private
     */
	private function save() {
		foreach($this->form_rows as $form_row) {
			$data = $_POST[$form_row['id']];
			if($form_row['type'] == 'editor') {
				$data = wptexturize(wpautop($data));
			}
			if($form_row['type'] == 'checkform_row') {
				if($data != 'true') {
					$data = 'false';
				}
			}
			if($form_row['type'] == 'upload') {
				if($_FILES[$form_row['id']]['size'] > 0) {
					$data = wp_handle_upload($_FILES[$form_row['id']], array('test_form' => false));
				} else {
					$data = get_option($form_row['id']);
				}
				$data['title'] = $_POST[$form_row['id'].'_title'];
			}
			if($form_row['type'] == 'slider') {
				if(strpos($data, '-') !== false) {
					$data = explode('-',$data);
				}
			}
			if($form_row['type'] == 'date') {
				$date = explode('/', $data);
				if(isset($date[2])) $data = mktime(0,0,0,$date[0],$date[1],$date[2]);
			}
			update_option($form_row['id'], $data);
		}
	}
	
	/**
     * Loads all the script and css files needed
	 * @access private
     */
	public function loadScripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('jquery-color');
		wp_admin_css('thickbox');
		wp_print_scripts('post');
		wp_print_scripts('media-upload');
		wp_print_scripts('jquery');
		wp_print_scripts('jquery-ui-core');
		wp_print_scripts('jquery-ui-tabs');
		wp_print_scripts('tiny_mce');
		wp_print_scripts('editor');
		wp_print_scripts('editor-functions');
		add_thickbox();
		wp_admin_css();
		wp_enqueue_script('utils');
		do_action("admin_print_styles-post-php");
		do_action('admin_print_styles');
		remove_all_filters('mce_external_plugins');
	}
}
}

if(!class_exists('TopPage')) {
class TopPage extends AdminPage {
	/**
     * Builds a new Top-Level-Menu
	 *
	 * Possible keys within $args:
	 *  > menu_title (string) - The name of the Top-Level-Menu
	 *  > page_title (string) - The name of the first page of the menu
	 *  > menu_slug (string) - A unique string identifying your new menu
	 *  > capability (string) (optional) - The capability needed to view the page
	 *  > icon_url (string) (optional) - URL to the icon, decorating the Top-Level-Menu
	 *  > position (string) (optional) - The position of the Menu in the ACP
	 *
     * @param array $args contains everything needed to build the menu
     */
	public function __construct($args) {
		$this->args = $args;
		$this->top = $this->args['menu_slug'];
		add_action('admin_menu', array($this, 'renderTopPage'));
		add_action('admin_head', array($this, 'loadScripts'));
	}
	
	/**
     * Does all the complicated stuff to build the menu and its first page
	 * @access private
     */
	public function renderTopPage() {
		$default = array(
			'capability' => 'edit_themes',
		);
		$this->args = array_merge($default, $this->args);
		add_menu_page($this->args['page_title'], $this->args['menu_title'], $this->args['capability'], $this->args['menu_slug'], array($this, 'outputHTML'), $this->args['icon_url'], $this->args['position']);
		$this->args['slug'] = add_submenu_page($this->args['menu_slug'], $this->args['page_title'], $this->args['page_title'], $this->args['capability'], $this->args['menu_slug'], array($this, 'outputHTML'));
	}
}
}

if(!class_exists('SubPage')) {
class SubPage extends AdminPage {
	/**
     * Builds a new Top-Level-Menu
	 *
	 * Possible keys within $args:
	 *  > page_title (string) - The name of this page
	 *  > capability (string) (optional) - The capability needed to view the page
	 *
	 * @param string|object $top contains the name of the parent Top-Level-Menu or a TopPage object
     * @param array|string $args contains everything needed to build the menu, if just a string it's the name of the page
     */
	public function __construct($top, $args) {
		if(is_object($top)) {
			$this->top = $top->top;
		} else {
			switch($top) {
				case 'posts':
					$this->top = 'edit.php';
					break;
				
				case 'dashboard':
					$this->top = 'index.php';
					break;
				
				case 'media':
					$this->top = 'upload.php';
					break;
				
				case 'links':
					$this->top = 'link-manager.php';
					break;
				
				case 'pages':
					$this->top = 'edit.php?post_type=page';
					break;
				
				case 'comments':
					$this->top = 'edit-comments.php';
					break;
				
				case 'theme':
					$this->top = 'themes.php';
					break;
				
				case 'plugins':
					$this->top = 'plugins.php';
					break;
				
				case 'users':
					$this->top = 'users.php';
					break;
				
				case 'tools':
					$this->top = 'tools.php';
					break;
				
				case 'settings':
					$this->top = 'options-general.php';
					break;
			
				default:
					if(post_type_exists($top)) {
						$this->top = 'edit.php?post_type='.$top;
					} else {
						$this->top = $top;
					}
			}
		}
		if(is_array($args)) {
			$this->args = $args;
		} else {
			$array['page_title'] = $args;
			$this->args = $array;
		}
		add_action('admin_menu', array($this, 'renderSubPage'));
		add_action('admin_head', array($this, 'loadScripts'));
	}
	
	/**
     * Does all the complicated stuff to build the page
	 * @access private
     */
	public function renderSubPage() {
		$default = array(
			'capability' => 'edit_themes',
		);
		$this->args = array_merge($default, $this->args);
		$this->args['slug'] = add_submenu_page($this->top, $this->args['page_title'], $this->args['page_title'], $this->args['capability'], $this->createSlug(), array($this, 'outputHTML'));
	}
	
	/**
     * Creates an unique slug out of the page_title and the current menu_slug
	 * @access private
     */
	private function createSlug() {
		$slug = $this->args['page_title'];
		$slug = strtolower($slug);
		$slug = str_replace(' ','_',$slug);
		return $this->top.'_'.$slug;
	}
}
}

?>