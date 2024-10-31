<?php
namespace OctoPush;

use OctoPush\Admin\Feedbacks;

/**
 * Class Admin
 *
 * @package OctoPush
 */
class Admin extends Main
{
	/**
	 * OctoPush constructor.
	 *
	 * The main plugin actions registered for WordPress
	 */
	public function __construct()
	{
		add_action('admin_menu',                    array($this, 'addAdminMenu'));
		add_action('wp_ajax_store_admin_data',      array($this, 'storeAdminData'));
		add_action('admin_enqueue_scripts',         array($this, 'addAdminScripts'));
	}

	/**
	 * Callback for the Ajax request
	 *
	 * Updates the options data
	 *
	 * @return void
	 */
	public function storeAdminData()
	{

		if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false) {
			die('Invalid Request! Reload your page please.');
        }

		$data = $this->getData();

		foreach ($_POST as $field=>$value) {

			if (substr($field, 0, 8) !== 'OctoPush_') {
				continue;
			}

			if (empty($value)) {
				unset($data[$field]);
            }

			// We remove the OctoPush_ prefix to clean things up
			$field = substr($field, 8);

			// Sanitize
            if (is_array($value)) {
                foreach ($value as $key=>$sub_value) {
	                if (is_array($sub_value)) {
                        foreach ($sub_value as $sub_key=>$sub_sub_value) {
	                        $sub_value[$sub_key] = sanitize_text_field($sub_sub_value);
                        }
	                } else {
		                $value[$key] = sanitize_text_field($sub_value);
                    }
                }
            } else {
	            $value = sanitize_text_field($value);
            }

			$data[$field] = $value;

		}

		update_option($this->option_name, $data);

		echo __('Saved!', 'OctoPush');
		die();

	}

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts()
	{
		wp_enqueue_style('OctoPush-admin', OctoPush_URL. 'assets/css/admin.css', false, 1.0);
		//wp_enqueue_script('OctoPush-admin', OctoPush_URL. 'assets/js/admin.js', array(), 1.0);

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);

		wp_localize_script('OctoPush-admin', 'OctoPush_exchanger', $admin_options);
	}

	/**
	 * Adds the OctoPush label to the WordPress Admin Sidebar Menu
     *
     * It also initiates the Feedback class to fire the WP list table at the right time
	 */
	public function addAdminMenu()
	{
		$page_hook = add_options_page(
            'OctoPush',
            'OctoPush',
			'manage_options',
			'OctoPush',
			array($this, 'adminLayout')
        );

		add_action('load-'. $page_hook, function () {
			$arguments = array(
				'label'		=>	__( 'Feedbacks Per Page', 'OctoPush'),
				'default'	=>	30,
				'option'	=>	'feedbacks_per_page'
			);
			add_screen_option( 'per_page', $arguments);
			new Feedbacks('OctoPush');
        });
	}

	/**
	 * Make an API call to the OctoPush API and returns the response
	 *
	 * @param $private_key string
	 * @param $carrier_id int|null
	 *
	 *
	 * @return array
	 */
	private function getCarriers($private_key, $carrier_id = null)
	{
		$data       = array();
		$carrier_id = ($carrier_id) ? '&carrier_id='. $carrier_id : '';
		$response   = wp_remote_get(OctoPush_PROTOCOL. '://api.'. OctoPush_ENDPOINT .'/v1/carriers/?api_key='. $private_key . $carrier_id);

		if (is_array($response) && !is_wp_error($response)) {
			$data = json_decode($response['body'], true);
		}

		return $data;
	}

	/**
	 * Get a Dashicon for a given status
	 *
	 * @param $valid boolean
	 *
	 * @return string
	 */

	/**
	 * Outputs the Admin Dashboard layout containing the form with all its options
	 *
	 * @return void
	 */
	public function adminLayout()
	{

		$data                   = $this->getData();

		$data['private_key']    = (isset($data['private_key'])) ? $data['private_key'] : null;
		$api_response           = $this->getCarriers($data['private_key']);
		$not_ready              = (empty($data['public_key']) || empty($api_response) || isset($api_response['error']));
		$has_engager_preview    = (isset($_GET['OctoPush-demo-engager']) && $_GET['OctoPush-demo-engager'] === 'go');
		$has_wc                 = (class_exists('WooCommerce'));
		$nav_tab                = (isset($_GET['tab'])) ? sanitize_text_field($_GET['tab']) : false;
		$tabs                   = array(
			'OctoPush-integration'      => __('OctoPush Integration', 'OctoPush'),
			'OctoPush-apikeys'      => __('Apikeys in OctoPush', 'OctoPush'),
			'OctoPush-popups'      => __('Popups in OctoPush', 'OctoPush'),
		);

		$tab_class = new Admin\Integration();
		$nav_tab = 'OctoPush-integration';
		
		?>

		<div class="wrap">

			<h1><?php _e('OctoPush', 'OctoPush'); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="?page=OctoPush" class="nav-tab">
					OctoPush Integration
				</a>
				<a href="https://app.octopush.hu/user/" target="_blank" class="nav-tab">
					Apikeys in OctoPush
				</a>
				<a href="https://app.octopush.hu/subscription/" target="_blank" class="nav-tab">
					Popups in OctoPush
				</a>
			</h2>

			<?php if ($has_engager_preview) { ?>
				<?php $widget_Engager = new Engager();
				$widget_Engager->addFooterCode(true); ?>
				<p class="notice notice-warning p-10">
					<?php _e( '', 'OctoPush' ); ?>
				</p>
			<?php } ?>


			<form id="OctoPush-admin-form" class="postbox">
				<div class="form-group inside">

					<h2>
						<?php echo $tab_class->getTitle(); ?>
					</h2>

		            <?php $tab_class->writeMarkup($not_ready, $data, $api_response); ?>

				</div>

				<hr>

				<div class="inside">

					<button class="button button-primary" id="OctoPush-admin-saver" type="submit">
						<?php _e( 'Save', 'OctoPush' ); ?>
					</button>

				</div>
				</form>


		</div>

		<?php

	}

}