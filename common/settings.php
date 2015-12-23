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
	8 => 'Simple|	      130f30,ccc,            130f30,   130f30,fff,   EEE,   FFA,     DD9,      ccc,            130f30,   130f30',
	//   'Name|          links, body_background,body_text,small, odd,   even,  replyodd,replyeven,menu_background,menu_text,menu_link',
);

$fonts = array( //ID => Value
					'Schoolbell:400'  => 'Schoolbell',
					'Raleway:500'     => 'Raleway',
					'Ubuntu+Mono:500' => 'Ubuntu Mono',
					'Karma:500'       => 'Karma',
					'Open+Sans:400'   => 'Open Sans',
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
		'dabr_settings',
		'dabr_perPage',
		'dabr_imageSize',
		'dabr_gwt',
		'dabr_colours',
		'dabr_timestamp',
		'dabr_show_inline',
		'dabr_show_oembed',
		'dabr_show_avatars',
		'dabr_show_icons',
		'dabr_float_menu',
		'dabr_image_size',
		'dabr_utc_offset',
		'dabr_fonts',
		'dabr_font_size',
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
	global $fonts;

	if (array_key_exists($setting, $settings)) {
		if("dabr_fonts" == $setting) {
			return array_search($settings[$setting], $fonts);
		}
		return $settings[$setting];
	} else {
		if("dabr_fonts" == $setting) {
			return array_search($default, $fonts);
		}
		return $default;
	}
}

function setcookie_year($name, $value) {
	$duration = time() + (3600 * 24 * 365);
	setcookie($name, $value, $duration, '/');
}

function settings_page($args) {
	if ($args[1] == 'save') {
		$settings['dabr_perPage']      = $_POST['dabr_perPage'];
		$settings['dabr_gwt']          = $_POST['dabr_gwt'];
		$settings['dabr_colours']      = $_POST['dabr_colours'];
		$settings['dabr_timestamp']    = $_POST['dabr_timestamp'];
		$settings['dabr_show_inline']  = $_POST['dabr_show_inline'];
		$settings['dabr_show_oembed']  = $_POST['dabr_show_oembed'];
		$settings['dabr_show_avatars'] = $_POST['dabr_show_avatars'];
		$settings['dabr_show_icons']   = $_POST['dabr_show_icons'];
		$settings['dabr_float_menu']   = $_POST['dabr_float_menu'];
		$settings['dabr_image_size']   = $_POST['dabr_image_size'];
		$settings['dabr_utc_offset']   = (float)$_POST['dabr_utc_offset'];
		$settings['dabr_font_size']   = $_POST['dabr_font_size'];
		$settings['dabr_fonts']       = $_POST['dabr_fonts'];

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

	$font_size = array(
		'0.5' => _(FONT_SMALLEST),
		'0.75'=> _(FONT_SMALL),
		'0.9'	=> _(FONT_MEDIUM),
		'1'	=> _(FONT_NORMAL),
		'1.25'=> _(FONT_BIG),
		'1.5'	=> _(FONT_LARGE),
		'2'	=> _(FONT_HUGE),
	);

	// $fonts = array( //ID => Value
	// 					'Schoolbell'	=> 'Schoolbell',
	// 					'Raleway'	=> 'Raleway',
	// 					'Ubuntu+Mono'	=> 'Ubuntu Mono',
	// 					'Karma'			=> 'Karma',
	// 					'Open+Sans'=> 'Open Sans',
	// 					'aaaaa'			=> 'bbbb',
	// 				);

	$utc_offset = setting_fetch('dabr_utc_offset', 0);
	//	returning 401 as it calls http://api.twitter.com/1/users/show.json?screen_name= (no username???)
	// if (!$utc_offset) {
	// 	$user = twitter_user_info(user_current_username());
	// 	$utc_offset = $user->utc_offset;
	// 	echo "THE UTC IS $utc_offset";
	// }

	if ($utc_offset > 0) {
		$utc_offset = '+' . $utc_offset;
	}

	$content = '';
	$content .= '<form action="settings/save" method="post">';

	$content .= theme('options', $colour_schemes,setting_fetch('dabr_colours', 0),          _(SETTINGS_COLOUR),    "dabr_colours");
	$content .= theme('options', $perPage,       setting_fetch('dabr_perPage', 20),         _(SETTINGS_PER_PAGE),  "dabr_perPage");
	$content .= theme('options', $image_size,    setting_fetch('dabr_image_size', "medium"),_(SETTINGS_IMAGE_SIZE),"dabr_image_size");

	global $fonts;
	$content .= "<fieldset><legend>"._(SETTINGS_FONT)."</legend>";
	$content .= theme('radio',array_combine($fonts,$fonts), "dabr_fonts", urldecode( substr(setting_fetch("dabr_fonts","Raleway"),0, -4)));
	$content .= "</fieldset>";
	$content .= "<fieldset><legend>"._(SETTINGS_FONT_SIZE)."</legend>";
	$content .= theme('radio',$font_size, "dabr_font_size", setting_fetch("dabr_font_size","1"));
	$content .= "</fieldset>";

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="dabr_timestamp" value="yes" '. (setting_fetch('dabr_timestamp','yes') == 'yes' ? ' checked="checked" ' : '') .' />'.
							sprintf(_(SETTINGS_TIMESTAMP), twitter_date('H:i')).
						'</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="dabr_show_inline" value="yes" '. (setting_fetch('dabr_show_inline','yes') == 'yes' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_SHOW_INLINE).
	                '</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="dabr_show_oembed" value="yes" '. (setting_fetch('dabr_show_oembed','yes') == yes ? ' checked="checked" ' : '') .' />'
	                  ._(SETTINGS_SHOW_PREVIEW).
						'</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="dabr_show_avatars" value="yes" '. (setting_fetch('dabr_show_avatars','yes') == 'yes' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_SHOW_AVATARS).
						'</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="dabr_show_icons" value="yes" '. (setting_fetch('dabr_show_icons','yes') == 'yes' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_MENU_ICONS).
						'</label>
	            </p>';

	$content .= '<p>
	               <label>
	                  <input type="checkbox" name="dabr_float_menu" value="yes" '. (setting_fetch('dabr_float_menu','yes') == 'yes' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_FLOAT_MENU).
						'</label>
	            </p>';


	$content .= '<p>
						<label>'.sprintf(_(SETTINGS_TIMESTAMP_IS),gmdate('H:i')) .'<br>' .
							sprintf(_(SETTINGS_TIMESTAMP_DISPLAY), "<input type=\"text\" name=\"dabr_utc_offset\" value=\"{$utc_offset}\" size=\"3\" />", twitter_date('H:i')) .
							'<br />'._(SETTINGS_TIMESTAMP_ADJUST).'
						</label>
					</p>';

	$content .= '<p>
						<label>
							<input type="checkbox" name="dabr_gwt" value="on" '. (setting_fetch('dabr_gwt') == 'on' ? ' checked="checked" ' : '') .' />'.
							_(SETTINGS_GWT_DETAIL) .
						'</label>
					</p>';

	$content .= '<p><input type="submit" value="'._(SETTINGS_SAVE_BUTTON).'" /></p></form>';

	$content .= '<hr /><p>'._(SETTINGS_RESET).'</p>';

	return theme('page', _(SETTINGS_TITLE), $content);
}
