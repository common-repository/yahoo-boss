<?php
/*
Plugin Name: Yahoo Boss
Plugin URI: http://www.picklewagon.com/wordpress/yahoo-boss/
Description: Adds a Yahoo Boss Search Engine to your WordPress install.
Author: Josh Harrison
Version: 0.7
Author URI: http://www.picklewagon.com
*/

/**  Copyright 2009
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

if (!class_exists('pw_yahoo_boss')) {
	class pw_yahoo_boss {
		/**
		 * @var string The options string name for this plugin
		 */
		var $fullPluginName = 'Yahoo Boss';
		
		/**
		 * @var string The options string name for this plugin
		 */
		var $optionsName = 'pw_search_options';
		
		/**
		 * @var string $localizationDomain Domain used for localization
		 */
		var $localizationDomain = 'pw_yahoo_boss';
		
		/**
		 * @var string $pluginurl The path to this plugin
		 */
		var $pluginurl = '';

		/**
		 * @var string $pluginurlpath The path to this plugin
		 */
		var $pluginpath = '';
		
		/**
		 * @var array $options Stores the options for this plugin
		 */
		public $options = array();
		
		/**
		 * @var boolean $is_search
		 */
		var $is_search = false;
		
		// Class Functions
		/**
		* PHP 4 Compatible Constructor
		*/
		function pw_yahoo_boss() {
			$this->__construct();
		}
		
		/**
		 * PHP 5 Constructor
		 */
		function __construct() {
			// Load up the localization file if we're using WordPress in a different language
			// Just drop it in this plugin's "localization" folder and name it "pw-yahoo-boss-[value in wp-config].mo"
			load_plugin_textdomain($this->localizationDomain, false, dirname(plugin_basename(__FILE__)) . '/localization');
			
			// "Constants" setup
			$this->pluginurl  = WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
			$this->pluginpath = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)).'/';
			
			// Initialize the options
			$this->get_options();
			
			// Actions
			add_action('admin_menu', array(&$this, 'admin_menu_link'));
			add_action('init', array(&$this, 'do_search'));
			add_action('init', array(&$this, 'init_scripts'));
			
			// Shortcodes
			add_shortcode('boss_search_form', array(&$this, 'show_search_form'));
			add_shortcode('boss_search_results', array(&$this, 'show_search_results'));
			add_shortcode('pw_search', array(&$this, 'show_search'));
			
			// Filters
			add_filter('screen_layout_columns', array(&$this, 'screen_layout_columns'), 10, 2);
			add_filter('query_vars', array(&$this, 'add_search_query_var'));
			// add a link to the settings page
			add_filter( 'plugin_action_links', array(&$this, 'filter_plugin_actions'), 10, 2 );
		}
		
		/**
		 * Retrieves the plugin options from the database.
		 * @return array
		 */
		function get_options() {
			if (!$theOptions = get_option($this->optionsName)) {
				$theOptions = array(
					'pw_search_results_per_page' => 10,
					'pw_search_format'           => 'json',
					'pw_search_domains'          => null,
				);
				update_option($this->optionsName, $theOptions);
			}
			$this->options = $theOptions;
		}
		
		/**
		 * Saves the admin options to the database.
		 */
		function save_admin_options(){
			update_option($this->optionsName, $this->options);
		}
		
		function add_search_query_var($query_vars) {
			$query_vars[] = 'q';
			return $query_vars;
		}
		
		function screen_layout_columns($columns, $screen) {
			if ($screen == $this->option_page_hook) {
				$columns[$screen] = 2;
			}
			return $columns;
		}
		
		/**
		 * @desc Adds the options subpanel
		 */
		function admin_menu_link() {
			$this->option_page_hook = add_options_page($this->fullPluginName, $this->fullPluginName, 'manage_options', 'yahoo-boss-config', array(&$this, 'admin_options_page'));
			
			add_action('load-' . $this->option_page_hook, array(&$this, 'save_options'));
			add_action('load-' . $this->option_page_hook, array(&$this, 'load_options_page'));
		}
		
		public function load_options_page() {
			wp_enqueue_script('post');
			add_meta_box('yahoo-boss-options-div', __('Options', 'pw_yahoo_boss'), array(&$this, 'options_meta_box'), $this->option_page_hook, 'normal', 'high');
			add_meta_box('yahoo-boss-news-div', __('Latest News', 'pw_yahoo_boss'), array(&$this, 'news_meta_box'), $this->option_page_hook, 'side', 'low');
			add_meta_box('yahoo-boss-share-div', __('Share', 'pw_yahoo_boss'), array(&$this, 'share_meta_box'), $this->option_page_hook, 'side', 'low');
			add_meta_box('yahoo-boss-support-div', __('Support', 'pw_yahoo_boss'), array(&$this, 'support_meta_box'), $this->option_page_hook, 'side', 'low');
			add_meta_box('yahoo-boss-donate-div', __('Donate', 'pw_yahoo_boss'), array(&$this, 'donate_meta_box'), $this->option_page_hook, 'side', 'low');
		}
		
		public function news_meta_box() {
?>
			<ul>
				<li><img src="<?php echo $this->pluginurl ?>images/rss.png" alt="" /> <a href="http://picklewagon.com/feed"><?php _e('Subscribe to Picklewagon', 'pw_yahoo_boss') ?></a></li>
				<li><img src="<?php echo $this->pluginurl ?>images/rss.png" alt="" /> <a href="http://picklewagon.com/tag/yahoo-boss/feed"><?php _e('Subscribe to updates specific to this plugin', 'pw_yahoo_boss') ?></a></li>
			</ul>
<?php
		}
		
		public function share_meta_box() {
?>
			<p><a href="http://wordpress.org/extend/plugins/yahoo-boss/"><?php _e('Rate this plugin on WordPress.org', 'pw_yahoo_boss') ?></a></p>
			<p><?php _e('If you are using this plugin in a production environment, <a href="mailto:yahoobossplugin@picklewagon.com">let me know</a> so I can link to you.', 'pw_yahoo_boss') ?></p>
<?php
		}
		
		public function support_meta_box() {
?>
			<p><?php _e('If you have any problems with this plugin or good ideas for improvements or new features, please talk about them in the <a href="http://wordpress.org/tags/yahoo-boss">Support forums</a>.', 'pw_yahoo_boss') ?></p>
<?php
		}
		
		public function donate_meta_box() {
			$donate = __('I\'ve spent a lot of time programming and maintaining this plugin. If it helps you make money, please donate to show your appreciation.', 'pw_yahoo_boss');
			
			echo '<p>'.$donate.'</p>';
			echo '<p><a href="http://picklewagon.com/wordpress/yahoo-boss/donate">'.__('Donate', 'pw_yahoo_boss').'</a></p>';
		}
		
		public function options_meta_box() {
			$domains = ($this->options['pw_search_domains']) ? $this->get_domains("\n") : null;
?>
			<table width="100%" cellspacing="2" cellpadding="5" class="form-table">
				<tr valign="top">
					<th scope="row"><label for="pw_yahoo_app_id"><?php _e('Yahoo App ID:', 'pw_yahoo_boss'); ?></label></th>
					<td><input name="pw_yahoo_app_id" type="text" id="pw_yahoo_app_id" value="<?php echo $this->options['pw_yahoo_app_id'] ;?>" class="regular-text code<?php if (defined('YAHOO_APP_ID')) print ' disabled'; ?>" <?php if (defined('YAHOO_APP_ID')) print 'disabled="disabled" '; ?>/>
					<span class="setting-description"><a href="http://developer.yahoo.com/search/boss"><?php _e('Get your Yahoo! App ID', 'pw_yahoo_boss') ?></a></span></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="pw_search_results_per_page"><?php _e('Results per Page:', 'pw_yahoo_boss'); ?></label></th>
					<td>
						<input name="pw_search_results_per_page" type="text" id="pw_search_results_per_page" value="<?php echo $this->options['pw_search_results_per_page'] ;?>" /><br />
						<span class="description"><?php _e('Total number of results to return. Maximum value is 50. Default sets count to 10.', 'pw_yahoo_boss') ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Format:', 'pw_yahoo_boss'); ?></th>
					<td>
						<input name="pw_search_format" type="radio" id="pw_search_format_json" value="json" <?php if ($this->options['pw_search_format'] == 'json') print 'checked="checked" '?>/> <label for="pw_search_format_json"><?php _e('JSON', 'pw_yahoo_boss') ?></label><br />
						<input name="pw_search_format" type="radio" id="pw_search_format_xml" value="xml" <?php if ($this->options['pw_search_format'] == 'xml') print 'checked="checked" '?>/> <label for="pw_search_format_xml"><?php _e('XML', 'pw_yahoo_boss') ?></label><br />
						<span class="description"><?php _e('The data format of the response.', 'pw_yahoo_boss') ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="pw_search_domains"><?php _e('Domains to Search:', 'pw_yahoo_boss'); ?></label></th>
					<td>
						<textarea name="pw_search_domains" id="pw_search_domains" cols="50" rows="5"><?php echo $domains;?></textarea><br />
						<span class="description"><?php _e('Restrict BOSS search results to a set of pre-defined sites. One domain per line.', 'pw_yahoo_boss') ?></span>
					</td>
				</tr>
			</table>
<?php
		}
		
		/**
		 * @desc Adds the Settings link to the plugin activate/deactivate page
		 */
		function filter_plugin_actions($links, $file) {
			static $this_plugin;
			
			if (!$this_plugin) {
				$this_plugin = plugin_basename(__FILE__);
			}
		
			if( $file == $this_plugin ){
				$settings_link = '<a href="options-general.php?page=' . 'yahoo-boss-config' . '">' . __('Settings', 'pw-yahoo-boss') . '</a>';
				array_unshift( $links, $settings_link ); // before other links
			}
			
			return $links;
		}

		function save_domains($domains) {
			$d = explode("\n", $domains);
			for ($i = 0; $i < count($d); $i++) {
				$d[$i] = trim($d[$i]);
				$d[$i] = str_replace("http://", "", $d[$i]);
			}
			return $d;
		}
		
		function save_options() {
			$this->message = null;
			$error = false;
			if ($_POST['pw_search_save']){       
				if (!wp_verify_nonce($_POST['yahoo_boss_options_nonce'], 'yahoo-boss-options-edit')) {
	    			return;
	  			}
				
				$results = (int) esc_attr($_POST['pw_search_results_per_page']);
				if ($results > 0 && $results <= 50) {
					$this->options['pw_search_results_per_page'] = $results;
				} else {
					$error = true;
					$this->message = __('ERROR: The max results per page is 50. Please enter a number between 1-50.', 'pw_yahoo_boss');
				}
				$this->options['pw_yahoo_app_id'] = esc_attr($_POST['pw_yahoo_app_id']);
				$this->options['pw_search_format'] = esc_attr($_POST['pw_search_format']);
				$this->options['pw_search_domains'] = $this->save_domains(esc_attr($_POST['pw_search_domains']));
				
				$this->save_admin_options();
				
				if (!$error) {
					$this->message = __('Success! Your changes were sucessfully saved!', 'pw_yahoo_boss');
				}
			}
		}
		
		/**
		* Adds settings/options page
		*/
		function admin_options_page() {
			global $screen_layout_columns;
?>
			<div class="wrap">
				<?php screen_icon(); ?>
				<h2><?php echo $this->fullPluginName; ?></h2>
				<?php if ( $this->message ) : ?>
				<div id="message" class="updated"><p><?php echo $this->message; ?></p></div>
				<?php endif; ?>
				<form method="post" id="pw_search_options">
				<input type="hidden" name="yahoo_boss_options_nonce" id="yahoo_boss_options_nonce" value="<?php echo wp_create_nonce('yahoo-boss-options-edit') ?>" />
				<div id="poststuff" class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
					<div id="side-info-column" class="inner-sidebar">
						<?php do_meta_boxes( $this->option_page_hook, 'side', null ); ?>
					</div>
					<div id="post-body">
						<div id="post-body-content">
							<?php do_meta_boxes( $this->option_page_hook, 'normal', null ); ?>
							<p class="submit"><input type="submit" class="button-primary" name="pw_search_save" value="<?php _e('Save Changes', 'pw_yahoo_boss') ?>" /></p>
						</div>
					</div>
				</div>
				</form>
			</div>
<?php
		}
		
		function init_scripts() {
			$src = str_replace(get_bloginfo('url'), '', $this->pluginurl);
			$default_stylesheet = $src . 'search-styles.css';
			
			$stylesheet = apply_filters('pw_search_stylesheet', $default_stylesheet);
			wp_enqueue_style('pw-search-styles', $stylesheet);
		}

		function get_domains($separator = ',') {
			return implode($separator, $this->options['pw_search_domains']);
		}
		
		function get_format() {
			return $this->options['pw_search_format'];
		}
		
		// Finds [pw_search_results] shortcode and produces the search results in the content
		function show_search_results() {
			if ($this->is_search) {
				pw_search();
			}
		}
		
		// Finds [pw_search_form] shortcode and shows the search form in the content
		function show_search_form() {
			pw_search_form();
		}
		
		function show_search() {
			pw_search_form();
			
			if ($this->is_search) {
				pw_search();
			}
		}
		
		function do_search() {
			include_once('yahoo-boss-results.php');
			
			if (!empty($_GET['q'])) {
				$GLOBALS['pw_search_results'] = new pw_yahoo_boss_results($this);
				if ($GLOBALS['pw_search_results']) {
					$this->is_search = true;
				}
			}
		}
		
		function get_yahoo_app_id() {
			$app_id = false;
			if (defined('YAHOO_APP_ID')) {
				$app_id = YAHOO_APP_ID;
			} else {
				$app_id = $this->options['pw_yahoo_app_id'];
			}
			
			return $app_id;
		}
	} //End Class
} //End if class exists statement

//instantiate the class
if (class_exists('pw_yahoo_boss')) {
    $pw_search = new pw_yahoo_boss();
}

include_once('template-functions.php');

// give the admin a warning if the yahoo app id has not been set
if ( !$pw_search->get_yahoo_app_id() && !isset($_POST['pw_yahoo_boss_save']) ) {
	function pw_yahoo_boss_warning() {
		echo "<div id='yahoo-boss-warning' class='updated fade'><p><strong>".__('Yahoo! BOSS is almost ready.', 'pw_yahoo_boss')."</strong> ".sprintf(__('You must <a href="%1$s">add your Yahoo! app ID</a> for it to work.', 'pw_yahoo_boss'), '"options-general.php?page=yahoo-boss-config')."</p></div>";
	}
	add_action('admin_notices', 'pw_yahoo_boss_warning');
	return;
}
?>