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
	'.favourite' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'.unfavourite' => array(
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
	'.follow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'.unfollow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'.confirm' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_confirmation_page',
	),
	'.confirmed' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_confirmed_page',
	),
	'.block' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'.unblock' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'.mute' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mute_page',
	),
	'.unmute' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mute_page',
	),
	'.spam' => array(
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
	'.delete' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_delete_page',
	),
	'.deleteDM' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_deleteDM_page',
	),
	'.deleteSavedSearch' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_delete_saved_search_page',
	),
	'.retweet' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweet_page',
	),
	'hash' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_hashtag_page',
	),
	'muted-list' => array(
		'security' => true,
		'callback' => 'twitter_muted_page',
		'display' => '🔇' // http://graphemica.com/%F0%9F%94%87
	),
	'blocked-list' => array(
		'security' => true,
		'callback' => 'twitter_blocks',
		'display'  => '⛔',
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
	'.retweeted-by' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_retweeters_page',
	),
	'account' => array(
		'security' => true,
		'callback' => 'twitter_profile_page',
		'display' => '☺'
	),
	'.showRetweets' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweets',
	),
	'.hideRetweets' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweets',
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
		execute_codebird("account_updateProfile",$api_options);

		$content = "<h2>"._(PROFILE_UPDATED)."</h2>";
	}

	//	http://api.twitter.com/1/account/update_profile_image.format
	if ($_FILES['image']['tmp_name']) {
		// these files to upload. You can also just upload 1 image!
		$api_options = array(
			"image" => $_FILES['image']['tmp_name']
		);
		execute_codebird("account_updateProfileImage",$api_options);
	}

	//	Twitter API is really slow!  If there's no delay, the old profile is returned.
	//	Wait for 5 seconds before getting the user's information, which seems to be sufficient
	//	See https://dev.twitter.com/rest/reference/post/account/update_profile_image
	sleep(5);

	// retrieve profile information
	$user = twitter_user_info(user_current_username());

	$content .= theme('user_header', $user);
	$content .= theme('profile_form', $user);

	theme('page', _(MENU_EDITPROFILE), $content);
}

function friendship_exists($user_a) {
	$api_options = array('target_screen_name' => $user_a);

	$friendship = execute_codebird("friendships_show",$api_options);

	if ($friendship->relationship->target->following == 1) {
		return true;
	} else {
		return false;
	}
}

function friendship($user_a) {
	$api_options = array('target_screen_name' => $user_a);

	$friendship = execute_codebird("friendships_show",$api_options);
	return $friendship;
}

function twitter_block_exists($user_id) {
	$api_options = array("user_id" => $user_id);

	// 0th element http://stackoverflow.com/questions/3851489/return-php-object-by-index-number-not-name
	$friendship = current(execute_codebird("friendships_lookup",$api_options));

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

	//	Fetch "local" names
	$api_options = array();

	$local_object = execute_codebird("trends_available",$api_options);
	$local = (array)$local_object;


	$header = '<form method="get" action="trends">
	         		<select name="woeid">';
	$header .=     	'<option value="1"' . (($woeid == 1) ? ' selected="selected"' : '') . '>Worldwide</option>';

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
			$header .= '<option value="' . $l->woeid . '"' . (($l->woeid == $woeid) ? ' selected="selected"' : '') . '>' .
								$n .
							'</option>';
		}
	}
	$header .= 		'</select>
						<input type="submit" value="'._(TREND_BUTTON).'" />
					</form>';

	$api_options = array("id" => $woeid);

	$trends_object = execute_codebird("trends_place",$api_options);

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
	theme('page', _(TRENDS_TITLE), $content);
}

