<?php
/**
 * Plugin Name: WP Ninja Hub
 * Description: Shows data from multiple WPManageNinja plugins on a single user dashboard.
 * Version: 1.0.0
 * Author: Sanjith Sarkar
 * Text Domain: wp-ninja-hub
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPNINJA_HUB_VERSION', '1.0.0');
define('WPNINJA_HUB_PATH', plugin_dir_path(__FILE__));
define('WPNINJA_HUB_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/vendor/autoload.php';

use WPNinjaDashboard\Controllers\AdminController;
use WPNinjaDashboard\Controllers\DashboardController;

add_action('plugins_loaded', function () {
    (new AdminController())->register();
    (new DashboardController())->register();
});
