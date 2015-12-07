<?php
require 'Autolink.php';
require 'Extractor.php';
require 'lists.php';

menu_register(array(
	'' => array(
		'callback' => 'twitter_home_page',
		'display'  => '🏠'
	),
	'status' => array(
		'hidden'   => true,
		'security' => true,
		'callback' => 'twitter_status_page',
	),
	'update' => array(
		'hidden'   => true,
		'security' => true,
		'callback' => 'twitter_update',
	),
	'twitter-retweet' => array(
		'hidden'   => true,
		'security' => true,
		'callback' => 'twitter_retweet',
	),
	'replies' => array(
		'security' => true,
		'callback' => 'twitter_replies_page',
		'display'  => '@'
	),
	'favourite' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'unfavourite' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'messages' => array(
		'security' => true,
		'callback' => 'twitter_directs_page',
		'display' => '✉'
	),
	'search' => array(
		'security' => true,
		'callback' => 'twitter_search_page',
		'display' => '🔍' // http://stackoverflow.com/questions/12036038/is-there-unicode-glyph-symbol-to-represent-search
	),
	'user' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_user_page',
	),
	'follow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'unfollow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'confirm' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_confirmation_page',
	),
	'confirmed' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_confirmed_page',
	),
	'block' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'unblock' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'spam' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_spam_page',
	),
	'favourites' => array(
		'security' => true,
		'callback' =>  'twitter_favourites_page',
		'display' => '♥'
	),
	'followers' => array(
		'security' => true,
		'callback' => 'twitter_followers_page',
		'display' => '☻'
	),
	'friends' => array(
		'security' => true,
		'callback' => 'twitter_friends_page',
		'display' => '😉'
	),
	'delete' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_delete_page',
	),
	'deleteDM' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_deleteDM_page',
	),
	'deleteSavedSearch' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_delete_saved_search_page',
	),
	'retweet' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweet_page',
	),
	'hash' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_hashtag_page',
	),
	'trends' => array(
		'security' => true,
		'callback' => 'twitter_trends_page',
		'display' => '↗'
	),
	'retweets' => array(
		'security' => true,
		'callback' => 'twitter_retweets_page',
		'display' => '♻'
	),
 	'lists' => array(
 		'security' => true,
 		'callback' => 'lists_controller',
 		'display'  => '≡'
 	),
	'retweeted_by' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_retweeters_page',
	),
	'account' => array(
		'security' => true,
		'callback' => 'twitter_profile_page',
		'display' => '☺'
	),
	'showretweets' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweets',
	),
	'hideretweets' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweets',
	),
	'blocked' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_blocks',
	),
));

// How should external links be opened?
function get_target()
{
	// Kindle doesn't support opening in a new window
	if (stristr($_SERVER['HTTP_USER_AGENT'], "Kindle/"))
	{
		return "_self";
	}
	else
	{
		return "_blank";
	}
}

//	Edit User Profile
function twitter_profile_page() {
	// process form data
	if ($_POST['name']){

		// post profile update
		$api_options = array(
			"name"        => stripslashes($_POST['name']),
			"url"         => stripslashes($_POST['url']),
			"location"    => stripslashes($_POST['location']),
			"description" => stripslashes($_POST['description']),
		);

		$cb = get_codebird();


		@twitter_api_status($cb->account_updateProfile($api_options));

		$content = "<h2>Profile Updated</h2>";
	}

	//	http://api.twitter.com/1/account/update_profile_image.format
	if ($_FILES['image']['tmp_name']) {
		// these files to upload. You can also just upload 1 image!
		$api_options = array(
			"image" => $_FILES['image']['tmp_name']
		);

		@twitter_api_status($cb->account_updateProfileImage($api_options));
	}

	//	Twitter API is really slow!  If there's no delay, the old profile is returned.
	//	Wait for 5 seconds before getting the user's information, which seems to be sufficient
	//	See https://dev.twitter.com/rest/reference/post/account/update_profile_image
	sleep(5);

	// retrieve profile information
	$user = twitter_user_info(user_current_username());

	$content .= theme('user_header', $user);
	$content .= theme('profile_form', $user);

	theme('page', "Edit Profile", $content);
}


// function long_url($shortURL)
// {
// 	if (!defined('LONGURL_KEY'))
// 	{
// 		return $shortURL;
// 	}
// 	$url = "http://www.longurlplease.com/api/v1.1?q=" . $shortURL;
// 	$curl_handle=curl_init();
// 	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
// 	curl_setopt($curl_handle,CURLOPT_URL,$url);
// 	$url_json = curl_exec($curl_handle);
// 	curl_close($curl_handle);

// 	$url_array = json_decode($url_json,true);

// 	$url_long = $url_array["$shortURL"];

// 	if ($url_long == null)
// 	{
// 		return $shortURL;
// 	}

// 	return $url_long;
// }


function friendship_exists($user_a) {

	$cb = get_codebird();

	$api_options = array('target_screen_name' => $user_a);

	$friendship = $cb->friendships_show($api_options);
	twitter_api_status($friendship);

	if ($friendship->relationship->target->following == 1) {
		return true;
	} else {
		return false;
	}
}

function friendship($user_a) {
	$cb = get_codebird();

	$api_options = array('target_screen_name' => $user_a);

	$friendship = $cb->friendships_show($api_options);
	twitter_api_status($friendship);
	return $friendship;
}

function twitter_block_exists($user_id) {
	$cb = get_codebird();
	$api_options = array("user_id" => $user_id);

	// 0th element http://stackoverflow.com/questions/3851489/return-php-object-by-index-number-not-name
	$friendship = current($cb->friendships_lookup($api_options));

	return ("blocking" == $friendship->connections[0]);
}

