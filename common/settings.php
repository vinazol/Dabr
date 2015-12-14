<?php

/*
Assembled in css.php
Syntax is
         'Name|          links, body_background,body_text,small, odd,   even,  replyodd,replyeven,menu_background,menu_text,menu_link',
*/

$GLOBALS['colour_schemes'] = array(
	//   'Name|          links, body_background,body_text,small, odd,   even,  replyodd,replyeven,menu_background,menu_text,menu_link',
	0 => 'Pretty In Pink|c06,   fcd,            623,      623,   fee,   fde,   ffa,     dd9,      c06,            fee,      fee',
	1 => 'Ugly Orange|   b50,   ddd,            111,      555,   fff,   eee,   ffa,     dd9,      e81,            c40,      fff',
	2 => 'Touch Blue|    138,   ddd,            111,      313460,fff,   eee,   ffa,     dd9,      138,            fff,      fff',
	3 => 'Sickly Green|  293C03,ccc,            000,      555,   fff,   eee,   CCE691,  ACC671,   495C23,         919C35,   fff',
	4 => 'Night Mode|    d5d,   000,            ddd,      B7A3A3,222,   111,   202,     101,      909,            222,      000',
	5 => '#red|          d12,   ddd,            111,      555,   fff,   eee,   ffa,     dd9,      c12,            fff,      fff',
	6 => 'Mellow Yellow| 0049DA,FFFFCC,         333300,   333300,F5EFC0,EDE8B1,CCFF99,  99FF99,   FFFFCC,         003300,   003300',
	7 => 'Work Safe|     000,   fff,            000,      555,   fff,   eee,   fff,     eee,      555,            fff,      fff',
	//   'Name|          links, body_background,body_text,small, odd,   even,  replyodd,replyeven,menu_background,menu_text,menu_link',
);

menu_register(array(
	'settings' => array(
		'callback' => 'settings_page',
		'display' => '⚙'
	),
	'reset' => array(
		'hidden' => true,
		'callback' => 'cookie_monster',
	),
));

function cookie_monster() {
	//	Delete Cookies
	$cookies = array(
		'settings',
		'utc_offset',
		'search_favourite',
		'perPage',
		'imageSize',
		'USER_AUTH',
	);
	$duration = time() - 3600;
	foreach ($cookies as $cookie) {
		setcookie($cookie, null, $duration, '/');
		setcookie($cookie, null, $duration);
	}

	setting_clear_session_oauth();

	return theme('page', _(COOKIE_MONSTER), '<p>'._(COOKIE_MONSTER_DONE).'</p>');
}

function setting_clear_session_oauth() {
	//	Reset OAuth data
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);
	unset($_SESSION['oauth_verify']);
}

function setting_fetch($setting, $default = null) {
	$settings = (array) unserialize(base64_decode($_COOKIE['settings']));
	if (array_key_exists($setting, $settings)) {
		return $settings[$setting];
	} else {
		return $default;
	}
}

function setcookie_year($name, $value) {
	$duration = time() + (3600 * 24 * 365);
	setcookie($name, $value, $duration, '/');
}

