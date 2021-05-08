<?php
/*
Plugin Name: rpi Cloud Client
Plugin URI: http:///
Description: Wordpress Webdav Client which connects to a public NextCloud share.
Author: Joachim Happel
Version: 1.0
Author URI: http://joachim-happel.de
 */
require_once 'vendor/autoload.php';
require_once 'classes/Cloud_Core.php';
require_once 'classes/Cloud_Config.php';
require_once 'classes/Cloud_Client.php';
require_once 'classes/Cloud_Directory.php';
require_once 'classes/Cloud_Download.php';
require_once 'classes/Cloud_Delete.php';
require_once 'classes/Cloud_Upload.php';
require_once 'classes/Cloud_File.php';
require_once 'classes/Cloud_Helper.php';
require_once 'classes/Cloud_Template.php';

Cloud_Core::$pluginurl = plugin_dir_url (__FILE__);
Cloud_Core::$plugindir = plugin_dir_path(__FILE__);
Cloud_Core::$shorturl = home_url().'/cloud/';
Cloud_Core::$frameurl = home_url().'/rpicloud/';
Cloud_Core::$officeurl = home_url().'/cloudview/';
Cloud_Core::$wpdir = ABSPATH;


add_action('init', array('Cloud_Core','init'),90);
//add_action( 'enqueue_block_editor_assets', array('Cloud_Core','init'));

