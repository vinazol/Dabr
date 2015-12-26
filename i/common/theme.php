<?php
require 'oembed.php';

$current_theme = false;

//	Setup
//	`theme('user_header', $user);` becomes `theme_user_header($user)` etc.
function theme() {
	global $current_theme;
	$args = func_get_args();
	$function = array_shift($args);
	$function = 'theme_'.$function;

	// if ($current_theme) {
	// 	$custom_function = $current_theme.'_'.$function;
	// 	if (function_exists($custom_function))
	// 	$function = $custom_function;
	// } else {
	// 	if (!function_exists($function))
	// 	return "<p>Error: theme function <b>{$function}</b> not found.</p>";
	// }
	return call_user_func_array($function, $args);
}

function theme_list($items, $attributes) {
	if (!is_array($items) || count($items) == 0) {
		return '';
	}
	$output = '<ul'.theme_attributes($attributes).'>';
	foreach ($items as $item) {
		$output .= "<li>$item</li>\n";
	}
	$output .= "</ul>\n";
	return $output;
}

// function theme_options($options, $selected = null) {
// 	if (count($options) == 0) return '';
// 	$output = '';
// 	foreach($options as $value => $name) {
// 		if (is_array($name)) {
// 			$output .= '<optgroup label="'.$value.'">';
// 			$output .= theme('options', $name, $selected);
// 			$output .= '</optgroup>';
// 		} else {
// 			$output .= '<option value="'.$value.'"'.($selected == $value ? ' selected="selected"' : '').'>'.$name."</option>\n";
// 		}
// 	}
// 	return $output;
// }

function theme_info($info) {
	$rows = array();
	foreach ($info as $name => $value) {
		$rows[] = array($name, $value);
	}
	return theme('table', array(), $rows);
}

function theme_table($headers, $rows, $attributes = null) {
	$out = '<div'.theme_attributes($attributes).'>';
	if (count($headers) > 0) {
		// $out .= '<thead><tr>';
		foreach ($headers as $cell) {
			$out .= theme_table_cell($cell, true);
		}
		// $out .= '</tr></thead>';
	}
	if (count($rows) > 0) {
		$out .= theme('table_rows', $rows);
	}
	$out .= '</div>';
	return $out;
}

function theme_table_rows($rows) {
	$i = 0;
    $out = '';
	foreach ($rows as $row) {
		if ($row['data']) {
			$cells = $row['data'];
			unset($row['data']);
			$attributes = $row;
		} else {
			$cells = $row;
			$attributes = false;
		}
		$attributes['class'] .= ($attributes['class'] ? ' ' : '') . ($i++ %2 ? 'even' : 'odd');
		$out .= '<div'.theme_attributes($attributes).'>';
		foreach ($cells as $cell) {
			$out .= theme_table_cell($cell);
		}
		$out .= "</div>\n";
	}
	return $out;
}

function theme_attributes($attributes) {
	if (!$attributes) return '';
    $out = '';
	foreach ($attributes as $name => $value) {
		$out .= " $name=\"$value\"";
	}
	return $out;
}

function theme_table_cell($contents, $header = false) {
	if (is_array($contents)) {
		$value = $contents['data'];
		unset($contents['data']);
		$attributes = $contents;
	} else {
		$value = $contents;
		$attributes = false;
	}
	return "<span".theme_attributes($attributes).">$value</span>";
}


function theme_error($message, $response = NULL, $post = NULL) {
	if ($response->errors) {
		$errors = current($response->errors);
		$error_message = $errors->message;
		$error_code = $errors->code;
	}

	//	Handle overly long messages by allowing the user to re-edit them.
	if (186 == $error_code)
	{
		$status = $post->status;
		$message .= theme_status_form($post["status"], $post["in_reply_to_id"]);
	}

	theme_page(_(ERROR), $message);
}

function theme_about() {
	return '<div id="about">
	            <h3>'._(WHAT_IS).'</h3>
                <ul>
                    <li>'._(ABOUT_CREDITS_1).'</li>
                    <li>'._(ABOUT_CREDITS_2).'</li>
                    <li>'._(ABOUT_CREDITS_3).'</li>
                    <li>'._(ABOUT_CREDITS_4).'</li>
                </ul>
                <p>'._(ABOUT_CREDITS_5).'</p>
            </div>';
}

