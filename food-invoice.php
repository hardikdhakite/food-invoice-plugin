<?php
/*
Plugin Name: Food Invoice
Description: Generate and manage food invoices for The Celebrations Resort and Restaurant.
Version: 1.0
Author: Hardik Dhakite
Author URI: https://github.com/hardikdhakite
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/post-type.php';
require_once plugin_dir_path(__FILE__) . 'admin/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
