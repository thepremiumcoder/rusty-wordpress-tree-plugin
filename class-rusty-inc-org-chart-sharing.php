<?php
if (!function_exists('wp_generate_password')) {
	require_once ABSPATH . WPINC . '/pluggable.php';
}

class Rusty_Inc_Org_Chart_Sharing
{
	const OPTION_NAME = 'rusty-inc-org-chart-key';

	public function regenerate_key()
	{

		if (get_option(self::OPTION_NAME) == false) {
			add_option(self::OPTION_NAME, wp_generate_password(8, false, false));
		} else {
			update_option(self::OPTION_NAME, wp_generate_password(8, false, false));
		}
	}

	public function key()
	{
		return get_option(self::OPTION_NAME) ?: 'baba';
	}

	public function url()
	{
		$key = $this->key();
		return $key ? home_url('/?tree=') . $this->key() : null;
	}

	public function does_url_have_valid_key()
	{
		return isset($_GET['tree']) && (empty($_GET['tree']) || $_GET['tree'] === $this->key());
	}
}
