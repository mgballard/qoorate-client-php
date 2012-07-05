<?php
/*
Plugin Name: Qoorate contributions platform comments
Plugin URI: http://qrate.co/
Description: This plugin enables you to add the Qoorate Contribution Platform to your Wordpress installation replacing the built in commenting system.
Author: Seth Murphy <seth@brooklyncode.com>
Version: 0.1
Author URI: http://brooklyncode.com/
l*/

/*.
	require_module 'standard';
	require_module 'pcre';
	require_module 'mysql';
.*/
// The vars needed for the proxy calls

// These values change per client
$api_key = get_option('qoorate_api_key', '');
$api_secret = get_option('qoorate_api_secret', '');
$api_shortname = get_option('qoorate_api_shortname', '');

define( 'QOORATE_API_KEY', $api_key );
define( 'QOORATE_API_SECRET', $api_secret );
define( 'QOORATE_API_SHORTNAME', $api_shortname );

// These are hard coded because we don't want to load WP
// for calls to the proxy after the page is loaded
// These are the same for all clients for now
//define( 'QOORATE_SHORTNAME', $q_shortname );
//define( 'QOORATE_BASE_URI', $q_base );
//define( 'QOORATE_UPLOADER_URI', $q_upload );
//define( 'QOORATE_FEED_URI', $q_feed );
//define( 'QOORATE_EMBED_URI', $q_embed_uri );
//define( 'QOORATE_JSON_URI', $q_json_uri );


/**
 * Returns an array of all option names needed for Qoorate
 * @return array[int] string
 */
function qoorate_install() {
	add_option('qoorate_api_key', '');
	add_option('qoorate_api_secret', '');
	add_option('qoorate_api_shortname', '');
}

// add our filters
add_filter('plugins_loaded', 'qoorate_plugins_loaded');
add_filter('comments_template', 'qoorate_comments_template');

// This is the first filter run, so except for a bad plugin we should not have sent the headers yet
function qoorate_plugins_loaded() {
	// Set the cookies needed for qoorate
	if( ! isset( $_COOKIE ) || ! array_key_exists( "QOOID", $_COOKIE ) ) {
		setcookie( "QOOID", uniqid( "QOO" ) ); // sessionid
	}
	if( ! isset( $_COOKIE ) || ! array_key_exists( "QOOTID", $_COOKIE ) ) {
		setcookie( "QOOTID", uniqid("QOOT"), time() + 630720000); // tracking id
	}
}

$qoorate = new Qoorate;

class Qoorate {
	public $status_message = '';
	private $ajax = '';
	function Qoorate() {
		$this->__construct();
	}
	function __construct() {
		// constructor
		// Do our install stuff upon activation
		register_activation_hook( __FILE__, array( &$this, 'install') );
		
		// Do our uninstall stuff upon activation
		// WARNING: Removes all data
		register_deactivation_hook( __FILE__, array( &$this, 'uninstall') );

		// Add the admin menu
		add_action( 'admin_menu', array( &$this, 'menu') );
		
		// used to embed a profiles anywhere
		// NOT sure how to do this yet
		// we need a hook to comments
		//add_filter( 'the_content', array( &$this, 'profile') );
		
		add_action('wp_print_styles', array(&$this, 'add_qoorate_stylesheet'));

		add_filter('comments_template', 'qoorate_comments_template');
	}

	function install () {
		require( WP_PLUGIN_DIR . '/qoorate/qoorate_install.php' );
		$q_install = new Qoorate_Install;
		$q_install->create_options(); // make sure we have our default options
	}

	function uninstall () {
		require( WP_PLUGIN_DIR . '/qoorate/qoorate_install.php' );
		$q_install = new Qoorate_Install;
		$q_install->delete_options(); // remove our options
	}

	
	/*********************************************************
	 *  Retrieve our CSS and JS files from qrate.co
	 *  Loads on every page, but it is small ...
	 *  Does NOT include any CSS for the admin section
	 *  Themes should overide the styles if they wish
	 ********************************************************/
	function add_qoorate_stylesheet(){
		// TODO: make call to get styles from QOORATE
		// MAYBE TODO: cacheing ... place in DB options?
		
		//$styleUrl = plugins_url('/css/guthrie.css', __FILE__);
		//$styleFile = WP_PLUGIN_DIR . '/guthrie/css/guthrie.css';
		//if ( file_exists($styleFile) ) {
		//		//echo($styleUrl."<br />");
		//		wp_register_style('guthrie', $styleUrl);
		//		wp_enqueue_style('guthrie');
		//}
		// unregister our really old jquery bundled with WP
		// Maybe we can keep it? should it be an option?
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js' );
		wp_enqueue_script( 'jquery' );
	}

	function add_qoorate_scripts(){
		// TODO: make call to get styles from QOORATE
		// MAYBE TODO: cacheing ... place in DB options?		
		// unregister our really old jquery bundled with WP
		// Maybe we can keep it? should it be an option?
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js' );
		wp_enqueue_script( 'jquery' );
	}


	/**
	 * Generic function to show a message to the user using WP's
	 * standard CSS classes to make use of the already-defined
	 * message colour scheme.
	 *
	 * @param $message The message you want to tell the user.
	 * @param $errormsg If true, the message is an error, so use
	 * the red message style. If false, the message is a status
	  * message, so use the yellow information message style.
	 */
	function show_message( $message, $errormsg = false )
	{
		if ( $errormsg ) {
			echo '<div id="message" class="error">';
		} else {
			echo '<div id="message" class="updated fade">';
		}
		echo "<p><strong>$message</strong></p></div>";
	}

	/**
	 * Just show our message (with possible checking if we only want
	 * to show message to certain users.
	 */
	function show_admin_messages($errormsg = false)
	{
		if($this->status_message != '') {
	  $this->show_message( $this->status_message, $errormsg );
		$this->status_message = '';
	}
	}

	function menu() {
		$mypage = add_options_page( 'Qoorate Options', 'Qoorate', 'manage_options', 'qoorate', array( &$this, 'options') );
		add_action( "admin_print_scripts-$mypage", array(&$this,'admin_head') );
		add_action( 'admin_notices', array( &$this, 'show_admin_messages' ) );
	}
	
	/* manage our plugin option in admin */
	function options() {
		if ( ! current_user_can( 'manage_options' ) )	{
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include WP_PLUGIN_DIR . '/qoorate/admin_options_page.php';
	}
	
	function admin_head() {
		$this->admin_print_scripts();
		$this->admin_print_styles();
		wp_print_styles();
	}
	
	function admin_print_styles() {
		// enqueue here
		$styleUrl = plugins_url('/css/admin-style.css', __FILE__);
		$styleFile = WP_PLUGIN_DIR . '/qoorate/css/admin-style.css';
		if ( file_exists($styleFile) ) {
				wp_register_style('qoorate-admin', $styleUrl);
				wp_enqueue_style('qoorate-admin');
		}
		// we are still in our head, but have written the styles already, so force a new write.
		//wp_enqueue_style('guthrie-style')
	}
	
	function admin_print_scripts() {
		// TODO: remove!? don't think I'll need this.
	}
	
}

/**
 *  Filters/Actions
 */

function qoorate_comments_template($value) {
	global $post;
	global $comments;
	if ( !( is_singular() && ( have_comments() || 'open' == $post->comment_status ) ) ) {
	   return;
	}

	// we don't care if we are set or not
	//if ( !qoorate_is_installed() || !qoorate_can_replace() ) {
	//	return $value;
	//}

	return dirname(__FILE__) . '/comments.php';
}
