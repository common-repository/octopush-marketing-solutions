<?php
namespace OctoPush\Admin;


/**
 * Class Feedbacks
 *
 * @package OctoPush\Admin
 */
class Feedbacks
{
	public function writeMarkup($has_wc, $data, $api_response)
	{
		$this->prepare_items();
		$this->display();
	}
}