function twitter_trends_page($query) {
	$woeid = $_GET['woeid'];
	if(isset($woeid)) {
		$duration = time() + (3600 * 24 * 365);
		setcookie('woeid', $woeid, $duration, '/');
	}
	else {
		$woeid = $_COOKIE['woeid'];
	}

	if($woeid == '') $woeid = '1'; //worldwide

	//fetch "local" names
	$cb = get_codebird();

	$api_options = array();

	$local_object = $cb->trends_available($api_options);
	twitter_api_status($local_object);
	$local = (array)$local_object;


	$header = '<form method="get" action="trends"><select name="woeid">';
	$header .= '<option value="1"' . (($woeid == 1) ? ' selected="selected"' : '') . '>Worldwide</option>';

	//sort the output, going for Country with Towns as children
	foreach($local as $key => $row) {
		$c[$key] = $row->country;
		$t[$key] = $row->placeType->code;
		$n[$key] = $row->name;
	}
	array_multisort($c, SORT_ASC, $t, SORT_DESC, $n, SORT_ASC, $local);

	foreach($local as $l) {
		if($l->woeid != 1) {
			$n = $l->name;
			if($l->placeType->code != 12) $n = '-' . $n;
			$header .= '<option value="' . $l->woeid . '"' . (($l->woeid == $woeid) ? ' selected="selected"' : '') . '>' . $n . '</option>';
		}
	}
	$header .= '</select> <input type="submit" value="Go" /></form>';

	$api_options = array("id" => $woeid);

	$trends_object = $cb->trends_place($api_options);
	twitter_api_status($trends_object);
	$trends = (array)$trends_object;
	unset($trends->httpstatus);
	twitter_rate_limit($trends->rate);
	unset($trends->rate);

	$search_url = 'search?query=';
	foreach($trends[0]->trends as $trend) {
		$row = array("<strong><a href='{$search_url}{$trend->query}'>{$trend->name}</a></strong>");
		$rows[] = array('data' => $row,  'class' => 'tweet');
	}
	$headers = array($header);
	$content = theme('table', $headers, $rows, array('class' => 'timeline'));
	theme('page', 'Trends', $content);
}

function js_counter($name, $length='140')
{
	$script = '<script type="text/javascript">
function updateCount() {
var remaining = ' . $length . ' - document.getElementById("' . $name . '").value.length;
document.getElementById("remaining").innerHTML = remaining;
if(remaining < 0) {
 var colour = "#FF0000";
 var weight = "bold";
} else {
 var colour = "";
 var weight = "";
}
document.getElementById("remaining").style.color = colour;
document.getElementById("remaining").style.fontWeight = weight;
setTimeout(updateCount, 400);
}
updateCount();
</script>';
	return $script;
}

function get_codebird() { //$url, $post_data = false) {

	//	Get the tokens
	list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);

	//	Create our CodeBird
	// $cb = \Codebird\Codebird::getInstance();
	// $cb->setToken($oauth_token, $oauth_token_secret);

	\Codebird\Codebird::setConsumerKey(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET); // static, see 'Using multiple Codebird instances'
	$cb = \Codebird\Codebird::getInstance();
	$cb->setToken($oauth_token, $oauth_token_secret);

	//	Start the API timer
	global $api_start;
	$api_start = microtime(1);

	return $cb;

	// global $api_time;
	// global $rate_limit;

	// //	Not every request is rate limited
	// if ($headers_array['x-rate-limit-limit']) {
	// 	$current_time = time();
	// 	$ratelimit_time = $headers_array['x-rate-limit-reset'];
	// 	$time_until_reset = $ratelimit_time - $current_time;
	// 	$minutes_until_reset = round($time_until_reset / 60);
	// 	$rate_limit .= " Rate Limit: " . $headers_array['x-rate-limit-remaining'] . " out of " . $headers_array['x-rate-limit-limit'] . " calls remaining for the next {$minutes_until_reset} minutes";
	// }

	// $api_time += microtime(1) - $api_start;
}


//	http://dev.twitter.com/pages/tweet_entities
function twitter_get_media($status) {
	//don't display images if: a) in the settings, b) NSFW
	if(setting_fetch('hide_inline') || stripos($status->text, 'NSFW') !== false) {
		return;
	}

	//	Get the inline image size
	$image_size = setting_fetch('image_size', "medium");

	//	If there are multiple images - or videos / gifs
	if ($status->extended_entities) {
		$media_html = "<span class=\"media\">";

		foreach($status->extended_entities->media as $media) {

			if ($_SERVER['HTTPS'] == "on" || (0 === strpos(BASE_URL, "https://"))) {
				$image = $media->media_url_https;
			} else {
				$image = $media->media_url;
			}

			if ($media->type == "video" || $media->type == "animated_gif") {

				$media_html .= "<video controls loop class=\"embedded\" poster=\"" . image_proxy($image) . "\">";

				//	Array is reversed in the hope that the highest resolution video is at the end
				foreach (array_reverse($media->video_info->variants) as $vid) {
					$video_url = $vid->url;
					$video_type = $vid->content_type;
					$media_html .= "<source src=\"" . image_proxy($video_url) . "\" type=\"{$video_type}\">";
				}

				$media_html .= "Your browser does not support the video element.
					</video>";
			} else {
				$link = $media->url;

				$width = $media->sizes->$image_size->w;
				$height = $media->sizes->$image_size->h;

				$media_html .= "<a href=\"" . image_proxy($image) . ":orig\" target=\"" . get_target() . "\" class=\"action\">
				                  <img src=\"" . image_proxy($image) . ":{$image_size}\" width=\"{$width}\" height=\"{$height}\">
				               </a>";
			}
		}
		$media_html .= "</span>";

		return $media_html;

	} else if($status->entities->media) {

		$media_html = '';

		foreach($status->entities->media as $media) {

			if ($_SERVER['HTTPS'] == "on" || (0 === strpos(BASE_URL, "https://"))) {
				$image = $media->media_url_https;
			} else {
				$image = $media->media_url;
			}

			$link = $media->url;

			$width = $media->sizes->$image_size->w;
			$height = $media->sizes->$image_size->h;

			$media_html .= "<span class=\"media\"><a href=\"" . image_proxy($image) . ":orig\" target=\"" . get_target() . "\">";
			$media_html .=     "<img src=\"" . image_proxy($image) . ":{$image_size}\" width=\"{$width}\" height=\"{$height}\">";
			$media_html .= "</a></span>";
		}

		return $media_html;
	}
}

