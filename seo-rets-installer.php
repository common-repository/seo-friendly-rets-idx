<?php
/*
Plugin Name: SEO RETS Installer
Plugin URI: http://seorets.com/
Description: Use this plugin to automatically install the SEO RETS plugin. Once activated you can safely remove this plugin.
Version: 1.3.0
Author: SEO RETS, LLC
Author URI: http://seorets.com/
License: GPL2
*/

class SEORETSInstaller {

	private static $plugins_dir;

	public static function init() {
		$location_parts = explode("/", rtrim(plugin_dir_path(__FILE__), "/"));
		array_pop($location_parts);
		self::$plugins_dir = "/" . implode("/", $location_parts);

		add_action('shutdown', array('SEORETSInstaller', 'activate'));
	}
	
	public static function show_message() {
		echo '<div id="message" class="updated fade"><p><strong>The SEO RETS plugin has been installed. <a href="' . get_bloginfo('url') . '/wp-admin/admin.php?page=seo-rets-user-guide">Get started</a> now!</strong></p></div>';
	}

	public static function activate() {
		if ( is_writable(self::$plugins_dir) && !is_dir(self::$plugins_dir . '/seo-rets') ) {
			$zip_loc = self::$plugins_dir . "/seo-rets.zip";
			file_put_contents($zip_loc, file_get_contents("http://seorets.com/seo-rets.zip"));
			
			if ( !class_exists('ZipArchive') ) {
				return;
			}

			$archive = new ZipArchive;
			$archive->open($zip_loc);
			$archive->extractTo(self::$plugins_dir);
			$archive->close($zip_loc);
			unlink($zip_loc);
			$installed_plugins = get_option("active_plugins");
			$installed_plugins[] = "seo-rets/seo-rets.php";
			unset($installed_plugins[array_search("seo-friendly-rets-idx/seo-rets-installer.php", $installed_plugins)]);
			$installed_plugins = array_values($installed_plugins);

			update_option("active_plugins", $installed_plugins);

			self::rrmdir(self::$plugins_dir . "/seo-friendly-rets-idx");
		}
	}

	private function rrmdir($dir) {
 		foreach ( glob($dir . '/*') as $file ) {
	        if ( is_dir($file) ) {
				self::rrmdir($file);
	        } else {
				unlink($file);
	        }
	    }
	    rmdir($dir);
	}

}

add_action('admin_notices', array('SEORETSInstaller', 'show_message'));
register_activation_hook(__FILE__, array('SEORETSInstaller', 'init'));