function settings_page($args) {
	if ($args[1] == 'save') {
		$settings['perPage']      = $_POST['perPage'];
		$settings['gwt']          = $_POST['gwt'];
		$settings['colours']      = $_POST['colours'];
		$settings['reverse']      = $_POST['reverse'];
		$settings['timestamp']    = $_POST['timestamp'];
		$settings['hide_inline']  = $_POST['hide_inline'];
		$settings['show_oembed']  = $_POST['show_oembed'];
		$settings['hide_avatars'] = $_POST['hide_avatars'];
		$settings['menu_icons']   = $_POST['menu_icons'];
		$settings['image_size']   = $_POST['image_size'];
		$settings['utc_offset']   = (float)$_POST['utc_offset'];

		setcookie_year('settings', base64_encode(serialize($settings)));
		twitter_refresh('');
	}

	$perPage = array(
		  '5'	=> sprintf(_(SETTINGS_TWEETS_PER_PAGE),5),
		 '10'	=> sprintf(_(SETTINGS_TWEETS_PER_PAGE),10),
		 '20'	=> sprintf(_(SETTINGS_TWEETS_PER_PAGE),20),
		 '30'	=> sprintf(_(SETTINGS_TWEETS_PER_PAGE),30),
		 '40'	=> sprintf(_(SETTINGS_TWEETS_PER_PAGE),40),
		 '50'	=> sprintf(_(SETTINGS_TWEETS_PER_PAGE),50),
		'100' => sprintf(_(SETTINGS_TWEETS_PER_PAGE),100) ." ". _(SETTINGS_SLOW_1),
		'150' => sprintf(_(SETTINGS_TWEETS_PER_PAGE),150) ." ". _(SETTINGS_SLOW_2),
		'200' => sprintf(_(SETTINGS_TWEETS_PER_PAGE),200) ." ". _(SETTINGS_SLOW_3),
	);

	$image_size = array(
		'thumb'	=>  _(SETTINGS_IMAGE_THUMB),
		'small'	=>  _(SETTINGS_IMAGE_SMALL),
		'medium' =>  _(SETTINGS_IMAGE_MEDIUM),
		'large'	=>  _(SETTINGS_IMAGE_LARGE),
		'orig'	=>  _(SETTINGS_IMAGE_ORIGINAL)
	);

	$colour_schemes = array();
	foreach ($GLOBALS['colour_schemes'] as $id => $info) {
		list($name) = explode('|', $info);
		$colour_schemes[$id] = $name;
	}

	$utc_offset = setting_fetch('utc_offset', 0);
/* returning 401 as it calls http://api.twitter.com/1/users/show.json?screen_name= (no username???)
	if (!$utc_offset) {
		$user = twitter_user_info();
		$utc_offset = $user->utc_offset;
	}
*/
	if ($utc_offset > 0) {
		$utc_offset = '+' . $utc_offset;
	}

	$content = '';
	$content .= '<form action="settings/save" method="post">
	                <p>'._(SETTINGS_COLOUR).'
	                    <br />
	                    <select name="colours">';
	$content .= theme('options', $colour_schemes, setting_fetch('colours', 0));
	$content .=         '</select>
	                </p>';


	$content .=     '<p>'._(SETTINGS_PER_PAGE).'
                        <br />
                        <select name="perPage">';
	$content .=             theme('options', $perPage, setting_fetch('perPage', 20));
	$content .=         '</select>
	                    <br/>
	                </p>';

	$content .=     '<p>'._(SETTINGS_IMAGE_SIZE).'
                        <br />
                        <select name="image_size">';
	$content .=             theme('options', $image_size, setting_fetch('image_size', "medium"));
	$content .=         '</select>
	                    <br/>
	                </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="gwt" value="on" '. (setting_fetch('gwt') == 'on' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_GWT_DETAIL) .
	               '</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="timestamp" value="yes" '. (setting_fetch('timestamp') == 'yes' ? ' checked="checked" ' : '') .' />'.
							sprintf(_(SETTINGS_TIMESTAMP), twitter_date('H:i')).
						'</label>
	            </p>';

	$content .= '<p>
	                <label>
	                    <input type="checkbox" name="hide_inline" value="yes" '. (setting_fetch('hide_inline') == 'yes' ? ' checked="checked" ' : '') .' />'.
							  _(SETTINGS_HIDE_INLINE).
	                '</label>
	            </p>';

	//	Hide oembeds by default. Keep things fast & save API calls.
	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="show_oembed" value="yes" '. (setting_fetch('show_oembed') == yes ? ' checked="checked" ' : '') .' />'
	                  ._(SETTINGS_SHOW_PREVIEW).
						'</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="hide_avatars" value="yes" '. (setting_fetch('hide_avatars') == 'yes' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_HIDE_AVATARS).
						'</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="menu_icons" value="yes" '. (setting_fetch('menu_icons') == 'yes' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_MENU_ICONS).
						'</label>
	            </p>';

	$content .= '<p>
						<label>'.sprintf(_(SETTINGS_TIMESTAMP_IS),gmdate('H:i')) .' ' .
							sprintf(_(SETTINGS_TIMESTAMP_DISPLAY), "<input type=\"text\" name=\"utc_offset\" value=\"{$utc_offset}\" size=\"3\" />", twitter_date('H:i')) .
							'<br />'._(SETTINGS_TIMESTAMP_ADJUST).'
						</label>
					</p>';

	$content .= '<p><input type="submit" value="'._(SETTINGS_SAVE_BUTTON).'" /></p></form>';

	$content .= '<hr /><p>'._(SETTINGS_RESET).'</p>';

	return theme('page', 'Settings', $content);
}