function js_counter($id='status',$length = 140)
{
	$linkLength = 24;
	$remaining = $id . "-remaining";
	$remainingVar = $id . "Remaining";
	$functionName = $id . "Count";
	//	Via https://github.com/twitter/twitter-text/blob/master/js/twitter-text.js
	$script = "<script src=\"i/js/twitter-text.js\"></script>
	<script type=\"text/javascript\">
		twttr;
		function {$functionName}() {
			var {$remainingVar} = {$length} - twttr.txt.getTweetLength(document.getElementById(\"{$id}\").value);
			if (document.getElementById(\"file\") != undefined) {
				if (document.getElementById(\"file\").value != \"\") {
					{$remainingVar} = {$remainingVar} - {$linkLength};
				}
			}
			document.getElementById(\"{$remaining}\").innerHTML = {$remainingVar};
			if({$remainingVar} < 0) {
				var colour = \"#FF0000\";
				var weight = \"bold\";
			} else {
				var colour = \"\";
				var weight = \"\";
			}
			document.getElementById(\"{$remaining}\").style.color = colour;
			document.getElementById(\"{$remaining}\").style.fontWeight = weight;
			setTimeout({$functionName}, 400);
		}
		{$functionName}();
		</script>";
	return $script;
}

function get_codebird() {
	//	Get the tokens
	list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);

	//	Create our CodeBird
	// static, see 'Using multiple Codebird instances'
	\Codebird\Codebird::setConsumerKey(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET);
	$cb = \Codebird\Codebird::getInstance();
	$cb->setToken($oauth_token, $oauth_token_secret);

	//	Start the API timer
	global $api_start;
	$api_start = microtime(1);

	return $cb;
}

function execute_codebird($function, $api_options = NULL) {
	try {
		$cb = get_codebird();
		$result = $cb->$function($api_options);
		twitter_api_status($result);
		return $result;
	} catch (Exception $e) {
		theme('error',
				"<div class=\"tweet\">".
					"<h2>"._(ERROR)."</h2>".
					"<pre>".sprintf(_(ERROR_TWITTER_MESSAGE), $e->getMessage(), $e->getCode())."</pre>".
				"</div>"
				);
	}
}

//	http://dev.twitter.com/pages/tweet_entities
function twitter_get_media($status) {
	//don't display images if: a) in the settings, b) NSFW
	if(setting_fetch('dabr_hide_inline') || stripos($status->text, 'NSFW') !== false) {
		return;
	}

	//	Get the inline image size
	$image_size = setting_fetch('dabr_image_size', "medium");

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

				$media_html .= _(ERROR_VIDEO) . "</video>";
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

				if (setting_fetch('dabr_gwt') == 'on') { // If the user wants links to go via GWT
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
		if (setting_fetch('dabr_gwt') == 'on') { // If the user wants links to go via GWT
			foreach($urls as $url) {
				$encoded = urlencode($url);
				$out = str_replace($url,
										"<a href='http://google.com/gwt/n?u={$encoded}' target='" . get_target() . "'>{$url}</a>",
										$out);
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

	//Linebreaks.  Some clients insert \n for formatting.
	$out = nl2br($out);

	//Return the completed string
	return $out;
}

function format_interval($timestamp) {
	if ($timestamp<60)
		return sprintf(ngettext("TIME_SECOND %s",
							 "TIME_SECONDS %s",
							 $timestamp), $timestamp);
	$timestamp = round($timestamp/60);
	if ($timestamp<60)
		return sprintf(ngettext("TIME_MINUTE %s",
							"TIME_MINUTES %s",
							$timestamp),$timestamp);
	$timestamp = round($timestamp/60);
	if ($timestamp<24)
		return sprintf(ngettext("TIME_HOUR %s",
							"TIME_HOURS %s",
							$timestamp), $timestamp);
	$timestamp = round($timestamp/24);
	if ($timestamp<7)
		return sprintf(ngettext("TIME_DAY %s",
							 "TIME_DAYS %s",
							 $timestamp), $timestamp);
	$timestamp = round($timestamp/7);
	if ($timestamp<4)
		return sprintf(ngettext("TIME_MONTH %s",
							 "TIME_MONTHS %s",
							 $timestamp), $timestamp);
	$timestamp = round($timestamp/52);

	return sprintf(ngettext("TIME_YEAR %s",
						 "TIME_YEARS %s",
						 $timestamp), $timestamp);
}

function twitter_status_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$api_options = "id={$id}";
		$status = execute_codebird("statuses_show_ID",$api_options);

		$text = $status->text;	//	Grab the text before it gets formatted

		$content = theme('status', $status);

		//	Show a link to the original tweet
		$screen_name = $status->from->screen_name;
		$content .= '<p>
		                <a href="https://twitter.com/' . $screen_name . '/status/' . $id . '" target="'. get_target() .'">'.
								_(LINK_VIEW_ORIGINAL).
							'</a> | ';

		//	Translate the tweet
		$content .= '   <a href="https://translate.google.com/m?hl=en&sl=auto&ie=UTF-8&q=' . urlencode($text) .
										'" target="'. get_target() . '">'.
								_(LINK_TRANSLATE).
							'</a>
		            </p>';

		$content .= "<p>
		                <strong>
		                    <a href=\"https://mobile.twitter.com/{$screen_name}/status/{$id}/report\" ".
										"target=\"". get_target() . "\">" .
		                     _(LINK_ABUSE).
		                    "</a>
		                </strong>
		            </p>";

		theme('page', "{$screen_name} - {$id}", $content);
	}
}

function twitter_retweet_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$api_options = array("id" => $id);
		$tl = execute_codebird("statuses_show_ID",$api_options);

		$content = theme('retweet', $tl);
		theme('page', _(RETWEET), $content);
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
		$api_options = array("id" => $id);
		$response = execute_codebird("statuses_destroy_ID",$api_options);

		twitter_refresh(user_current_username());
	}
}

function twitter_deleteDM_page($query) {
	//Deletes a DM
	twitter_ensure_post_action();

	$id = (string) $query[1];
	if (is_numeric($id)) {
		$api_options = array("id" => $id);
		$response = execute_codebird("directMessages_destroy",$api_options);

		twitter_refresh('messages/');
	}
}

function twitter_delete_saved_search_page($query) {
	//Deletes a saved search
	twitter_ensure_post_action();

	$id = (string) $query[1];
	if (is_numeric($id)) {
		$api_options = array("id" => $id);
		$response = execute_codebird("savedSearches_destroy_ID",$api_options);

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
		$api_options = array("screen_name" => $screen_name);
		if($query[0] == '.follow'){
			execute_codebird("friendships_create",$api_options);
		} else {
			execute_codebird("friendships_destroy",$api_options);
		}
		twitter_refresh('friends');
	}
}

function twitter_block_page($query) {
	twitter_ensure_post_action();
	$screen_name = $query[1];
	if ($screen_name) {
		$api_options = array("screen_name" => $screen_name);

		if($query[0] == '.block'){
			execute_codebird("blocks_create",$api_options);
			twitter_refresh(".confirmed/.block/{$screen_name}");
		} else {
			execute_codebird("blocks_destroy",$api_options);
			twitter_refresh(".confirmed/.unblock/{$screen_name}");
		}
	}
}

function twitter_mute_page($query) {
	twitter_ensure_post_action();
	$screen_name = $query[1];
	if ($screen_name) {
		$api_options = array("screen_name" => $screen_name);

		if($query[0] == '.mute'){
			execute_codebird("mutes_users_create",$api_options);
			twitter_refresh(".confirmed/.mute/{$screen_name}");
		} else {
			execute_codebird("mutes_users_destroy",$api_options);
			twitter_refresh(".confirmed/.unmute/{$screen_name}");
		}
	}
}

function twitter_spam_page($query)
{
	//	http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-report_spam
	//	We need to post this data
	twitter_ensure_post_action();
	$screen_name = $query[1];

	$api_options = array("screen_name" => $screen_name);
	execute_codebird("users_reportSpam",$api_options);

	//Where should we return the user to?  Back to the user
	twitter_refresh(".confirmed/.spam/{$screen_name}");
}

function twitter_confirmation_page($query)
{
	//	The URL /confirm can be passed parameters like so /.confirm/.param1/param2/param3 etc.
	$action    = $query[1];
	$target    = $query[2];	//	The name of the user we are doing this action on
	$target_id = $query[3];	//	The targets's ID.  Needed to check if they are being blocked.

	switch ($action) {
		case '.block':
			$content = "<p>".sprintf(_(ARE_YOU_SURE_BLOCK), $target)."</p>";
			$content .= "<ul>
			                <li>"._(BLOCK_1)."</li>
			                <li>"._(BLOCK_2)."</li>
			                <li>"._(BLOCK_3)."</li>
			                <li>"._(BLOCK_4)."</li>
			            </ul>";
			break;
		case '.unblock':
			$action = '.unblock';
			$content  = "<p>".sprintf(_(ARE_YOU_SURE_UNBLOCK), $target)."</p>";
			$content .= "<ul>
								<li>"._(UNBLOCK_1)."</li>
								<li>"._(UNBLOCK_2)."</li>
							</ul>";
			break;
		case '.mute':
			$content  = "<p>".sprintf(_(ARE_YOU_SURE_MUTE),$target)."</p>";
			$content .= "<ul>
								 <li>"._(MUTE_1)."</li>
								 <li>"._(MUTE_2)."</li>
								 <li>"._(MUTE_3)."</li>
								 <li>"._(MUTE_4)."</li>
							</ul>";
			break;

		case '.unmute':
			$content  = "<p>".sprintf(_(ARE_YOU_SURE_UNMUTE),$target)."</p>";
			$content .= "<ul>
								 <li>"._(UNMUTE_1)."</li>
								 <li>"._(UNMUTE_2)."</li>
								 <li>"._(UNMUTE_3)."</li>
							</ul>";
			break;

		case '.delete':
			//	Display the Tweet which is being deleted
			$api_options = array("id" => $target);
			$status = 	execute_codebird("statuses_show_ID",$api_options);

			$content = '<p>'._(ARE_YOU_SURE_DELETE).'</p>';
			$content .= "<ul>
			                 <li>{$status->text}</li>
			                 <li>"._(NO_UNDO)."</li>
			            </ul>";
			break;

		case '.deleteDM':
			//	Display the message which is being deleted
			$api_options = array("id" => $target);
			$status = execute_codebird("directMessages_show",$api_options);

			$content = '<p>'._(ARE_YOU_SURE_DELETE_DM).'</p>';
			$content .= "<ul>
			                <li>"._(DIRECT_MESSAGE).": {$status->text}</li>
			                <li>"._(NO_UNDO)."</li>
			                <li>"._(DELETE_DM_NOTIFICATION)."</li>
			            </ul>";
			break;

		case '.deleteSavedSearch':
			$api_options = array("id" => $target);

			$search = execute_codebird("savedSearches_show_ID",$api_options);
			$content = '<p>'._(ARE_YOU_SURE_DELETE_SEARCH).'</p>';
			$content .= "<ul>
			                <li>"._(SEARCH_BUTTON).": {$search->query}</li>
			                <li>"._(NO_UNDO)."</li>
			            </ul>";
			break;

		case '.spam':
			$content  = "<p>".sprintf(_(SPAM_1),$target)."</p>";
			$content .= "<p>"._(SPAM_2)."</p>";
			break;

		case '.hideRetweets':
			$content  = "<p>".sprintf(_(HIDE_RETWEETS),$target)."</p>";
			$content .= "<ul><li>".sprintf(_(HIDE_RETWEETS_1),$target)."</li>
			                 <li>".sprintf(_(HIDE_RETWEETS_2),$target)."</li></ul>";
			break;

		case '.showRetweets':
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
	theme('Page', _(CONFIRM_TITLE), $content);
}

function twitter_confirmed_page($query)
{
        // the URL /.confirm can be passed parameters like so /.confirm/.param1/param2/param3 etc.
        $action = $query[1]; // The action. block, unblock, spam
        $target = $query[2]; // The username of the target

	switch ($action) {
	   case '.block':
			$content = theme_confirmation_message(sprintf(_(BLOCKED_MESSAGE),$target));
         break;
	   case '.unblock':
         $content = theme_confirmation_message(sprintf(_(UNBLOCKED_MESSAGE),$target));
         break;
	   case '.spam':
         $content = theme_confirmation_message(sprintf(_(SPAMMER_MESSAGE),$target));
         break;
		case '.mute':
			$content = theme_confirmation_message(sprintf(_(MUTED_MESSAGE),$target));
         break;
		case '.unmute':
			$content = theme_confirmation_message(sprintf(_(UNMUTED_MESSAGE),$target));
			break;
	}
 	theme ('Page', 'Confirmed', $content);
}

function twitter_retweets($query) {
	$user = $query[1];	//The name of the user we are doing this action on
	$api_options = array("screen_name" => $user);

	if($user) {
		if($query[0] == '.hideRetweets') {
			$api_options["retweets"] = false;
			execute_codebird("friendships_update",$api_options);
		} else {
			$api_options["retweets"] = true;
			execute_codebird("friendships_update",$api_options);
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

	$api_options = array("screen_name" => $user);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = execute_codebird("friends_list",$api_options);

	$content = "<h2>" . sprintf(_(FRIENDS_OF), $user) . "</h2>";
	$content .= theme('users_list', $tl);
	theme('page', _(MENU_FRIENDS), $content);
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

	$api_options = array("screen_name"=>$user);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = execute_codebird("followers_list",$api_options);

	$content = "<h2>" . sprintf(_(FOLLOWERS_OF), $user) . "</h2>";
	$content .= theme('users_list', $tl);
	theme('page', _(MENU_FOLLOWERS), $content);
}

function twitter_blocks() {
	$cursor = $_GET['cursor'];
	if (!is_numeric($cursor)) {
		$cursor = -1;
	}

	$api_options = array("skip_status" => "true");
	$api_options["count"] = setting_fetch('dabr_perPage', 20);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = execute_codebird("blocks_list",$api_options);

	$content = "<h2>"._(BLOCKED_TITLE)."</h2>\n";
	$content .= theme('users_list', $tl);

	theme('page', _(BLOCKED_TITLE), $content);
}

function twitter_muted_page() {
	$cursor = $_GET['cursor'];
	if (!is_numeric($cursor)) {
		$cursor = -1;
	}

	$api_options = array("skip_status" => "true");
	$api_options["count"] = setting_fetch('dabr_perPage', 20);

	if ($cursor > 0) {
		$api_options["cursor"] = $cursor;
	}

	$tl = execute_codebird("mutes_users_list",$api_options);

	$content = "<h2>"._(LIST_MUTED)."</h2>\n";
	$content .= theme('users_list', $tl);
	theme('page', _(LIST_MUTED), $content);
}

//  Shows first 100 users who retweeted a specific status (limit defined by twitter)
function twitter_retweeters_page($query) {
	// Which tweet are we looking for?
	$id = $query[1];

	//	List of retweeters
	$api_options = array("id" => $id);
	$users = execute_codebird("statuses_retweets_ID",$api_options);

	//	Display the Tweet which is being retweeted
	$api_options = array("id" => $id);
	$status = execute_codebird("statuses_show_ID",$api_options);

	// Format the output
	$title = sprintf(_(RETWEET_LIST),"{$status->user->screen_name}");

	$content =  "<h2>{$title}</h2>";
	$content .= "<p>".twitter_parse_tags($status->text)."</p>";
	$content .= theme('users_list', $users);

	theme('page', $title, $content);
}

function twitter_update() {
	//	Was this request sent by POST?
	twitter_ensure_post_action();

	$api_options  = array();

	//	Upload the image (if there is one) first
	if ($_FILES['image']['tmp_name']) {

		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		if (finfo_file($finfo, $_FILES['image']['tmp_name']) == "video/mp4") {	//	For Videos
			$file       = $_FILES['image']['tmp_name'];
			$size_bytes = filesize($file);
			$fp         = fopen($file, 'r');

			// INIT the upload

			$reply = execute_codebird("media_upload",[
			    'command'     => 'INIT',
			    'media_type'  => 'video/mp4',
			    'total_bytes' => $size_bytes
			]);

			$media_id = $reply->media_id_string;

			// APPEND data to the upload

			$segment_id = 0;

			while (! feof($fp)) {
			    $chunk = fread($fp, 1048576); // 1MB per chunk for this sample

			    $reply =execute_codebird("media_upload",[
			        'command'       => 'APPEND',
			        'media_id'      => $media_id,
			        'segment_index' => $segment_id,
			        'media'         => $chunk
			    ]);

			    $segment_id++;
			}

			fclose($fp);

			// FINALIZE the upload

			$reply = execute_codebird("media_upload",[
			    'command'       => 'FINALIZE',
			    'media_id'      => $media_id
			]);

			// var_dump($reply);

			// if ($reply->httpstatus < 200 || $reply->httpstatus > 299) {
			//     die();
			// }

			// Now use the media_id in a tweet
			$api_options['media_ids'] = $media_id;

		} else {	//	Just Images

			// these files to upload. You can also just upload 1 image!
			$media_files = array(
				$_FILES['image']['tmp_name']
			);

			// will hold the uploaded IDs
			$media_ids = array();

			foreach ($media_files as $file) {
				// upload all media files
				$reply = execute_codebird("media_upload",array(
					'media' => $file
				));
				// and collect their IDs
				$media_ids[] = $reply->media_id_string;
			}

			// convert media ids to string list
			$media_ids = implode(',', $media_ids);

			// send tweet with these medias
			$api_options['media_ids'] = $media_ids;
		}
	}

	//	Remove extra whitespace
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
		$reply = execute_codebird("statuses_update",$api_options);
	}
	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_retweet($query) {
	twitter_ensure_post_action();
	$id = $query[1];
	if (is_numeric($id)) {
		$api_options = array("id" => $id);
		execute_codebird("statuses_retweet_ID",$api_options);
	}
	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_replies_page() {
	$api_options = "";

	$per_page = setting_fetch('dabr_perPage', 20);
	$api_options = "count=$per_page";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	$replies = execute_codebird("statuses_mentionsTimeline",$api_options);

	$tl = twitter_standard_timeline($replies, 'replies');
	$content =  theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', _(MENU_REPLIES), $content);
}

function twitter_retweets_page() {
	$api_options = "";

	$per_page = setting_fetch('dabr_perPage', 20);
	$api_options = "count=$per_page";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	$retweets = execute_codebird("statuses_retweetsOfMe",$api_options);

	$tl = twitter_standard_timeline($retweets, 'replies');
	$content =  theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', _(RETWEETS_TITLE), $content);
}

function twitter_directs_page($query) {
	$api_options = array("count" => setting_fetch('dabr_perPage', 20), "full_text" => true);

	$action = strtolower(trim($query[1]));
	switch ($action) {
		case 'create':
			$to = $query[2];
			$content = theme('directs_form', $to);
			theme('page', _(CREATE_DM_TITLE), $content);

		case 'send':
			twitter_ensure_post_action();
			$to = trim(stripslashes(str_replace('@','',$_POST['to'])));
			$message = trim(stripslashes($_POST['message']));
			$api_options["screen_name"] = $to;
			$api_options["text"] = $message;
			execute_codebird("directMessages_new",$api_options);
			twitter_refresh('messages/sent');

		case 'sent':
			if ($_GET['max_id']) {
				$api_options["max_id"] = $_GET['max_id'];
			}

			$tl = execute_codebird("directMessages_sent",$api_options);

			$tl = twitter_standard_timeline($tl, 'directs_sent');
			$content = theme_directs_menu();
			$content .= theme('timeline', $tl);
			theme('page', _(DM_SENT_TITLE), $content);

		case 'inbox':
		default:
			if ($_GET['max_id']) {
				$api_options["max_id"] = $_GET['max_id'];
			}

			$tl = execute_codebird("directMessages",$api_options);
			$tl = twitter_standard_timeline($tl, 'directs_inbox');
			$content = theme_directs_menu();
			$content .= theme('timeline', $tl);
			theme('page', _(DM_INBOX_TITLE), $content);
	}
}


function twitter_search_page() {
	//	Save a search
	if (isset($_POST['query'])) {
		$query = $_POST['query'];
		$api_options = array('query' => html_entity_decode($query));
		execute_codebird("savedSearches_create",$api_options);
		twitter_refresh("search?query={$query}");
	}

	//	What was searched for. e.g. `/search?query=hello`
	$search_query = $_GET['query'];

	// Geolocation parameters
	list($lat, $long) = explode(',', $_GET['location']);
	$loc = $_GET['location'];
	$radius = $_GET['radius'];

	//	Get Saved Searches
	$saved_searches = execute_codebird("savedSearches_list");

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
	theme('page', _(MENU_SEARCH), $content);
}

function twitter_search($search_query, $lat = null, $long = null, $radius = null) {
	$per_page = setting_fetch('dabr_perPage', 20);

	$api_options = array("q" => urlencode($search_query),
		                 "count" => $per_page,
		                 "result_type" => "recent" // Make this customisable?
		                 );

	if ($_GET['max_id']) {
		$api_options["max_id"] = $_GET['max_id'];
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

	$tl = execute_codebird("search_tweets",$api_options);

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
		$api_options = array("id" => $tweet_id);
		$tweet = execute_codebird("statuses_show_ID",$api_options);
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
		$api_options = "";
		$per_page = setting_fetch('dabr_perPage', 20);
		$api_options = "&count={$per_page}";

		//	If we're paginating through
		if ($_GET['max_id']) {
			$api_options .= '&max_id='.$_GET['max_id'];
		}

		$api_options .= "&screen_name={$screen_name}";

		$user_timeline = execute_codebird("statuses_userTimeline",$api_options);

		$tl = twitter_standard_timeline($user_timeline, 'user');
	}

	// Build an array of people we're talking to
	$to_users = array($user->screen_name);

	// Build an array of hashtags being used
	$hashtags = array();

	// Are we replying to anyone?
	if (is_numeric($in_reply_to_id)) {
		$tweet = twitter_find_tweet_in_timeline($in_reply_to_id, $tl);

		$out = twitter_parse_tags($tweet->text);

		$content .= "<p>".sprintf(_(IN_REPLY_TO),$screen_name).":<br />{$out}</p>";

		//	Reply to all users mentioned in the tweet.
		//	TODO: Include the retweeter?
		$found = Twitter_Extractor::create($tweet->text)
			->extractMentionedUsernames();
		$to_users = array_unique(array_merge($to_users, $found));

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

	theme('page', sprintf(_(USER_TITLE),$screen_name), $content);
}

function twitter_favourites_page($query) {
	$screen_name = $query[1];
	if (!$screen_name) {
		user_ensure_authenticated();
		$screen_name = user_current_username();
	}

	$api_options = "";
	$per_page = setting_fetch('dabr_perPage', 20);
	$api_options = "&count={$per_page}";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	$api_options .= "&screen_name={$screen_name}";

	$favorites_list = execute_codebird("favorites_list",$api_options);
	$tl = twitter_standard_timeline($favorites_list, 'favourites');

	$content = "<h2>" . sprintf(_(FAVOURITES_OF), $screen_name) . "</h2>";
	$content .= theme('timeline', $tl);
	theme('page', _(FAVOURITES_TITLE), $content);
}

function twitter_mark_favourite_page($query) {
	$id = (string) $query[1];
	if (!is_numeric($id)) return;

	$api_options = array('id' => $id);

	if ($query[0] == '.unfavourite') {
		execute_codebird("favorites_destroy",$api_options);
	}
	else {
		execute_codebird("favorites_create",$api_options);
	}
	twitter_refresh();
}

function twitter_home_page() {
	user_ensure_authenticated();

	$api_options = "";
	$per_page = setting_fetch('dabr_perPage', 20);
	$api_options = "&count={$per_page}";

	//	If we're paginating through
	if ($_GET['max_id']) {
		$api_options .= '&max_id='.$_GET['max_id'];
	}

	if ($_GET['since_id']) {
		$api_options .= '&since_id='.$_GET['since_id'];
	}

	$api_options .= "&screen_name={$screen_name}";

	$home_timeline = execute_codebird("statuses_homeTimeline",$api_options);

	$tl = twitter_standard_timeline($home_timeline, 'friends');
	$content =  theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', _(MENU_HOME), $content);
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
	}
}

function twitter_tweets_per_day($user, $rounding = 0) {
	// Helper function to calculate an average count of tweets per day
	$days_on_twitter = (time() - strtotime($user->created_at)) / 86400;
	return round($user->statuses_count / $days_on_twitter, $rounding);
}

function twitter_date($format, $timestamp = null) {
	$offset = setting_fetch('dabr_utc_offset', 0) * 3600;
	if (!isset($timestamp)) {
		$timestamp = time();
	}
	return gmdate($format, $timestamp + $offset);
}

function twitter_standard_timeline($feed, $source) {

	//	Remove the HTTP elements from the array
	unset($feed->httpstatus);
	if ($feed->rate){
		twitter_rate_limit($feed->rate);
		unset($feed->rate);
	}

	$output = array();

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
	$api_options = "screen_name={$username}";
	$user_info = execute_codebird("users_show",$api_options);
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
	if ($rate){
		global $rate_limit;
		$ratelimit_time = $rate->reset- time();
		$ratelimit_time = floor($ratelimit_time / 60);
		$rate_limit = $rate->remaining . "/" . $rate->limit . " reset in {$ratelimit_time} minutes";
	}
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
				theme('error', "<h2>"._(ERROR)." "._(ERROR_LOGIN)."</h2>".
							"<p>".sprintf(_(ERROR_TWITTER_MESSAGE), $error_message, $error_code)."</p>");
			case 429:
				theme('error', "<h2>"._(ERROR_RATE_LIMIT)."</h2><p>{$rate_limit}.</p>",
						$response, $_POST);
			case 403:
				theme('error', "<h2>"._(ERROR)."</h2>".
							"<p>".sprintf(_(ERROR_TWITTER_MESSAGE), $error_message, $error_code)."</p>",
						$response, $_POST);
			default:
				theme('error', "<h2>"._(ERROR)."</h2>".
							"<p>".sprintf(_(ERROR_TWITTER_MESSAGE), $error_message, $error_code)."</p>",
						$response, $_POST);
		}
	}
}
