<?php
/*
	Welcome to Dabr!
	Edit this file and rename it "config.php"
*/

//	Base URL, must be point to your website, including a trailing slash
//	eg "https://example.com/dabr/"
$server_name = "http://dabr.mywebcommunity.org/";
$folder_name = "";  //	If you're installing Dabr in your root directory, this should be set to ""
define('SERVER_NAME', $server_name);
define('FOLDER_NAME', $folder_name);
define('BASE_URL', $server_name . $folder_name);

//	OAuth consumer and secret keys. Available from https://apps.twitter.com/
define('OAUTH_CONSUMER_KEY',    'KFQUK6Fb7BJNdt70w7GBNLTWf');
define('OAUTH_CONSUMER_SECRET', '5oasmRNkKYLWEC6P94Z7m1q8hcK9pOlEkCK7R1om2rXjXeLUkO');

//	Cookie encryption key. Max 52 characters
define('ENCRYPTION_KEY', 'Example Key - Change Me!');

//	That's it! You're done :-)

//	Everything in this section is optional. Add it if you wish

//	Optional: Embedkit Key 
//	Embed image previews in tweets
//	Free sign up at https://embedkit.com/
define('EMBEDKIT_KEY', '');

//	Optional: Image Proxy URL
//	Documentation http://carlo.zottmann.org/2013/04/14/google-image-resizer/
define('IMAGE_PROXY_URL', '');

//	Optional: Enable to view page processing and API time
define('DEBUG_MODE', 'OFF');

//	Optional: This will display any errors you introduce into the code.
//	See more at http://php.net/manual/en/function.error-reporting.php
error_reporting(E_ALL ^ E_NOTICE);