function twitter_parse_tags($input, $entities = false, $rel = false) {
	$out = $input;

	//Linebreaks.  Some clients insert \n for formatting.
	$out = nl2br($out);

	// Use the Entities to replace hyperlink URLs
	// http://dev.twitter.com/pages/tweet_entities
	if($entities) {
		if($entities->urls) {
			foreach($entities->urls as $urls) {
				if($urls->expanded_url != "") {
					$display_url = $urls->expanded_url;
				}
				else {
					$display_url = $urls->url;
				}

				//$url = $urls->url;
				//	Stop Invasive monitoring of URLs
				$url = $urls->expanded_url;
				$parsed_url = parse_url($url);

				if (empty($parsed_url['scheme'])) {
					$url = 'http://' . $url;
				}

				if (setting_fetch('gwt') == 'on') { // If the user wants links to go via GWT
					$encoded = urlencode($url);
					$link = "http://google.com/gwt/n?u={$encoded}";
				}
				else {
					$link = $url;
				}

				if ($rel) {
					$rel = " rel='{$rel}'";
				} else {
					$rel = '';
				}

				$link_html = '<a href="' . $link . '" target="' . get_target() . '"' . $rel .'>' . $display_url . '</a>';
				$url = $urls->url;

				// Replace all URLs *UNLESS* they have already been linked (for example to an image)
				$pattern = '#((?<!href\=(\'|\"))'.preg_quote($url,'#').')#i';
				$out = preg_replace($pattern,  $link_html, $out);
			}
		}

		if($entities->hashtags) {
			foreach($entities->hashtags as $hashtag) {
				$text = $hashtag->text;
				$pattern = '/(^|\s)([#＃]+)('. $text .')/iu';
				$link_html = ' <a href="hash/' . $text . '">#' . $text . '</a> ';
				$out = preg_replace($pattern,  $link_html, $out, 1);
			}
		}

		if($entities->media) {
			foreach($entities->media as $media) {
				$url = $media->url;
				$pattern = '#((?<!href\=(\'|\"))'.preg_quote($url,'#').')#i';
				$link_html = "<a href='{$media->url}' target='" . get_target() . "'>{$media->display_url}</a>";
				$out = preg_replace($pattern,  $link_html, $out, 1);
			}
		}

	}
	else {  // If Entities haven't been returned (usually because of search or a bio) use Autolink
		// Create an array containing all URLs
		$urls = Twitter_Extractor::create($input)
				->extractURLs();

		// Hyperlink the URLs
		if (setting_fetch('gwt') == 'on') { // If the user wants links to go via GWT
			foreach($urls as $url) {
				$encoded = urlencode($url);
				$out = str_replace($url, "<a href='http://google.com/gwt/n?u={$encoded}' target='" . get_target() . "'>{$url}</a>", $out);
			}
		}
		else {
			$out = Twitter_Autolink::create($out)
					->addLinksToURLs();
		}

		// Hyperlink the #
		$out = Twitter_Autolink::create($out)
				->setTarget('')
				->addLinksToHashtags();
	}

	// Hyperlink the @ and lists
	$out = Twitter_Autolink::create($out)
			->setTarget('')
			->addLinksToUsernamesAndLists();

	// Emails
	$tok = strtok($out, " \n\t\n\r\0");	// Tokenise the string by whitespace

	while ($tok !== false) {	// Go through all the tokens
		$at = stripos($tok, "@");	// Does the string contain an "@"?

		if ($at && $at > 0) { // @ is in the string & isn't the first character
			$tok = trim($tok, "?.,!\"\'");	// Remove any trailing punctuation

			if (filter_var($tok, FILTER_VALIDATE_EMAIL)) {	// Use the internal PHP email validator
				$email = $tok;
				$out = str_replace($email, "<a href=\"mailto:{$email}\">{$email}</a>", $out);	// Create the mailto: link
			}
		}
		$tok = strtok(" \n\t\n\r\0");	// Move to the next token
	}

	//Return the completed string
	return $out;
}

function format_interval($timestamp, $granularity = 2) {
	$units = array(
	'year' => 31536000,
	'day'  => 86400,
	'hour' => 3600,
	'min'  => 60,
	'sec'  => 1
	);
	$output = '';
	foreach ($units as $key => $value) {
		if ($timestamp >= $value) {
			$output .= ($output ? ' ' : ''). pluralise($key, floor($timestamp / $value), true);
			$timestamp %= $value;
			$granularity--;
		}
		if ($granularity == 0) {
			break;
		}
	}
	return $output ? $output : '0 sec';
}

function twitter_status_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {

		$cb = get_codebird();

		$api_options = "id={$id}";

		$status = $cb->statuses_show_ID($api_options);
		twitter_api_status($status);

		$text = $status->text;	//	Grab the text before it gets formatted

		$content = theme('status', $status);

		//	Show a link to the original tweet
		$screen_name = $status->from->screen_name;
		$content .= '<p>
		                <a href="https://twitter.com/' . $screen_name . '/status/' . $id . '" target="'. get_target() .
		                '">'._(LINK_VIEW_ORIGINAL).'</a> | ';

		//	Translate the tweet
		$content .= '   <a href="https://translate.google.com/m?hl=en&sl=auto&ie=UTF-8&q=' . urlencode($text) . '" target="'. get_target() . '">'._(LINK_TRANSLATE).'</a>
		            </p>';

		$content .= "<p>
		                <strong>
		                    <a href=\"https://mobile.twitter.com/{$screen_name}/status/{$id}/report\" target=\"". get_target() . "\">"
		                     ._(LINK_ABUSE).
		                    "</a>
		                </strong>
		            </p>";

		/* NO LONGER SUPPORTED WITH THE MOVE TO 1.1
		if (!$status->user->protected) {
			$thread = twitter_thread_timeline($id);
		}
		if ($thread) {
			$content .= '<p>And the experimental conversation view...</p>'.theme('timeline', $thread);
			$content .= "<p>Don't like the thread order? Go to <a href='settings'>settings</a> to reverse it. Either way - the dates/times are not always accurate.</p>";
		}
		*/
		theme('page', "{$screen_name} Status {$id}", $content);
	}
}

// function twitter_thread_timeline($thread_id) {
// 	$request = "https://search.twitter.com/search/thread/{$thread_id}";
// 	$tl = twitter_standard_timeline(twitter_fetch($request), 'thread');
// 	return $tl;
// }

function twitter_retweet_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$cb = get_codebird();

		$api_options = array("id" => $id);
		$tl = $cb->statuses_show_ID($api_options);
		twitter_api_status($tl);

		$content = theme('retweet', $tl);
		theme('page', 'Retweet', $content);
	}
}

function twitter_refresh($page = null) {
	if (isset($page)) {
		$page = BASE_URL . $page;
	} else {
		$page = $_SERVER['HTTP_REFERER'];
	}
	header('Location: '. $page);
	exit();
}

function twitter_delete_page($query) {
	twitter_ensure_post_action();

	$id = (string) $query[1];
	if (is_numeric($id)) {
		$cb = get_codebird();
		$api_options = array("id" => $id);
		$response = $cb->statuses_destroy_ID($api_options);
		twitter_api_status($response);

		twitter_refresh(user_current_username());
	}
}

function twitter_deleteDM_page($query) {
	//Deletes a DM
	twitter_ensure_post_action();

	$id = (string) $query[1];
	if (is_numeric($id)) {
		$cb = get_codebird();
		$api_options = array("id" => $id);
		$response = $cb->directMessages_destroy($api_options);
		twitter_api_status($response);

		twitter_refresh('messages/');
	}
}

function twitter_delete_saved_search_page($query) {
	//Deletes a saved search
	twitter_ensure_post_action();

	$id = (string) $query[1];
	if (is_numeric($id)) {
		$cb = get_codebird();
		$api_options = array("id" => $id);
		$response = $cb->savedSearches_destroy_ID($api_options);
		twitter_api_status($response);

		twitter_refresh('search/');
	}
}

