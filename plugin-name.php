<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.0.0
 * @package           Labor_Cost_Calculator
 *
 * @wordpress-plugin
 * Plugin Name:       Labor Cost Calculator
 * Plugin URI:        
 * Description:       Calculate labor cost and net salary from basic user input and policy information stored in database through a built-in formula.
 * Version:           1.0.0
 * Author:            HROne
 * Author URI:        http://hrone.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       
 * Domain Path:       
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-activator.php';
	Plugin_Name_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name-deactivator.php';
	Plugin_Name_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin-name.php';

// require(plugin_dir_path(__FILE__) . 'database/DatabaseConnect.php');
// $mysqli = database_connect();

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Plugin_Name();
	$plugin->run();

}
run_plugin_name();


class LaborCost{
	function __construct(){
		// $this->load_google_client();

		add_action('admin_menu',  array($this, 'add_plugin_admin_menu'));
		add_action('init', array($this, 'shortcode_init'));

		function database_shortcode(){
			return require_once(plugin_dir_path(__FILE__) . 'database/DatabaseTest.php');
		}
		add_shortcode("Database_Test", 'database_shortcode');
	}


	/**
	 * Initialize the menu page
	 */
	function add_plugin_admin_menu() {
		add_menu_page('Labor Cost Calculator Setup', 'Labor Cost', 'manage_options', 'labor-cost', array(__CLASS__, 'menu_page'));
	}


	/**
	 * Write to json
	 */
	static function create_calc_backend(){
		$user_type = $_POST['user_type'];
		if ($user_type == ""){
			return;
		}

		// self::shortcode_init($user_type);

		$fname = plugin_dir_path( __FILE__ ) . 'calculators.json';
		$temp_content = file_get_contents($fname);
	    $temp_array = (array)json_decode($temp_content);

	    $shortcode = "Labor-Cost-Calculator_" . $user_type;

		$new_calc = Array (
			"id" => count($temp_array) + 1,
			"user_type" => $user_type,
			"shortcode" => $shortcode
		);

	    array_push($temp_array, $new_calc);
	    $json_data = json_encode($temp_array);
	    file_put_contents($fname, $json_data);
	}


	/**
	 * Delete from json
	 */
	static function delete_calc(){

	}


	/**
	 * The content of menu page
	 */
	static function menu_page(){
		require_once(plugin_dir_path( __FILE__ ) . 'html/AdminPage.php');
	}


	/**
	 * Initialize shortcode
	 */
	function shortcode_init($user_type){
		$guest = "Labor-Cost-Calculator_Guest";
		$internal = "Labor-Cost-Calculator_Internal";

		function guest_shortcode($atts = [], $content = null){
			return require_once(plugin_dir_path( __FILE__ ) . 'html/GuestShortcode.php');  // Switch back to html/...
		}
		add_shortcode($guest, 'guest_shortcode');

		function internal_shortcode(){
			return require_once(plugin_dir_path(__FILE__)) . 'html/InternalShortcode.php';
		}
		add_shortcode($internal, 'internal_shortcode');
	}


	/**
	 * Upload file to media
	 */
	static function upload_excel_backend(){
	    // First check if the file appears on the _FILES array
	    if(isset($_FILES['test_upload'])){
	    	$filename = $_FILES['test_upload']['name'];

	    	require_once(plugin_dir_path(__FILE__) . "database/DatabaseUpdate.php");
	    	if (filename_check($filename))
	    		echo "Filename is good according to the format above.";
	    	else{
	    		echo "Error: Filename is incorrect according to the format above. Upload aborted.";
	    		return;
	    	}
	    	?><br><?php

	        // Use the wordpress function to upload
	        // test_upload_pdf corresponds to the position in the $_FILES array
	        // 0 means the content is not associated with any other posts
	        $uploaded=media_handle_upload('test_upload', 0);

	        // Error checking using WP functions
	        if(is_wp_error($uploaded)){
	            echo "Error uploading file: " . $uploaded->get_error_message();
	        }else{
	            echo "Succeed Uploading Data!";
				?><br><?php

	            write_db();
	        }
	    }
	}
}


new LaborCost();

?>