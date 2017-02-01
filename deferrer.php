<?php
/**
 * Plugin Name: Deferrer
 * Author: Allan Christian Carlos
 * Author URI: mailto:allanchristiancarlos@gmail.com
 * Description: Deferring plugin for javascript and css
 */

if (!defined('ABSPATH')) {
	die();
}


class WP_Plugin_Deferrer
{
	private $queue = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'enqueue'), 999999999);
		add_action('wp_enqueue_scripts', array($this, 'dequeue_defered_assets'), 99999);
	}

	public function dequeue_defered_assets()
	{
		global $wp_scripts, $wp_styles;
		$this->queue['script'] = $wp_scripts->queue;
		$this->queue['style'] = $wp_styles->queue;
		$script_handles = $this->get_deferred_scripts();
		foreach($script_handles as $script_handle) {
			wp_dequeue_script($script_handle);
			$siblings = $this->get_dependent_assets($script_handle, 'script');
			foreach($siblings as $sibling_handle) {
				wp_dequeue_script($sibling_handle);
			}
		}

		$style_handles = $this->get_deferred_styles();
		foreach($style_handles as $style_handle) {
			wp_dequeue_style($style_handle);
			$siblings = $this->get_dependent_assets($style_handle, 'style');
			foreach($siblings as $sibling_handle) {
				wp_dequeue_style($sibling_handle);
			}
		}
	}

	/**
	 * Enqueue assets
	 * @access public
	 * @return void 
	 */
	public function enqueue()
	{	
		$plugin_url = plugin_dir_url(__FILE__);

		wp_register_script('deferrer', $plugin_url . 'js/deferrer.js', array(), '1.0', true);
		wp_enqueue_script('deferrer');

		wp_localize_script('deferrer', 'WP_Plugin_Deferrer_Localize', $this->localization());
	}

	/**
	 * Localization
	 * @return array 
	 */
	public function localization()
	{
		$collection = array(
			'scripts' => $this->get_scripts_html(),
			'styles'  => $this->get_styles_html(),
			'test' =>  array_merge($this->get_deferred_scripts(), $this->get_dependent_assets($this->get_deferred_scripts(), 'script'))
		);

		return apply_filters('deferrer_localization', $collection);
	}

	public function get_scripts_html()
	{
		global $wp_scripts;
		$deferred = $this->get_deferred_scripts();
		$scripts = array_merge($deferred, $this->get_dependent_assets($deferred, 'script'));
		ob_start();
		$wp_scripts->do_items($scripts);
		return ob_get_clean();
	}

	public function get_styles_html()
	{
		global $wp_styles;
		$deferred = $this->get_deferred_styles();
		$styles = array_merge($deferred, $this->get_dependent_assets($deferred, 'style'));
		ob_start();
		$wp_styles->do_items($styles);
		return ob_get_clean();
	}

	public function get_dependent_assets($assets, $type)
	{
		global $wp_scripts, $wp_styles;

		$siblings = array();
		$handles  = is_array($assets) ? $assets : array($assets);

		foreach($handles as $handle) {
			$assets            = $type === "style" ? $wp_styles : $wp_scripts;
			$asset             = $assets->query($handle, 'registered');
			$dependencies      = isset($asset->deps) ? (array)$asset->deps : array();
			$registered_assets = $assets->registered;

			foreach($dependencies as $dependency_handle) {
				foreach($registered_assets as $registered_asset_handle => $registered_asset) {
					if (   (in_array($dependency_handle, $registered_asset->deps)) 
						&& ($registered_asset_handle !== $handle)
						&& (in_array($registered_asset_handle, $this->queue[$type]))) {
						$siblings[] = $registered_asset_handle;
						$siblings += $registered_asset->deps;
					}
				}
			}
		}

		return $siblings;
	}

	public function get_deferred_styles()
	{
		return apply_filters('deferrer_get_deferred_styles', array('loancenter', 'google-open-sans', 'bootstrap', 'font-awesome'));
	}

	public function get_deferred_scripts()
	{
		return apply_filters('deferrer_get_deferred_scripts', array('main-js', 'wpb_composer_front_js'));
	}
}

$GLOBALS['WP_Plugin_Deferrer'] = new WP_Plugin_Deferrer();