function twitter_ensure_post_action() {
	// This function is used to make sure the user submitted their action as an HTTP POST request
	// It slightly increases security for actions such as Delete, Block and Spam
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		theme('error', "<h2>"._(ERROR)."</h2><p>"._(ERROR_INVALID_METHOD)."</p>");
	}
}

function twitter_follow_page($query) {
	$screen_name = $query[1];
	if ($screen_name) {
		$cb = get_codebird();
		$api_options = array("screen_name" => $screen_name);

		if($query[0] == 'follow'){
			@twitter_api_status($cb->friendships_create($api_options));
		} else {
			@twitter_api_status($cb->friendships_destroy($api_options));
		}
		twitter_refresh('friends');
	}
}

function twitter_block_page($query) {
	twitter_ensure_post_action();
	$screen_name = $query[1];
	if ($screen_name) {

		$cb = get_codebird();
		$api_options = array("screen_name" => $screen_name);

		if($query[0] == 'block'){
			@twitter_api_status($cb->blocks_create($api_options));
			twitter_refresh("confirmed/block/{$screen_name}");
		} else {
			@twitter_api_status($cb->blocks_destroy($api_options));
			twitter_refresh("confirmed/unblock/{$screen_name}");
		}
	}
}

function twitter_spam_page($query)
{
	//http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-report_spam
	//We need to post this data
	twitter_ensure_post_action();
	$screen_name = $query[1];

	$cb = get_codebird();
	$api_options = array("screen_name" => $screen_name);
	@twitter_api_status($cb->users_reportSpam($api_options));

	//Where should we return the user to?  Back to the user
	twitter_refresh("confirmed/spam/{$screen_name}");
}


function twitter_confirmation_page($query)
{
	// the URL /confirm can be passed parameters like so /confirm/param1/param2/param3 etc.
	$action = $query[1];
	$target = $query[2];	//The name of the user we are doing this action on
	$target_id = $query[3];	//The targets's ID.  Needed to check if they are being blocked.

	switch ($action) {
		case 'block':
			if (twitter_block_exists($target_id)) //Is the target blocked by the user?
			{
				$action = 'unblock';
				$content  = "<p>".sprintf(_(ARE_YOU_SURE), "Unblock {$target}")."</p>";
				$content .= '<ul><li>They will see your updates on their home page if they follow you again.</li><li>You <em>can</em> block them again if you want.</li></ul>';
			}
			else
			{
				$content = "<p>".sprintf(_(ARE_YOU_SURE), "{$action} {$target}")."</p>";
				$content .= "<ul>
				                <li>You won't show up in their list of friends</li>
				                <li>They won't see your updates on their home page</li>
				                <li>They won't be able to follow you</li>
				                <li>You <em>can</em> unblock them but you will need to follow them again afterwards</li>
				            </ul>";
			}
			break;

		case 'delete':
			$cb = get_codebird();
			$api_options = array("id" => $target);
			$status = $cb->statuses_show_ID($api_options);
			@twitter_api_status($status);

			$content = '<p>'._(ARE_YOU_SURE_DELETE).'</p>';
			$content .= "<ul>
			                 <li>{$status->text}</li>
			                 <li>"._(NO_UNDO)."</li>
			            </ul>";
			break;

		case 'deleteDM':
			$cb = get_codebird();
			$api_options = array("id" => $target);
			$status = $cb->directMessages_show($api_options);
			@twitter_api_status($status);
			$content = '<p>'._(ARE_YOU_SURE_DELETE_DM).'</p>';
			$content .= "<ul>
			                <li>"._(DIRECT_MESSAGE).": {$status->text}</li>
			                <li>"._(NO_UNDO)."</li>
			                <li>"._(DELETE_DM_NOTIFICATION)."</li>
			            </ul>";
			break;

		case 'deleteSavedSearch':
			$cb = get_codebird();
			$api_options = array("id" => $target);

			$search = $cb->savedSearches_show_ID($api_options);
			@twitter_api_status($search);
			$content = '<p>'._(ARE_YOU_SURE_DELETE_SEARCH).'</p>';
			$content .= "<ul>
			                <li>"._(SEARCH_BUTTON).": {$search->query}</li>
			                <li>"._(NO_UNDO)."</li>
			            </ul>";
			break;

		case 'spam':
			$content  = "<p>".sprintf(_(SPAM_1),$target)."</p>";
			$content .= "<p>"._(SPAM_2)."</p>";
			break;

		case 'hideretweets':
			$content  = "<p>".sprintf(_(HIDE_RETWEETS),$target)."</p>";
			$content .= "<ul><li>".sprintf(_(HIDE_RETWEETS_1),$target)."</li>
			                 <li>".sprintf(_(HIDE_RETWEETS_2),$target)."</li></ul>";
			break;

		case 'showretweets':
			$content  = "<p>".sprintf(_(SHOW_RETWEETS),$target)."</p>";
			$content .= "<ul><li>".sprintf(_(SHOW_RETWEETS_1),$target)."</li>
			                 <li>"._(SHOW_RETWEETS_2)."</li></ul>";
			break;
		case '':
			theme('error', "<h2>"._(ERROR)."</h2><p>"._(ERROR_NOTHING_TO_CONFIRM)."</p>");
			break;


	}
	$content .= "<form action='$action/$target' method='post'>
						<input type='submit' value='"._(CONFIRM_BUTTON)."' />
					</form>";
	theme('Page', 'Confirm', $content);
}

function twitter_confirmed_page($query)
{
        // the URL /confirm can be passed parameters like so /confirm/param1/param2/param3 etc.
        $action = $query[1]; // The action. block, unblock, spam
        $target = $query[2]; // The username of the target

	switch ($action) {
	   case 'block':
			$content  = "<p>
								<span class='avatar'>
									<img src='images/dabr.png' width='48' height='48' />
								</span>
								<span class='status shift'>".sprintf(_(BLOCKED_MESSAGE),$target)."</span>
							</p>";
         break;
	   case 'unblock':
         $content  = "<p>
								<span class='avatar'>
									<img src='images/dabr.png' width='48' height='48' />
								</span>
								<span class='status shift'>".sprintf(_(UNBLOCKED_MESSAGE),$target)."</span>
							</p>";
         break;
	   case 'spam':
         $content = "<p>
								<span class='avatar'>
									<img src='images/dabr.png' width='48' height='48' />
								</span>
								<span class='status shift'>".sprintf(_(SPAMMER_MESSAGE),$target)."</span>
							</p>";
         break;
	}
 	theme ('Page', 'Confirmed', $content);
}

