<?php
namespace OctoPush;

/**
 * Class Main
 *
 * This is the base parent class
 *
 * @package OctoPush
 */
class Main
{
	/**
	 * The security nonce
	 *
	 * @var string
	 */
	public $_nonce = 'OctoPush_admin';

	/**
	 * The option name
	 *
	 * @var string
	 */
	public $option_name = 'OctoPush_data';

	/**
	 * Returns the saved options data as an array
	 *
	 * @return array
	 */
	public function getData()
	{
		return get_option($this->option_name, array());
	}

}
