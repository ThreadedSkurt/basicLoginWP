<?php
/*
Plugin Name: Basic Login / Register
Plugin URI: http://your-plugin-website.com
Description: Super simple test plugin for register and logging into WordPress
Version: 1.0.0
Author: Me
Author URI: http://your-website.com
License: GPL-2.0+
*/
require_once plugin_dir_path(__FILE__) . 'includes/widget.php';
// Register plugin widget
function my_plugin_register_widget()
{
    // Register your widget class
    register_widget('BasicCredWidget');
}
add_action('widgets_init', 'my_plugin_register_widget');

// Load CSS
function enqueue_widget_styles()
{
    wp_enqueue_style('widget-styles', plugin_dir_url(__FILE__) . 'includes/css/styling.css');
}
add_action('wp_enqueue_scripts', 'enqueue_widget_styles');

// Login actions
add_action('admin_post_my_widget_login', 'process_login_form');
add_action('admin_post_nopriv_my_widget_login', 'process_login_form');

// Register actions
add_action('admin_post_my_widget_register', 'process_registration_form');
add_action('admin_post_nopriv_my_widget_register', 'process_registration_form');

// Process login form
function process_login_form()
{
    $widget = new BasicCredWidget();
    $widget->process_login_form();
}

// Process registration form
function process_registration_form()
{
    $widget = new BasicCredWidget();
    $widget->process_registration_form();
}