function twitter_retweets($query) {
	$user = $query[1];	//The name of the user we are doing this action on

	$cb = get_codebird();

	$api_options = array("screen_name" => $user);

	if($user) {
		if($query[0] == 'hideretweets') {
			$api_options["retweets"] = false;
			@twitter_api_status($cb->friendships_update($api_options));
		} else {
			$api_options["retweets"] = true;
			@twitter_api_status($cb->friendships_update($api_options));
		}
		twitter_refresh($user);
	}
}

function twitter_friends_page($query) {
	$user = $query[1];
	if (!$user) {
		user_ensure_authenticated();
		$user = user_current_username();
	}

	$cursor = $_GET['cursor'];

	if (!is_numeric($cursor)) {
		$cursor = -1;
	}

	$cb = get_codebird();

	$api_options = array("screen_name" => $user);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = $cb->friends_list($api_options);
	twitter_api_status($tl);

	$content = theme('users_list', $tl);
	theme('page', 'Friends', $content);
}

function twitter_followers_page($query) {
	$user = $query[1];
	if (!$user) {
		user_ensure_authenticated();
		$user = user_current_username();
	}
	$cursor = $_GET['cursor'];
	if (!is_numeric($cursor)) {
		$cursor = -1;
	}

	$cb = get_codebird();

	$api_options = array("screen_name" => $user);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = $cb->followers_list($api_options);
	twitter_api_status($tl);

	$content = theme('users_list', $tl);
	theme('page', 'Followers', $content);
}

function twitter_blocks() {

	$cursor = $_GET['cursor'];
	if (!is_numeric($cursor)) {
		$cursor = -1;
	}

	$cb = get_codebird();

	$api_options = array("skip_status" => "true");
	$api_options["count"] = setting_fetch('perPage', 20);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = $cb->blocks_list($api_options);
	twitter_api_status($tl);

	$content = theme('users_list', $tl);
	theme('page', 'Blocked Users', $content);
}

//  Shows first 100 users who retweeted a specific status (limit defined by twitter)
function twitter_retweeters_page($query) {
	// Which tweet are we looking for?
	$id = $query[1];

	$cb = get_codebird();
	$api_options = array("id" => $id);
	$users = $cb->statuses_retweets_ID($api_options);
	twitter_api_status($users);

	// Format the output
	$content = theme('users_list', $users);
	theme('page', sprintf(_(RETWEET_LIST),$id), $content);
}

