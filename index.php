<?php
$dabr_start = microtime(1);

// I18N support information here
$language = "en_GB";
putenv("LANG=" . $language);
setlocale(LC_ALL, $language);

// Set the text domain as "messages"
$domain = "messages";
bindtextdomain($domain, "i/Locale");
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . date('r'));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');

require 'config.php';
require 'i/common/menu.php';
require 'i/common/user.php';
require 'i/common/theme.php';
require 'i/common/twitter.php';
require 'i/common/settings.php';
require 'i/common/codebird.php';
require 'i/common/css.php';

//	Initial menu items to show even when not logged in.
menu_register(array (
	'about' => array (
		'callback' => 'about_page',
		'display' => '🛈' // Perhaps ℹ http://www.fileformat.info/info/unicode/char/1f6c8/index.htm
	),
	'logout' => array (
		'security' => true,
		'callback' => 'logout_page',
		'display' => ''
	),
	'oauth' => array(
		'callback' => 'user_oauth',
		'hidden' => 'true',
	),
	'widgets' => array(
		'callback' => 'css',
		'hidden'   => 'true',
	),
));

function logout_page() {
	user_logout();
	header("Location: " . BASE_URL); /* Redirect browser */
	exit;
}

function about_page() {
	theme('page', 'About', theme('about'));
}

session_start();
menu_execute_active_handler();