function theme_page($title, $content) {
	$body = "";
	$body .= theme('menu_top');
	$body .= $content;
	if (DEBUG_MODE == 'ON') {
		global $dabr_start, $api_time, $services_time, $rate_limit;
		$time = microtime(1) - $dabr_start;
		$body .= '<p>'.
		         	sprintf(_('TIME_PROCESSED %s'), round($time, 4)).
		         	' ('.round(($time - $api_time - $services_time) / $time * 100).'% Dabr, '.
						round($api_time / $time * 100).'% Twitter, '.
						round($services_time / $time * 100).'% '._(TIME_PROCESSED_MEDIA).'). '.
						$rate_limit.
					'.</p>';
	}
    $meta = '';
	if ($title == 'Login') {
		$title = _(DABR_LOGIN);
		$meta = '<meta name="description" content="'._(DABR_META).'" />';
	}
	ob_start('ob_gzhandler');
	header('Content-Type: text/html; charset=utf-8');
	echo	'<!DOCTYPE html>
            <html>
               <head>
						<meta charset="utf-8" />
						<meta name="viewport" content="width=device-width; initial-scale=1;" />
						<title>Dabr - ' . $title . '</title>
						<base href="',BASE_URL,'" />
						<!--[if IE]><link rel="shortcut icon" href="favicon.ico"><![endif]-->
						<link rel="apple-touch-icon" href="i/images/apple-touch-icon.png">
						<link rel="icon" href="i/images/favicon.png">
						<link href="widgets" rel="stylesheet">';

	if ($title == _(SETTINGS_TITLE))
	{
		echo			'<link href="//fonts.googleapis.com/css?family=Schoolbell:400|Ubuntu+Mono:500|Raleway:500|Karma:500|Open+Sans:500" rel="stylesheet" type="text/css">';
	} else {
		echo			'<link href="//fonts.googleapis.com/css?family='.(setting_fetch("dabr_fonts","Raleway")).'" rel="stylesheet" type="text/css">';
	}

	echo			'</head>
					<body id="thepage">';
	echo 				$body;
	echo '      </body>
			</html>';
	exit();
}

function theme_colours() {
	$info = $GLOBALS['colour_schemes'][setting_fetch('dabr_colours', 0)];
	list(, $bits) = explode('|', $info);
	$colours = explode(',', $bits);
	return (object) array(
		'links'           => trim($colours[0]),
		'body_background' => trim($colours[1]),
		'body_text'       => trim($colours[2]),
		'small'           => trim($colours[3]),
		'odd'             => trim($colours[4]),
		'even'            => trim($colours[5]),
		'replyodd'        => trim($colours[6]),
		'replyeven'       => trim($colours[7]),
		'menu_background' => trim($colours[8]),
		'menu_text'       => trim($colours[9]),
		'menu_link'       => trim($colours[10]),
	);
}

function theme_profile_form($user){
	// Profile form
	$out .= "
				<form name='profile' action='account' method='post' enctype='multipart/form-data'>
				    <hr />"._(PROFILE_NAME).":     <input name='name' maxlength='20' value='"                 . htmlspecialchars($user->name, ENT_QUOTES) ."' />
				    <br />"._(PROFILE_AVATAR).":   <img src='".theme_get_avatar($user)."' /> <input type='file' name='image' />
				    <br />"._(PROFILE_BIO).":      <textarea name='description' cols=40 rows=6 maxlength=160>". htmlspecialchars($user->description, ENT_QUOTES)."</textarea>
				    <br />"._(PROFILE_LINK).":     <input name='url' type='url' size=40 value='"              . htmlspecialchars($user->entities->url->urls[0]->expanded_url, ENT_QUOTES) ."' />
				    <br />"._(PROFILE_LOCATION).": <input name='location' maxlength='30' value='"             . htmlspecialchars($user->location, ENT_QUOTES) ."' />
				    <br /><input type='submit' value='Update Profile' />
				</form>";
	return $out;
}
function theme_directs_menu() {
	return '<p>'.
	       	'<a href="messages/create">'._(DIRECTS_CREATE).'</a> | '.
	       	'<a href="messages/inbox">' ._(DIRECTS_INBOX) .'</a> | '.
				'<a href="messages/sent">'  ._(DIRECTS_SENT)  .'</a>'.
			'</p>';
}

function theme_directs_form($to) {
	if ($to) {
		$friendship = friendship($to);
		$messaging = $friendship->relationship->source->can_dm;
		if (!$messaging)
		{
			$html_to = "<em>"._(DIRECTS_WARNING)."</em> ". sprintf(_(DIRECTS_WARNING_TEXT),$to) .
			           "<br/>";
		}
		$html_to .= sprintf(_(DIRECTS_SENDING_TO),$to) . "<input name='to' value='$to' type='hidden'>";
	} else {
		$html_to .= _(DIRECTS_TO).": {$to}<input name='to'><br />"._(DIRECTS_MESSAGE).":";
	}
	$content = '<form method="post" action="messages/send" enctype="multipart/form-data">'.$html_to.'
						<br>
						<textarea name="message"
							style="width:90%;
							max-width: 400px;"
							rows="10"
							maxlength="10000"
							id="message"></textarea>
						<br>
						<input type="submit" value="'._(DIRECTS_BUTTON).'" />
	            </form>';
	return $content;
}

