<?php
/*
Plugin Name: rpiCloud Block
Plugin URI: https://github.com/johappel/rpicloud
Über einen Gutenberg Block oder einen Shortcode lässt sich eine minimalistische Webclient zu  einer beliebigen Nextcloud Instanz darstellen. Unterstützt werden veröffentliche (geteilte) Ordner, mit und ohne Passortschutz. Über eine Baumansicht kann der Besucher zu beliebigen Dateien navigieren und diese direkt im Browser aufrufen. Auch HTML Seiten!! Eine minimalistisches Auch das Bearbeiten des Ordnerinhaltes kann erlaubt werden.
A minimalist web client to any Nextcloud instance can be displayed via a Gutenberg block or a shortcode. Supported are published (shared) folders, with and without password protection. Via a tree view, the visitor can navigate to any file and access it directly in the browser. Also HTML pages! A minimalist editing of the folder content can also be allowed.
Author: Joachim Happel
Version: 1.0
Author URI: https://github.com/johappel
 */

require_once 'sabre/autoload.php';
require_once 'classes/Cloud_Block.php';
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

define('RPICLOUD', 'rpi-virtuell Nextcloud Plugin');

add_action('init', array('Cloud_Core','init'));
add_action( 'wp', array('Cloud_Core','dispatch') );
add_filter('query_vars',  array('Cloud_Core','add_query_vars') );
add_filter( 'upload_mimes', array('Cloud_Upload','allow_myme_types'), 999, 1 );