function twitter_update() {
	//	Was this request sent by POST?
	twitter_ensure_post_action();

	$cb = get_codebird();

	$api_options  = array();

	//	Upload the image (if there is one) first
	if ($_FILES['image']['tmp_name']) {

		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		if (finfo_file($finfo, $_FILES['image']['tmp_name']) == "video/mp4") {	//	For Videos
			$file       = $_FILES['image']['tmp_name'];
			$size_bytes = filesize($file);
			$fp         = fopen($file, 'r');

			// INIT the upload

			$reply = $cb->media_upload([
			    'command'     => 'INIT',
			    'media_type'  => 'video/mp4',
			    'total_bytes' => $size_bytes
			]);

			$media_id = $reply->media_id_string;

			// APPEND data to the upload

			$segment_id = 0;

			while (! feof($fp)) {
			    $chunk = fread($fp, 1048576); // 1MB per chunk for this sample

			    $reply = $cb->media_upload([
			        'command'       => 'APPEND',
			        'media_id'      => $media_id,
			        'segment_index' => $segment_id,
			        'media'         => $chunk
			    ]);

			    $segment_id++;
			}

			fclose($fp);

			// FINALIZE the upload

			$reply = $cb->media_upload([
			    'command'       => 'FINALIZE',
			    'media_id'      => $media_id
			]);

			// var_dump($reply);

			if ($reply->httpstatus < 200 || $reply->httpstatus > 299) {
			    die();
			}

			// Now use the media_id in a tweet
			$api_options['media_ids'] = $media_id;

			// $reply = $cb->statuses_update([
			//     'status'    => 'Twitter now accepts video uploads.',
			//     'media_ids' => $media_id
			// ]);
		} else {	//	Just Images

			// these files to upload. You can also just upload 1 image!
			$media_files = array(
				$_FILES['image']['tmp_name']
			);

			// will hold the uploaded IDs
			$media_ids = array();

			foreach ($media_files as $file) {
				// upload all media files
				$reply = $cb->media_upload(array(
					'media' => $file
				));
				twitter_api_status($reply);
				// and collect their IDs
				$media_ids[] = $reply->media_id_string;
			}

			// convert media ids to string list
			$media_ids = implode(',', $media_ids);

			// send tweet with these medias
			$api_options['media_ids'] = $media_ids;
		}
	}

	//	POSTing adds slashes, let's get rid of them.
	//	Or not...
	$status_text = trim($_POST['status']);

	if ($status_text) {
		//	Ensure that the text is properly escaped
		$api_options["status"] = $status_text;

		//	Is this a reply?
		$in_reply_to_id = (string) $_POST['in_reply_to_id'];
		if (is_numeric($in_reply_to_id)) {
			$api_options["in_reply_to_status_id"] = $in_reply_to_id;
		}

		// Geolocation parameters
		list($lat, $long) = explode(',', $_POST['location']);

		//$geo = 'N';
		if (is_numeric($lat) && is_numeric($long)) {
		//	$geo = 'Y';
			$api_options['lat']  = $lat;
			$api_options['long'] = $long;
		}

		//	Send the status
		$reply = $cb->statuses_update($api_options);
		twitter_api_status($reply);
	}
	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_retweet($query) {
	twitter_ensure_post_action();
	$id = $query[1];
	if (is_numeric($id)) {

		$cb = get_codebird();
		$api_options = array("id" => $id);
		$cb->statuses_retweet_ID($api_options);

	}
	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_replies_page() {
	$cb = get_codebird();

	$api_options = "";

	$per_page = setting_fetch('perPage', 20);
	$api_options = "count=$per_page";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	$replies = $cb->statuses_mentionsTimeline($api_options);
	twitter_api_status($replies);

	$tl = twitter_standard_timeline($replies, 'replies');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', 'Replies', $content);
}

function twitter_retweets_page() {
	$cb = get_codebird();
	$api_options = "";

	$per_page = setting_fetch('perPage', 20);
	$api_options = "count=$per_page";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	$retweets = $cb->statuses_retweetsOfMe($api_options);
	twitter_api_status($retweets);

	$tl = twitter_standard_timeline($retweets, 'replies');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', 'Retweets', $content);
}

function twitter_directs_page($query) {

	$cb = get_codebird();
	$api_options = array("count" => setting_fetch('perPage', 20), "full_text" => true);

	$action = strtolower(trim($query[1]));
	switch ($action) {
		case 'create':
			$to = $query[2];
			$content = theme('directs_form', $to);
			theme('page', 'Create DM', $content);

		case 'send':
			twitter_ensure_post_action();

			//	Upload the image (if there is one) first
			if ($_FILES['image']['tmp_name']) {

				$finfo = finfo_open(FILEINFO_MIME_TYPE);

				if (finfo_file($finfo, $_FILES['image']['tmp_name']) == "video/mp4") {	//	For Videos
					$file       = $_FILES['image']['tmp_name'];
					$size_bytes = filesize($file);
					$fp         = fopen($file, 'r');

					// INIT the upload

					$reply = $cb->media_upload([
					    'command'     => 'INIT',
					    'media_type'  => 'video/mp4',
					    'total_bytes' => $size_bytes
					]);

					$media_id = $reply->media_id_string;

					// APPEND data to the upload

					$segment_id = 0;

					while (! feof($fp)) {
					    $chunk = fread($fp, 1048576); // 1MB per chunk for this sample

					    $reply = $cb->media_upload([
					        'command'       => 'APPEND',
					        'media_id'      => $media_id,
					        'segment_index' => $segment_id,
					        'media'         => $chunk
					    ]);

					    $segment_id++;
					}

					fclose($fp);

					// FINALIZE the upload

					$reply = $cb->media_upload([
					    'command'       => 'FINALIZE',
					    'media_id'      => $media_id
					]);

					// var_dump($reply);

					if ($reply->httpstatus < 200 || $reply->httpstatus > 299) {
					    die();
					}

					// Now use the media_id in a tweet
					$api_options['media_ids'] = $media_id;

					// $reply = $cb->statuses_update([
					//     'status'    => 'Twitter now accepts video uploads.',
					//     'media_ids' => $media_id
					// ]);
				} else {	//	Just Images

					// these files to upload. You can also just upload 1 image!
					$media_files = array(
						$_FILES['image']['tmp_name']
					);

					// will hold the uploaded IDs
					$media_ids = array();

					foreach ($media_files as $file) {
						// upload all media files
						$reply = $cb->media_upload(array(
							'media' => $file
						));
						twitter_api_status($reply);
						// and collect their IDs
						$media_ids[] = $reply->media_id_string;
					}

					// convert media ids to string list
					$media_ids = implode(',', $media_ids);

					// send tweet with these medias
					$api_options['media_ids'] = $media_ids;
				}
			}

			$to = trim(stripslashes(str_replace('@','',$_POST['to'])));
			$message = trim(stripslashes($_POST['message']));
			$api_options["screen_name"] = $to;
			$api_options["text"] = $message;
			@twitter_api_status($cb->directMessages_new($api_options));
			twitter_refresh('messages/sent');

		case 'sent':
			if ($_GET['max_id']) {
				$api_options["max_id"] = $_GET['max_id'];
			}

			$tl = $cb->directMessages_sent($api_options);
			twitter_api_status($tl);

			$tl = twitter_standard_timeline($tl, 'directs_sent');
			$content = theme_directs_menu();
			$content .= theme('timeline', $tl);
			theme('page', 'DM Sent', $content);

		case 'inbox':
		default:
			if ($_GET['max_id']) {
				$api_options["max_id"] = $_GET['max_id'];
			}

			$tl = $cb->directMessages($api_options);
			twitter_api_status($tl);
			$tl = twitter_standard_timeline($tl, 'directs_inbox');
			$content = theme_directs_menu();
			$content .= theme('timeline', $tl);
			theme('page', 'DM Inbox', $content);
	}
}


function twitter_search_page() {
	$cb = get_codebird();

	//	Save a search
	if (isset($_POST['query'])) {
		$query = $_POST['query'];
		$api_options = array('query' => html_entity_decode($query));
		$cb->savedSearches_create($api_options);
		twitter_refresh("search?query={$query}");
	}

	//	What was searched for. e.g. `/search?query=hello`
	$search_query = $_GET['query'];

	// Geolocation parameters
	list($lat, $long) = explode(',', $_GET['location']);
	$loc = $_GET['location'];
	$radius = $_GET['radius'];

	//	Get Saved Searches
	$saved_searches = $cb->savedSearches_list();
	twitter_api_status($saved_searches);

	//	Sort the searches by the time they were created
	$sorted_saved_searches = array();
	foreach ($saved_searches as &$saved_search) {
		$time = strtotime($saved_search->created_at);
		$sorted_saved_searches[$time] = $saved_search;
	}
	ksort($sorted_saved_searches);

	//	Generate the search form
	$content = theme('search_form', $search_query, $sorted_saved_searches);

	//	Use the first Saved Search as the default search term, if no search term was entered.
	if (!isset($search_query)) {
		$first = true;
		foreach ( $sorted_saved_searches as $saved_search )
		{
			if ( $first )
			{
				$search_query = $saved_search->query;
				$first = false;
			}
		}
	}

	//	Generate a timeline of tweets
	if ($search_query) {
		$tl = twitter_search($search_query, $lat, $long, $radius);
		$content .= theme('timeline', $tl);
	}
	theme('page', 'Search', $content);
}

function twitter_search($search_query, $lat = null, $long = null, $radius = null) {

	$per_page = setting_fetch('perPage', 20);

	$cb = get_codebird();
	$api_options = array("q" => urlencode($search_query),
		                 "count" => $per_page,
		                 "result_type" => "recent" // Make this customisable?
		                 );

	// $request = API_NEW."search/tweets.json?result_type=recent&q={$search_query}&rpp={$per_page}";
	if ($_GET['max_id']) {
		$api_options["max_id"] = $_GET['max_id'];
		// $request .= '&max_id='.$_GET['max_id'];
	}
	if ($lat && $long) {
		$geocode = "{$lat},{$long}";

		if ($radius) {
			$geocode .= ",{$radius}";
		}
		else {
			$geocode .= ",{1km}";
		}

		$api_options["geocode"] = $geocode;
	}

	$tl = $cb->search_tweets($api_options);
	twitter_api_status($tl);

	$tl = twitter_standard_timeline($tl->statuses, 'search');
	return $tl;
}

function twitter_find_tweet_in_timeline($tweet_id, $tl) {
	// Parameter checks
	if (!is_numeric($tweet_id) || !$tl) return;

	// Check if the tweet exists in the timeline given
	if (array_key_exists($tweet_id, $tl)) {
		// Found the tweet
		$tweet = $tl[$tweet_id];
	} else {
		$cb = get_codebird();
		$api_options = array("id" => $tweet_id);
		$tweet = $cb->statuses_show_ID($api_options);
		twitter_api_status($tweet);
	}
	return $tweet;
}

function twitter_user_page($query) {
	$screen_name    = $query[1];
	// echo "<h1>q1 = {$screen_name}</h1>";
	$subaction      = $query[2];
	// echo "<h1>q2 = {$subaction}</h1>";
	$in_reply_to_id = (string) $query[3];
	// echo "<h1>q3 = {$in_reply_to_id}</h1>";

	$content = '';

	if (!$screen_name) {
		// theme('error', 'No username given');

		//	Ugly cludge because @user is a real user
		twitter_refresh('user/user');
	}

	// Load up user profile information and one tweet
	$user = twitter_user_info($screen_name);

	// If the user has at least one tweet
	if (isset($user->status)) {
		// Fetch the timeline early, so we can try find the tweet they're replying to

		$cb = get_codebird();

		$api_options = "";

		$per_page = setting_fetch('perPage', 20);
		$api_options = "&count={$per_page}";

		//	If we're paginating through
		if ($_GET['max_id']) {
			$api_options .= '&max_id='.$_GET['max_id'];
		}

		$api_options .= "&screen_name={$screen_name}";

		$user_timeline = $cb->statuses_userTimeline($api_options);
		twitter_api_status($user_timeline);

		$tl = twitter_standard_timeline($user_timeline, 'user');
		// $content = theme('status_form');
		// $content .= theme('timeline', $tl);
		// theme('page', 'user', $content);
	}

	// Build an array of people we're talking to
	$to_users = array($user->screen_name);

	// Build an array of hashtags being used
	$hashtags = array();

	// Are we replying to anyone?
	if (is_numeric($in_reply_to_id)) {
		$tweet = twitter_find_tweet_in_timeline($in_reply_to_id, $tl);

		$out = twitter_parse_tags($tweet->text);

		$content .= "<p>In reply to:<br />{$out}</p>";

		// if ($subaction == 'replyall') {
			$found = Twitter_Extractor::create($tweet->text)
				->extractMentionedUsernames();
			$to_users = array_unique(array_merge($to_users, $found));
		// }

		if ($tweet->entities->hashtags) {
			$hashtags = $tweet->entities->hashtags;
		}
	}

	// Build a status message to everyone we're talking to
	$status = '';
	foreach ($to_users as $username) {
		if (!user_is_current_user($username)) {
			$status .= "@{$username} ";
		}
	}

	// Add in the hashtags they've used
	foreach ($hashtags as $hashtag) {
		$status .= "#{$hashtag->text} ";
	}

	$content .= theme('status_form', $status, $in_reply_to_id);
	$content .= theme('user_header', $user);
	$content .= theme('timeline', $tl);

	theme('page', "User {$screen_name}", $content);
}

function twitter_favourites_page($query) {
	$cb = get_codebird();

	$screen_name = $query[1];
	if (!$screen_name) {
		user_ensure_authenticated();
		$screen_name = user_current_username();
	}

	$api_options = "";

	$per_page = setting_fetch('perPage', 20);
	$api_options = "&count={$per_page}";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	$api_options .= "&screen_name={$screen_name}";

	//echo "$api_options";
	$favorites_list = $cb->favorites_list($api_options);
	twitter_api_status($favorites_list);
	$tl = twitter_standard_timeline($favorites_list, 'favourites');
	// $content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', 'Favourites', $content);
}

function twitter_mark_favourite_page($query) {
	$id = (string) $query[1];
	if (!is_numeric($id)) return;

	$cb = get_codebird();

	$api_options = array('id' => $id);

	if ($query[0] == 'unfavourite') {
		@twitter_api_status($cb->favorites_destroy($api_options));
	}
	else {
		@twitter_api_status($cb->favorites_create($api_options));
	}
	twitter_refresh();
}

function twitter_home_page() {
	user_ensure_authenticated();

	$cb = get_codebird();

	$api_options = "";

	$per_page = setting_fetch('perPage', 20);
	$api_options = "&count={$per_page}";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	if ($_GET['since_id']) {
		$api_options .= '&since_id='.$_GET['since_id'];
	}

	$api_options .= "&screen_name={$screen_name}";

	//echo "$api_options";
	$home_timeline = $cb->statuses_homeTimeline($api_options);
	twitter_api_status($home_timeline);
	$tl = twitter_standard_timeline($home_timeline, 'friends');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', 'Home', $content);
}

function twitter_hashtag_page($query) {
	if (isset($query[1])) {
		$hashtag = '#'.$query[1];
		$content = theme('status_form', $hashtag.' ');
		$tl = twitter_search($hashtag);
		$content .= theme('timeline', $tl);
		theme('page', $hashtag, $content);
	} else {
		//	Ugly cludge because @hash is a real user
		twitter_refresh('user/hash');
		// theme('page', 'Hashtag', 'Hash hash!');
	}
}


function twitter_tweets_per_day($user, $rounding = 1) {
	// Helper function to calculate an average count of tweets per day
	$days_on_twitter = (time() - strtotime($user->created_at)) / 86400;
	return round($user->statuses_count / $days_on_twitter, $rounding);
}


function twitter_date($format, $timestamp = null) {
/*
	static $offset;
	if (!isset($offset)) {
		if (user_is_authenticated()) {
			if (array_key_exists('utc_offset', $_COOKIE)) {
				$offset = $_COOKIE['utc_offset'];
			} else {
				$user = twitter_user_info();
				$offset = $user->utc_offset;
				setcookie('utc_offset', $offset, time() + 3000000, '/');
			}
		} else {
			$offset = 0;
		}
	}
*/
	$offset = setting_fetch('utc_offset', 0) * 3600;
	if (!isset($timestamp)) {
		$timestamp = time();
	}
	return gmdate($format, $timestamp + $offset);
}

function twitter_standard_timeline($feed, $source) {
//	echo "<pre>";
	//var_dump($feed);
//	echo json_encode($feed);
	//	Remove the status elements from the array
	unset($feed->httpstatus);
	if ($feed->rate){
		twitter_rate_limit($feed->rate);
		unset($feed->rate);
	}

	$output = array();
//	if (!is_array($feed) && $source != 'thread') return $output;

	//32bit int / snowflake patch
	// if (is_array($feed)) {
	// 	foreach($feed as $key => $status) {
	// 		if($status->id_str) {
	// 			$feed[$key]->id = $status->id_str;
	// 		}
	// 		if($status->in_reply_to_status_id_str) {
	// 			$feed[$key]->in_reply_to_status_id = $status->in_reply_to_status_id_str;
	// 		}
	// 		if($status->retweeted_status->id_str) {
	// 			$feed[$key]->retweeted_status->id = $status->retweeted_status->id_str;
	// 		}
	// 	}
	// }

	foreach ($feed as $status) {
//		echo "</pre><br.>STATUS = " . $status->text;
	}

	switch ($source) {
		case 'status':
		case 'favourites':
		case 'friends':
		case 'replies':
		case 'retweets':
		case 'user':
		case 'search':
			foreach ($feed as $status) {
				$new = $status;
				if ($new->retweeted_status) {
					$retweet = $new->retweeted_status;
					unset($new->retweeted_status);
					$retweet->retweeted_by = $new;
					$retweet->original_id = $new->id;
					$new = $retweet;
				}
				$new->from = $new->user;
				unset($new->user);
				$output[(string) $new->id] = $new;
			}
			return $output;

		case 'directs_sent':
		case 'directs_inbox':
			foreach ($feed as $status) {
				$new = $status;
				if ($source == 'directs_inbox') {
					$new->from = $new->sender;
					$new->to = $new->recipient;
				} else {
					$new->from = $new->recipient;
					$new->to = $new->sender;
				}
				unset($new->sender, $new->recipient);
				$new->is_direct = true;
				$output[$new->id_str] = $new;
			}
			return $output;

		case 'thread':
			// First pass: extract tweet info from the HTML
			$html_tweets = explode('</li>', $feed);
			foreach ($html_tweets as $tweet) {
				$id = preg_match_one('#msgtxt(\d*)#', $tweet);
				if (!$id) continue;
				$output[$id] = (object) array(
					'id' => $id,
					'text' => strip_tags(preg_match_one('#</a>: (.*)</span>#', $tweet)),
					'source' => preg_match_one('#>from (.*)</span>#', $tweet),
					'from' => (object) array(
						'id' => preg_match_one('#profile_images/(\d*)#', $tweet),
						'screen_name' => preg_match_one('#twitter.com/([^"]+)#', $tweet),
						'profile_image_url' => preg_match_one('#src="([^"]*)"#' , $tweet),
					),
					'to' => (object) array(
						'screen_name' => preg_match_one('#@([^<]+)#', $tweet),
					),
					'created_at' => str_replace('about', '', preg_match_one('#info">\s(.*)#', $tweet)),
				);
			}
			// Second pass: OPTIONALLY attempt to reverse the order of tweets
			if (setting_fetch('reverse') == 'yes') {
				$first = false;
				foreach ($output as $id => $tweet) {
					$date_string = str_replace('later', '', $tweet->created_at);
					if ($first) {
						$attempt = strtotime("+$date_string");
						if ($attempt == 0) $attempt = time();
						$previous = $current = $attempt - time() + $previous;
					} else {
						$previous = $current = $first = strtotime($date_string);
					}
					$output[$id]->created_at = date('r', $current);
				}
				$output = array_reverse($output);
			}
			return $output;

		default:
			echo "<h1>$source</h1><pre>";
			print_r($feed); die();
	}
}

function preg_match_one($pattern, $subject, $flags = null) {
	preg_match($pattern, $subject, $matches, $flags);
	return trim($matches[1]);
}

function twitter_user_info($username = null) {
	// if (!$username)

	$cb = get_codebird();

	$api_options = "screen_name={$username}";
	$user_info = $cb->users_show($api_options);
	twitter_api_status($user_info);
	return $user_info;
}


function twitter_is_reply($status) {
	if (!user_is_authenticated()) {
		return false;
	}
	$user = user_current_username();

	//	Use Twitter Entities to see if this contains a mention of the user
	if ($status->entities)	// If there are entities
	{
		if ($status->entities->user_mentions)
		{
			$entities = $status->entities;

			foreach($entities->user_mentions as $mentions)
			{
				if ($mentions->screen_name == $user)
				{
					return true;
				}
			}
		}
		return false;
	}

	// If there are no entities (for example on a search) do a simple regex
	$found = Twitter_Extractor::create($status->text)->extractMentionedUsernames();
	foreach($found as $mentions)
	{
		// Case insensitive compare
		if (strcasecmp($mentions, $user) == 0)
		{
			return true;
		}
	}
	return false;
}

function pluralise($word, $count, $show = false) {
	if($show) $word = number_format($count) . " {$word}";
	return $word . (($count != 1) ? 's' : '');
}

function is_64bit() {
	$int = "9223372036854775807";
	$int = intval($int);
	return ($int == 9223372036854775807);
}


function x_times($count) {
	if($count == 1) return 'once';
	if($count == 2) return 'twice';
	if(is_int($count)) return number_format($count) . ' times';
	return $count . ' times';
}

function image_proxy($src, $size = "") {
	if(defined('IMAGE_PROXY_URL') && IMAGE_PROXY_URL != "") {
		$size = "";
		return IMAGE_PROXY_URL . urlencode($src);
	}
	else {
		return $src;
	}
}

function twitter_rate_limit($rate) {
	// echo "<br> RATE <pre>" . var_export($rate,true). "</pre>";
	if ($rate){
		global $rate_limit;
		$ratelimit_time = $rate["reset"]- time();
		$ratelimit_time = floor($ratelimit_time / 60);
		$rate_limit = $rate["remaining"] . "/" . $rate["limit"] . " reset in {$ratelimit_time} minutes";
	}
	// $backtrace = debug_backtrace();
	// echo "<BR> Called by <pre>". var_export($backtrace,true). "</pre><br/>";
}

function twitter_api_status(&$response) {
	global $rate_limit;
	global $api_time;
	global $api_start;
	$api_time += microtime(1) - $api_start;

	//	Store the rate limit
	if ($response->rate) {
		twitter_rate_limit($response->rate);
		unset($response->rate);
	}

	//	Have we any errors?
	if ($response->httpstatus) {
		$httpstatus = intval($response->httpstatus);
		// echo "STATUS <pre>$httpstatus</pre>";

		if ($response->errors) {
			$errors = current($response->errors);
			$error_message = $errors->message;
			$error_code = $errors->code;
		}

		switch($httpstatus)	{
			case 200:
			case 201:
				unset($response->httpstatus);	//	A-OK
				return;
			case 401:
				user_logout();
				theme('error', "<h2>"._(ERROR).": "._(ERROR_LOGIN)."</h2>".
							"<p>".sprintf(_(ERROR_TWITTER_MESSAGE), $error_message, $error_code)."</p>");
			case 429:
				theme('error', "<h2>"._(ERROR_RATE_LIMIT)."</h2><p>{$rate_limit}.</p>");
			case 403:
				theme('error', "<h2>"._(ERROR).": </h2>".
							"<p>".sprintf(_(ERROR_TWITTER_MESSAGE), $error_message, $error_code)."</p>");
			default:
				theme('error', "<h2>" . _(ERROR) . ": </h2>".
							"<p>".sprintf(_(ERROR_TWITTER_MESSAGE), $error_message, $error_code)."</p>");
		}
	}
}