function theme_status_form($text = '', $in_reply_to_id = null) {

	if (user_is_authenticated()) {
		$icon = "";//"images/twitter-bird-16x16.png";

		//	adding ?status=foo will automatically add "foo" to the text area.
		if ($_GET['status'])
		{
			$text = $_GET['status'];
		}

		if ('yes' == setting_fetch('dabr_show_icons','yes'))
		{
			$camera = "📷";
		} else {
			$camera = _(ICONS_CAMERA);
		}

      $output = '
        <form method="post" action="update" enctype="multipart/form-data">
            <fieldset>
                <legend><span class="icons" id="twitterbird">'.$icon.'</span>'._(STATUS_BOX).'</legend>
                <textarea id="status" name="status" rows="4" class="statusbox">'.$text.'</textarea>
                <div>
                    <input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" />
                    <input type="submit" value="'._(SEND_BUTTON).'" />
                    <span id="status-remaining">140</span>
                    <span id="geo" style="display: none;">
                        <input onclick="goGeo()" type="checkbox" id="geoloc" name="location" />
                        <label for="geoloc" id="lblGeo"></label>
                    </span>
                </div>
                <span class="icons" style="float:right;">'.$camera.'</span>
                <div class="fileinputs">
					<input type="file" accept="image/*,video/mp4" name="image" class="file" />
				</div>
            </fieldset>
            <script type="text/javascript">
                started = false;
                chkbox = document.getElementById("geoloc");
                if (navigator.geolocation) {
                    geoStatus("'._(SHARE_MY_LOCATION).'");
                    if ("'.$_COOKIE['geo'].'"=="Y") {
                        chkbox.checked = true;
                        goGeo();
                    }
                }
                function goGeo(node) {
                    if (started) return;
                    started = true;
                    geoStatus("'._(LOCATING).'");
                    navigator.geolocation.getCurrentPosition(geoSuccess, geoStatus , { enableHighAccuracy: true });
                }
                function geoStatus(msg) {
                    document.getElementById("geo").style.display = "inline";
                    document.getElementById("lblGeo").innerHTML = msg;
                }
                function geoSuccess(position) {
                    geoStatus("<a href=\'https://maps.google.co.uk/m?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>'.
						  					_(SHARE_MY_LOCATION).
										'</a>");
                    chkbox.value = position.coords.latitude + "," + position.coords.longitude;
                }
            </script>
        </form>';
        $output .= js_counter('status');
        return $output;
	}
}

function theme_status($status) {
	//32bit int / snowflake patch
	if($status->id_str) $status->id = $status->id_str;

	$feed[] = $status;
	$tl = twitter_standard_timeline($feed, 'status');
	$content = theme('timeline', $tl);
	return $content;
}

function theme_retweet($status)
{
	$text = "RT @{$status->user->screen_name}: {$status->text}";
	$screen_name = $status->user->screen_name;
	$id = $status->id_str;
	$length = function_exists('mb_strlen') ? mb_strlen($text,'UTF-8') : strlen($text);
	$from = substr($_SERVER['HTTP_REFERER'], strlen(BASE_URL));

	if($status->user->protected == 0)
	{
		$content.="<p>"._(RETWEET_TWITTER).":</p>
					<form action='twitter-retweet/{$status->id_str}' method='post'>
						<input type='hidden' name='from' value='$from' />
						<input type='submit' value='"._(RETWEET_TWITTER)."' />
					</form>
					<hr />";

		$content .= "<p>"._(RETWEET_COMMENT).":</p>
					<form action='update' method='post'>
						<input type='hidden' name='from' value='{$from}' />
						<input type='hidden' name='in_reply_to_id' value='{$status->id_str}' />
						<textarea name='status'
							style='width:90%;
							max-width: 400px;'
							rows='5'
							id='status'>&nbsp;\nhttps://twitter.com/{$screen_name}/status/{$id}</textarea>
						<br/>
						<input type='submit' value='"._(RETWEET_COMMENT)."' />
						<span id='status-remaining'>" . (140 - $length) ."</span>
					</form>
					<hr />";
		$content .= js_counter("status");
	}
	else
	{
		$content.="<p>" . sprintf(_(RETWEET_FORBIDDEN),$status->user->screen_name) . "</p>";
	}

	$content .= "<p>"._(RETWEET_EDIT).":</p>
					<form action='update' method='post'>
						<input type='hidden' name='from' value='{$from}' />
						<input type='hidden' name='in_reply_to_id' value='{$status->id_str}' />
						<textarea name='status'
							style='width:90%;
							max-width: 400px;'
							rows='5'
							id='edit'>{$text}</textarea>
						<br/>
						<input type='submit' value='"._(RETWEET_EDIT_BUTTON)."' />
						<span id='edit-remaining'>" . (140 - $length) ."</span>
					</form>";
	$content .= js_counter("edit");

	return $content;
}
function theme_user_header($user) {
	$friendship = friendship($user->screen_name);

	$followed_by = $friendship->relationship->target->followed_by; //The $user is followed by the authenticating
	$following = $friendship->relationship->target->following;
	$name = theme('full_name', $user);
	$screen_name = $user->screen_name;
	$full_avatar = theme_get_full_avatar($user);
	$link = twitter_parse_tags($user->url, $user->entities->url, "me");
	//Some locations have a prefix which should be removed (UberTwitter and iPhone)
	$cleanLocation = urlencode(str_replace(array("iPhone: ","ÜT: "),"",$user->location));
	$raw_date_joined = strtotime($user->created_at);
	$date_joined = date('jS M Y', $raw_date_joined);
	$tweets_per_day = twitter_tweets_per_day($user);
	$bio = twitter_parse_tags($user->description, $user->entities->description);

	$out = "<div class='profile'>
	            <span class='avatar'>".theme('external_link', $full_avatar, theme('avatar', theme_get_avatar($user)))."</span>
	            <span class='status shift'><b>{$name}</b><br/>
	               <span class='about'>";

	if ($user->protected == true) {
		$out .=       '<strong>'._(PRIVATE_TWEETS).'</strong><br />';
	}

	$out .=				_(PROFILE_BIO). ": {$bio}<br />".
                     _(PROFILE_LINK).": {$link}<br />
                     <span class='icons'>⌖</span>
								<a href=\"https://maps.google.com/maps?q={$cleanLocation}\" target=\"" . get_target() . "\">
                           {$user->location}
                        </a>
               			<br />".
								_(PROFILE_JOINED).": {$date_joined} (" .
									sprintf(ngettext("PROFILE_TWEET_PER_DAY %s",
									                 "PROFILE_TWEETS_PER_DAY %s",
														  $tweets_per_day),
														  number_format($tweets_per_day)).
 								")
            			</span>
            		</span>
   					<div class='features'>";
	$out .= theme_user_info($user);
	$out .= "</div>
			</div>";
	return $out;
}

function theme_user_info($user) {
	$screen_name = $user->screen_name;
	if ($user->following == false) {
		$out = "<div class='button-div'><a class='button' href='.follow/{$screen_name}'>"._(FOLLOW)."</a></div>";
	}
	else {
		$out = "<div class='button-div'><a class='button' href='.unfollow/{$screen_name}'>"._(UNFOLLOW)."</a></div>";
	}
	$out .= "<div>" . _(INFO) . ": ";

	$out .= sprintf(ngettext("PROFILE_COUNT_TWEET %s", "PROFILE_COUNT_TWEETS %s", $user->statuses_count), number_format($user->statuses_count));

	//	If the authenticated user is not following the protected user,
	//	the API will return a 401 error when trying to view friends, followers and favourites
	//	This is not the case on the Twitter website
	//	To avoid the user being logged out, check to see if she is following the protected user.
	//	If not, don't create links to friends, followers and favourites
	if ($user->protected == true && $followed_by == false) {
		$out .= " | " . sprintf(ngettext("PROFILE_COUNT_FOLLOWER %s", "PROFILE_COUNT_FOLLOWERS %s", $user->followers_count), number_format($user->followers_count));
		$out .= " | " . sprintf(ngettext("PROFILE_COUNT_FRIEND %s",   "PROFILE_COUNT_FRIENDS %s",   $user->friends_count),   number_format($user->friends_count));
		$out .= " | " . sprintf(ngettext("PROFILE_COUNT_FAVOURITE %s","PROFILE_COUNT_FAVOURITES %s",$user->favourites_count),number_format($user->favourites_count));
	}
	else {
		$out .= " | <a href='followers/{$screen_name}'>" .
							sprintf(ngettext("PROFILE_COUNT_FOLLOWER %s", "PROFILE_COUNT_FOLLOWERS %s", $user->followers_count), number_format($user->followers_count)) .
						"</a>";
		$out .= " | <a href='friends/{$screen_name}'>"   .
							sprintf(ngettext("PROFILE_COUNT_FRIEND %s",   "PROFILE_COUNT_FRIENDS %s",   $user->friends_count),   number_format($user->friends_count)) .
						"</a>";
		$out .= " | <a href='favourites/{$screen_name}'>".
							sprintf(ngettext("PROFILE_COUNT_FAVOURITE %s","PROFILE_COUNT_FAVOURITES %s",$user->favourites_count),number_format($user->favourites_count)) .
						"</a>";
	}
	$out .=     " | <a href='lists/{$screen_name}'>" .
							sprintf(ngettext("PROFILE_COUNT_LIST %s",     "PROFILE_COUNT_LISTS %s",     $user->listed_count),    number_format($user->listed_count)) .
						"</a>";

	//	Blocking and Muting are not always returned. Here's the hacky way to get it.
	if ($user->muting === null)
	{
		$friendship = friendship($user->screen_name);
		$muting    = $friendship->relationship->source->muting;
		$blocking  = $friendship->relationship->source->blocking;
		$messaging = $friendship->relationship->source->can_dm;
		$retweets  = $friendship->relationship->source->want_retweets;
	} else {
		$muting    = $user->muting;
		$blocking  = $user->blocking;
		$messaging = false; //$user->following;	//	Is the authenticated user being followed by the listed user.
		$retweets  = true; //	Can assume that Retweets haven't been hidden?
	}

	if($muting)
	{
		$out .= " | <a href='.confirm/.unmute/{$screen_name}'>".
							_(UNMUTE) .
						"</a>";
	} else {
		$out .= " | <a href='.confirm/.mute/{$screen_name}'>".
							_(MUTE) .
						"</a>";
	}

	if($blocking == true)
	{
		$out .= " | <a href='.confirm/.unblock/{$screen_name}'>".
							_(UNBLOCK) .
						"</a>";
	} else {
		$out .= " | <a href='.confirm/.block/{$screen_name}'>".
							_(BLOCK) .
						"</a>";
	}

	if($messaging == true)
	{
		$out .=	" | <a href='messages/create/{$screen_name}'>"._(DIRECTS_BUTTON)."</a>";
	}

	//	One cannot follow, block, nor report spam oneself.
	if (strtolower($screen_name) !== strtolower(user_current_username())) {

		if ($user->following == true) {
			if($retweets) {
				$out .= " | <a href='.confirm/.hideRetweets/{$screen_name}'>"._(RETWEETS_HIDE)."</a>";
			}
			else {
				$out .= " | <a href='.confirm/.showRetweets/{$screen_name}'>"._(RETWEETS_SHOW)."</a>";
			}
		}

		$out .= " | <a href='.confirm/.spam/{$user->screen_name}/{$user->id}'>"._(REPORT_SPAM)."</a>";
	} else {
		//	Items we can only show on ourself
		$out .= " | <a href='blocked-list'>"._(BLOCK_SHOW)."</a>";
	}

	$out .= " | <a href='search?query=%40{$screen_name}'>".sprintf(_(SEARCH_AT),$user->screen_name)."</a>";
	$out .= "</div>";
	return $out;
}

function theme_avatar($url, $force_large = false) {
	$size = 48;	//$force_large ? 48 : 24;
	return "<img src='$url' height='$size' width='$size' />";
}

function theme_status_time_link($status, $is_link = true) {
	$time = strtotime($status->created_at);
	if ($time > 0) {
		if (twitter_date('dmy') == twitter_date('dmy', $time) && !setting_fetch('dabr_timestamp')) {
			$out = format_interval(time() - $time);
			// $out = sprintf(_(SECONDS), time() - $time);
		} else {
			$out = twitter_date('H:i', $time);
		}
	} else {
		$out = $status->created_at;
	}
	if ($is_link)
		$out = "<a href='status/{$status->id}' class='time'>$out</a>";
	return $out;
}

function theme_timeline($feed, $paginate = true) {
	if (count($feed) == 0) return theme('no_tweets');
	if (count($feed) < 2) {
		$hide_pagination = true;
	}
	$rows = array();
	$page = menu_current_page();
	$date_heading = false;
	$first=0;

	// Add the hyperlinks *BEFORE* adding images
	foreach ($feed as &$status)	{
		$status->text = twitter_parse_tags($status->text, $status->entities);
	}

	unset($status);

	// Only embed images if user hasn't hidden them

	if(setting_fetch('dabr_show_oembed')) {
		oembed_embed_thumbnails($feed);
	}

	foreach ($feed as $status) {
		if ($first==0) {
			$since_id = $status->id;
			$first++;
		}
		else {
			$max_id =  $status->id;
			if ($status->original_id) {
				$max_id =  $status->original_id;
			}
		}
		$time = strtotime($status->created_at);
		if ($time > 0) {
			$date = twitter_date('l jS F Y', strtotime($status->created_at));
			if ($date_heading !== $date) {
				$date_heading = $date;
				$rows[] = array('data'  => array($date), 'class' => 'date');
			}
		}
		else {
			$date = $status->created_at;
		}

		if ($status->retweeted_by) {
			$retweeted_by = $status->retweeted_by->user->screen_name;
			$retweeted = "<br />
				<small>
					<a href='{$retweeted_by}'>".sprintf(_('RETWEETED_BY %s'),$retweeted_by)."</a>
				</small>";
		} else {
			$retweeted = "";
		}

		$text = $status->text;
		if ("yes" != setting_fetch('dabr_hide_inline')) {
			$media = twitter_get_media($status);

			if ($media != "")
			{
				$media = "<br />{$media}";
			}
		}
		$link    = theme('status_time_link', $status, !$status->is_direct);
		$actions = theme('action_icons', $status);

		//	Add an Quoted Tweet
		if ($status->quoted_status != null) {
			$quoted = "<blockquote class='embedded-tweet'>" . theme('status', $status->quoted_status) . "</blockquote>";
		} else {
			$quoted = "";
		}

		if ("yes" != setting_fetch('dabr_hide_avatars')) {
			$avatar = theme('avatar', theme_get_avatar($status->from));
		}

		$source = "";

		if ($status->in_reply_to_status_id)	{
			$reply_screen_name = $status->in_reply_to_screen_name;
			$source .= "<a href='status/{$status->in_reply_to_status_id_str}'>" .
								sprintf(_(IN_REPLY_TO),$reply_screen_name) .
							"</a>. ";
		}

		if ($status->place->name) {
			$source .= " " . theme('action_icon',
											"https://maps.google.com/maps?q=" . urlencode("{$status->place->name},{$status->place->country}") ,
											"<span class='icons' title='location'>⌖</span> {$status->place->name}, {$status->place->country}.",
											'MAP');
		}

		//need to replace & in links with &amps and force new window on links
		if ($status->source) {
			$source .= _(VIA).
			           str_replace('rel="nofollow"', 'target="' . get_target() . '"',
			           preg_replace('/&(?![a-z][a-z0-9]*;|#[0-9]+;|#x[0-9a-f]+;)/i', '&amp;', $status->source)) .
			           ".";
		}

		//	Build up the status to display
		$html = "<b>" . theme_full_name($status->from) . "</b>
		        {$link}
		        {$retweeted}
		        <br />
		        {$text}
		        {$media}
		        {$quoted}
		        {$actions}
		        <span class='from'>{$source}</span>";

		unset($row);
		$class = 'status';

		if ($avatar)	{
			$row[] = array('data' => $avatar, 'class' => 'avatar');
			$class .= ' shift';
		}

		$row[] = array('data' => $html, 'class' => $class);

		$class = 'tweet';
		if ($page != 'replies' && twitter_is_reply($status)) {
			$class .= ' reply';
		}
		$row = array('data' => $row, 'class' => $class);

		$rows[] = $row;
	}
	$content = theme('table', array(), $rows, array('class' => 'timeline'));

	if(!$hide_pagination) {
		if($paginate) {
			if($page == 'some-unknown-method-which-doesnt-take-max_id') {
				$content .= theme('pagination');
			}
			else {
				if(is_64bit()) $max_id = intval($max_id) - 1; //stops last tweet appearing as first tweet on next page
				$content .= theme('pagination', $max_id);
			}
		}
	}

	return $content;
}

function theme_full_name($user) {

	//	Link to the screen name but display as "Ms E Xample (@Example"
	if ($user->name == $user->screen_name || null == $user->name)
	{
		$name = "@<a href='{$user->screen_name}'>{$user->screen_name}</a>";
	} else  {
		$name = "<a href='{$user->screen_name}'>{$user->name} (@{$user->screen_name})</a>";
	}

	//	Add the veified tick
	if($user->verified)
	{
		$name .= " " . theme('action_icon', "", '✔', _(VERIFIED));
	}

	return $name;
}

// http://groups.google.com/group/twitter-development-talk/browse_thread/thread/50fd4d953e5b5229#
function theme_get_avatar($object) {
	if ($_SERVER['HTTPS'] == "on" || (0 == strpos(BASE_URL, "https://"))) {
		return image_proxy($object->profile_image_url_https, "48/48/");
	}
	else {
		return image_proxy($object->profile_image_url, "48/48/");
	}
}

function theme_get_full_avatar($object) {
	//	Strip off the "_normal" from the image name to get full sized.

	if ($_SERVER['HTTPS'] == "on" || (0 == strpos(BASE_URL, "https://"))) {
		return image_proxy(str_replace('_normal.', '.', $object->profile_image_url_https));
	}
	else {
		return image_proxy(str_replace('_normal.', '.', $object->profile_image_url));
	}
}

function theme_no_tweets() {
	return "<p>"._(NO_TWEETS)."</p>";
}

function theme_search_results($feed) {
	$rows = array();
	foreach ($feed->results as $status) {
		$text = twitter_parse_tags($status->text, $status->entities);
		$link = theme('status_time_link', $status);
		$actions = theme('action_icons', $status);

		$row = array(
		theme('avatar', theme_get_avatar($status)), "<a href='{$status->from_user}'>{$status->from_user}</a> $actions - {$link}<br />{$text}",);
		if (twitter_is_reply($status)) {
			$row = array('class' => 'reply', 'data' => $row);
		}
		$rows[] = $row;
	}
	$content = theme('table', array(), $rows, array('class' => 'timeline'));
	$content .= theme('pagination');
	return $content;
}

function theme_search_form($search_query, $saved) {
	$query = stripslashes(htmlentities($search_query,ENT_QUOTES,"UTF-8"));

	//	The basic search box
	$search_form_html = '
	<form action="search" method="get">
	   <span class="icons">🔍</span>
	   <input type="search" name="query" value="'. $query .'" />
		<input type="submit" value="'._(SEARCH_BUTTON).'" />
	</form>';

	//	A button for saving a new search
	$new_saved_search_html = "";
	if ($query !== "") {
		$new_saved_search_html .= '
			<form action="search/bookmark" method="post">
				<input type="hidden" name="query" value="'.$query.'" />
				<input type="submit" value="'.sprintf(_(SEARCH_SAVE),$query).'" />
			</form>';
	}

	//	A list of all the saved searches
	$saved_searches_html = "<div>"._(SAVED_SEARCHES).": ";

	//	If there are no saved searches, don't display any.
	$show_saved_searches = false;

	foreach ($saved as &$saved_search)	{
		$saved_display = $saved_search->name;
		$saved_query = urlencode($saved_search->query);
		$saved_id = $saved_search->id_str;
		$saved_searches_html .= "<a href='search?query={$saved_query}'>{$saved_display}</a> ";

		//	Add a delete icon
		$saved_searches_html .= theme('action_icon', ".confirm/.deleteSavedSearch/{$saved_id}", '🗑', '['._(DELETE_BUTTON).']');
		$saved_searches_html .= " | ";

		// Remove the Save New Search button if the term was already found
		if ($query == $saved_search->query)
		{
			$new_saved_search_html = "";
		}

		//	If there are saved searches
		$show_saved_searches = true;
	}

	if ($show_saved_searches)
	{
		$saved_searches_html .= "</div>";
	} else {
		$saved_searches_html = "";
	}

	return $search_form_html . $new_saved_search_html . $saved_searches_html;
}

function theme_external_link($url, $content = null) {
	// //Long URL functionality.  Also uncomment function long_url($shortURL)
	// if (!$content)
	// {
	// 	//Used to wordwrap long URLs
	// 	//return "<a href='$url' target='_blank'>". wordwrap(long_url($url), 64, "\n", true) ."</a>";
	// 	return "<a href='$url' target='" . get_target() . "'>". long_url($url) ."</a>";
	// }
	// else
	// {
		return "<a href='$url' target='" . get_target() . "'>$content</a>";
	// }

}

function theme_pagination($max_id = false) {
	$page = intval($_GET['page']);
	if (preg_match('#&q(.*)#', $_SERVER['QUERY_STRING'], $matches))	{
		//	Need to replace the first & with &amp; for compliance
		$query = htmlspecialchars($matches[0]);
	}
	if($max_id) {
		$links[] = "<a href='{$_GET['q']}?max_id=".$max_id."{$query}' class='button'>"._(LINK_OLDER)."</a>";
	}
	else {
		if ($page == 0) $page = 1;
		$links[] = "<a href='{$_GET['q']}?page=".($page+1)."{$query}' class='button'>"._(LINK_OLDER)."</a>";
		if ($page > 1) $links[] = "<a href='{$_GET['q']}?page=".($page-1)."{$query}' class='button'>"._(LINK_NEWER)."</a> ";
	}

	$links[] =  theme('menu_bottom_button');
	$links[] = "<a href='{$_GET['q']}?{$query}' class='button'>"._(LINK_FIRST)."</a>";
	if (count($links) > 0) return '<div class="bottom">'.implode(' ', $links).'</div>';
}

function theme_action_icons($status) {
	$id           = $status->id;
	$from         = $status->from->screen_name;
	$retweeted_by = $status->retweeted_by->user->screen_name;
	$retweeted_id = $status->retweeted_by->id;
	$geo          = $status->geo;
	$actions = array();

	if (!$status->is_direct) {
		$actions[] = theme('action_icon', "{$from}/reply/{$status->id}", '@', _(REPLY));
	}

	//	DM only shows up if we can actually send a DM
	if (!user_is_current_user($from)) {
		$actions[] = theme('action_icon', "messages/create/{$from}", '✉', _(DIRECT_MESSAGE));
	}
	if (!$status->is_direct) {

		$favourite_count = "";
		//	Display favourite count
		if($status->favorite_count) {
			$favourite_count = "<sup>" . number_format($status->favorite_count) . "</sup>";
		}

		if ($status->favorited == '1') {
			$actions[] = theme('action_icon', ".unfavourite/{$id}", '<span style="color:#FF0000;">♥</span>', _(UNFAVOURITE)) . $favourite_count;
		} else {
			$actions[] = theme('action_icon', ".favourite/{$id}", '♡', _(FAVOURITE)) . $favourite_count;
		}

		$retweet_count = "";
		//	Display number of RT
		if ($status->retweet_count)	{
			$retweet_count = "<sup>" .
			                    theme('action_icon',
									        ".retweeted-by/{$id}",
											  number_format($status->retweet_count),
											  number_format($status->retweet_count)) .
			                "</sup>";
		}

		// Show a diffrent retweet icon to indicate to the user this is an RT
		if ($status->retweeted || user_is_current_user($retweeted_by)) {
			$actions[] = theme('action_icon', ".retweet/{$id}", '<span style="color:#009933;">♻</span>', _(RETWEET)) . $retweet_count;
		}
		else {
			$actions[] = theme('action_icon', ".retweet/{$id}", '♻', _(RETWEET)) . $retweet_count;
		}


		if (user_is_current_user($from)) {
			$actions[] = theme('action_icon', ".confirm/.delete/{$id}", '🗑', _(DELETE_BUTTON));
		}

		//Allow users to delete what they have retweeted
		if (user_is_current_user($retweeted_by)) {
			$actions[] = theme('action_icon', ".confirm/.delete/{$retweeted_id}", '🗑', _(DELETE_BUTTON));
		}

	}
	else {
		$actions[] = theme('action_icon', ".confirm/.deleteDM/{$id}", '🗑', _(DELETE_BUTTON));
	}
	if ($geo !== null) {
		$latlong = $geo->coordinates;
		$lat = $latlong[0];
		$long = $latlong[1];
		$actions[] = theme('action_icon', "https://maps.google.com/maps?q={$lat},{$long}", '⌖', _(PROFILE_LOCATION));
	}
	//Search for @ to a user
	$actions[] = theme('action_icon',"search?query=%40{$from}",'🔍',_(SEARCH_BUTTON));

	return '<span class="actionicons">' . implode('&emsp;', $actions) . '</span>';
}

function theme_action_icon($url, $display, $text) {

	//	If the user doesn't want icons, display as text
	if ('yes' !== setting_fetch('dabr_show_icons','yes'))
	{
		$display = $text;
		$class = "action-text";
	} else {
		$class = "action";
	}

	// Maps open in a new tab
	if ($text == _(PROFILE_LOCATION))
	{
		return "<a href='$url' target='" . get_target() . "' class='{$class}'>{$display}</a>";
	}

	//	Verified ticks & RT notifications don't need to be linked
	if (_(VERIFIED) == $text || _(RETWEETED) == $text)
	{
		return "<span class='{$class}' title='{$text}'>{$display}</span>";
	}

    return "<a href='{$url}' class='{$class}' title='{$text}'>{$display}</a>";

}
function theme_users_list($feed, $hide_pagination = false) {
	if(isset($feed->users))
		$users = $feed->users;
	else
		$users = $feed;
	$rows = array();
	if (count($users) == 0 || $users == '[]') return '<p>'._(NO_USERS_FOUND).'</p>';

	foreach($users as $user) {
		$content = "";
		if($user->user) $user = $user->user;
		$name = theme('full_name', $user);
		$tweets_per_day = twitter_tweets_per_day($user);
		$last_tweet = strtotime($user->status->created_at);
		$content = "{$name}<br />";
		$content .= "<span class='about'>";
		if($user->description != "") {
			$content .= _(PROFILE_BIO).": " . twitter_parse_tags($user->description, $user->entities->description) . "<br />";
		}
		if($user->location != "") {
			$content .= theme('action_icon',
			                  "https://maps.google.com/maps?q=" . urlencode($user->location),
									"<span class='icons'>⌖</span> {$user->location}",
									_(PROFILE_LOCATION));
			$content .= "<br />";
		}

		$content .= theme_user_info($user);

		if($user->status->created_at) {
			$content .= " " . _(LAST_TWEET) . ": ";
			if($user->protected == 'true' && $last_tweet == 0)
				$content .= _(LAST_TWEET_PRIVATE);
			else if($last_tweet == 0)
				$content .= _(LAST_TWEET_NEVER);
			else
				$content .= twitter_date('l jS F Y', $last_tweet);
		}
		$content .= "</span>";

		$rows[] = array('data' => array(array('data' => theme('avatar', theme_get_avatar($user)), 'class' => 'avatar'),
		                                array('data' => $content, 'class' => 'status shift')),
		                'class' => 'tweet');
	}

	$content = theme('table', array(), $rows, array('class' => 'followers'));
	if (!$hide_pagination)
		#$content .= theme('pagination');
		$content .= theme('list_pagination', $feed);
	return $content;
}

function theme_list_pagination($json) {
	if ($cursor = (string) $json->next_cursor) {
		$links[] = "<a href='{$_GET['q']}?cursor={$cursor}' class='button'>"._(LINK_OLDER)."</a>";
	}

	$links[] = theme('menu_bottom_button');

	if ($cursor = (string) $json->previous_cursor) {
		//	Codebird needs a +ve cursor, but returns a -ve one?
		if (0 === strpos($cursor, "-"))
		{
			//	TODO FIXME still doesn't go back to first screen?
			$cursor = trim($cursor,"-");
		}
		$links[] = "<a href='{$_GET['q']}?cursor={$cursor}' class='button'>"._(LINK_NEWER)."</a>";
	}
	if (count($links) > 0) return '<div class="bottom">'.implode(' ', $links).'</div>';
}

function theme_confirmation_message($message){
	return "<p>
				<span class='avatar'>
					<img src='i/images/dabr.png' width='48' height='48' />
				</span>
				<span class='status shift'>{$message}</span>
			</p>";
}

function theme_trends_page($locales, $trends) {
	// TODO FIXME
}

function theme_radio($options, $name, $selected = NULL)
{
	if (count($options) == 0) return '';
	$output = '';
	foreach($options as $value => $description)
	{
		if ($name == "dabr_fonts")
		{
			$style = "style='font-family:". urldecode($value) . ", sans;'";
			$output .= '<label for="'.$value.'" '.$style.'>
							<input
								type="radio"
								name="' .$name. '"
								id="'   .$value.'"
								value="'.$description. '" '.
								($selected == $description ? 'checked="checked"' : '').
							' />';
			$output .= ' ' . $description . '</label>
			<br>';
		} elseif ($name == "dabr_font_size") {
			$style = "style='font-size:". $value . "em;'";
			$output .= '<label for="'.$value.'" '.$style.'>
							<input
								type="radio"
								name="' .$name. '"
								id="'   .$value.'"
								value="'.$value. '" '.
								($selected == $value ? 'checked="checked"' : '').
							' />';
			$output .= ' ' . $description . '</label>
			<br>';
		}

	}
	return $output;
}

function theme_check($options, $name, $selected = NULL)
{
	if (count($options) == 0) return '';
	$output = '';
	foreach($options as $value => $description)
	{

		$output .= '<p>
		               <label>
		                  <input type="checkbox"
								       name="'.$name.'"
										 value="'.$value.'"
										 '.($selected == $value ? 'checked="checked"' : '').' />';
								$description.
							'</label>
		            </p>';
	}
	return $output;
}

function theme_options($options, $selected = NULL, $title = NULL, $select_name = NULL)
{
	if (count($options) == 0) return '';
	$output = "<p>{$title}
		        		<br />
		 				<select name=\"{$select_name}\">'";

	foreach($options as $value => $name) {
		if (is_array($name)) {
			$output .= '<optgroup label="'.$value.'">';
			$output .= theme('options', $name, $selected);
			$output .= '</optgroup>';
		} else {
			$output .= '<option value="'.$value.'"'.($selected == $value ? ' selected="selected"' : '').'>'.$name."</option>\n";
		}
	}
	$output .= "	</select>
	      	</p>";

	return $output;
}
