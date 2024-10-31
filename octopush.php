<?php
/**
 * Plugin Name:       OctoPush - Marketing Solutions
 * Description:       You can use this plugin to run OctoPush services.
 * Version:           1.0.0
 * Author:            OctoPush
 * Author URI:        https://octopush.hu
 * Text Domain:       octopush
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/*
 * Plugin constants
 */
if(!defined('OctoPush_PLUGIN_VERSION'))
	define('OctoPush_PLUGIN_VERSION', '1.0.0');
if(!defined('OctoPush_URL'))
	define('OctoPush_URL', plugin_dir_url( __FILE__ ));
if(!defined('OctoPush_PATH'))
	define('OctoPush_PATH', plugin_dir_path( __FILE__ ));
if(!defined('OctoPush_ENDPOINT'))
	define('OctoPush_ENDPOINT', 'octopush.hu');
if(!defined('OctoPush_PROTOCOL'))
	define('OctoPush_PROTOCOL', 'https');

require_once OctoPush_PATH . 'OctoPush/Main.php';
require_once OctoPush_PATH . 'OctoPush/Admin/Integration.php';
require_once OctoPush_PATH . 'OctoPush/Admin/Feedbacks.php';
require_once OctoPush_PATH . 'OctoPush/Admin.php';

/*
 * Main class
 */
new \OctoPush\Admin();


if(!defined('ABSPATH')){
	die();
}

function database_creation(){
	global $wpdb;
	$op_details = $wpdb->prefix.'octopush';
	$charset = $wpdv->get_charset_collate;
	
	$op_det = "CREATE TABLE ".$op_details."(
	id	int	NOT NULL AUTO_INCREMENT,
	apikeys	text,
	PRIMARY KEY (id)
	) $charset;";
	
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($op_det);
}

if(is_admin()){
	global $wpdb;
	$table_name = $wpdb->base_prefix.'octopush';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

	if ( ! $wpdb->get_var( $query ) == $table_name ) {
		// go go
	}else{
		register_activation_hook(__FILE__, 'database_creation');
	}
}

add_action('wp_footer', 'wpshout_action_example'); 
function wpshout_action_example() { 
	global $wpdb;
	$op_details = $wpdb->prefix.'octopush';
	$apikey = $wpdb->get_var("SELECT apikeys FROM ".$op_details);

    echo '<script type="text/javascript">
    (function(d,i){
        var e,r=d.getElementsByTagName("head")[0],p=d.location.protocol;
        e=d.createElement("script");e.type="text/javascript";
	e.charset="utf-8";e.async=!0;e.defer=!0;
        e.src=p+"//app.octopush.hu/plugins/preload.js?pikey="+i;r.appendChild(e);
    })(document,"'.$apikey.'");
</script>'; 
}


if ( is_admin() && isset($_GET['OctoPush_public_key']) && $_GET['OctoPush_public_key'] != "") {
	if(preg_match("/^[a-zA-Z0-9]+$/", $_GET['OctoPush_public_key']) == 1 && strlen($_GET['OctoPush_public_key']) <= 32) {
		$octopush_public_key = sanitize_text_field($_GET['OctoPush_public_key']);
		$octopush_public_key = esc_attr($octopush_public_key);

		global $wpdb;
		$op_details = $wpdb->prefix.'octopush';
		$getapikey = $wpdb->get_var("SELECT apikeys FROM ".$op_details);

		if($getapikey != ""){
			$sql = $wpdb->prepare("UPDATE ".$op_details." SET `apikeys` = '".$octopush_public_key."'");
			$wpdb->query($sql);
		}else{
			$sql = $wpdb->prepare("INSERT INTO ".$op_details." (id, apikeys) VALUES ('NULL', '".$octopush_public_key."')");
			$wpdb->query($sql);
		}
	}
}