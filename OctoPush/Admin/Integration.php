<?php
namespace OctoPush\Admin;

/**
 * Class Integration
 *
 * @package OctoPush\Admin
 */
class Integration
{

	/**
	 * Return the tab title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return __('OctoPush connection', 'OctoPush');
	}

	/**
	 * Whether the page is ready or not
	 *
	 * @param $not_ready
	 *
	 * @return boolean
	 */
	public function isActive($not_ready)
	{
		return !$not_ready;
	}

	/**
	 * Write the tab markup
	 *
	 * @param boolean   $not_ready - whether the Api settings are correct and set or not
	 * @param array     $data - the current options data
	 *
	 * @return void
	 */
	 
	public function writeMarkup($not_ready, $data)
	{

		?>
<?php
global $wpdb;
$apikey = $wpdb->get_var("SELECT apikeys FROM ".$wpdb->prefix.'octopush');
?>
        <div class="row">
            <div class="col">

                <div class="mt-3">
	                <?php if ($apikey == ""){ ?>
                        <p>
                            <b><?php _e('Get ready in 3 steps', 'OctoPush'); ?></b>
                            <br>
                            <br>
			                <?php _e('1. <a href="https://octopush.hu" target="_blank">Create a OctoPush account here</a>', 'OctoPush'); ?>
                            <br>
			                <?php _e('2. <a href="https://app.octopush.hu/user" target="_blank">Get your API keys from the USER page here</a>', 'OctoPush'); ?>
                            <br>
			                <?php _e('3. Copy and paste the private and public API keys in the box below and <b>reload the page</b>.', 'OctoPush'); ?>
                        </p>
	                <?php }else{ ?>
		                <?php _e('Great! You can create popup in octopush system, enable the popup and the popup display on your website <a href="https://app.octopush.hu/subscription" target="_blank">OctoPush</a>.', 'OctoPush'); ?>
	                <?php } ?>
                </div>

                <table class="form-table mt-3">
                    <tbody>
                    <tr>
                        <td scope="row">
                            <label><?php _e( 'Apikey ID:', 'OctoPush' ); ?></label>
                        </td>
                        <td>
                            <input type="hidden" name="page" id="page" class="regular-text" value="OctoPush"/>
                            <input name="OctoPush_public_key" id="OctoPush_public_key" class="regular-text" type="text" value="<?php echo (isset($apikey)) ? $apikey : ''; ?>"/>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="col text-right">

                <img src="<?php echo OctoPush_URL .'assets/images/connection.jpg' ?>" width="400">

            </div>
        </div>

		<?php
	}

}