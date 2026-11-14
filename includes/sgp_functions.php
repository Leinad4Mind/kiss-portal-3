<?php
/**
*
* Kiss Portal extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Michael O’Toole <http://www.phpbbireland.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/*
  sgp_get_rand_logo()
  set_k_config()
  k_progress_bar()
  sgp_checksize()
  smiley_sort()
  search_block_func()
  which_group()
  process_for_vars()
  get_user_data()
  portal_block_template()
  process_for_admin_bbcodes()
  get_page_id()
  get_menu_lang_name()
  s_get_vars_array()
  s_get_vars()
  get_link_from_image_name()
  generate_menus()

*/

/*
* A couple of functions rescued from functions.php
* @copyright (c) 2007 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU License
*/

if (!defined('IN_PHPBB'))
{
   exit;
}

if (!function_exists('portal_normalize_block_list'))
{
	/**
	 * Convert a submitted block layout to a unique comma-separated list of IDs.
	 *
	 * @param string $value Raw UCP or cookie value
	 * @return string
	 */
	function portal_normalize_block_list($value)
	{
		$ids = array();
		foreach (preg_split('/[,\s&]+/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY) as $candidate)
		{
			if (ctype_digit($candidate) && (int) $candidate > 0)
			{
				$ids[] = (int) $candidate;
			}
		}
		$ids = array_values(array_unique($ids));

		return implode(',', $ids);
	}
}

if (!function_exists('portal_sanitize_menu_target'))
{
	/**
	 * Accept HTTP(S) URLs or a relative phpBB path and reject executable schemes.
	 *
	 * @param string $target
	 * @return string
	 */
	function portal_sanitize_menu_target($target)
	{
		$target = trim((string) $target);
		if ($target === '')
		{
			return '';
		}

		if (filter_var($target, FILTER_VALIDATE_URL))
		{
			$scheme = strtolower((string) parse_url($target, PHP_URL_SCHEME));
			return in_array($scheme, array('http', 'https'), true) ? $target : '';
		}

		if (preg_match('/[\x00-\x20<>"\'\\\\]/', $target)
			|| preg_match('#(^|/)\.\.(/|$)#', $target)
			|| preg_match('#^[a-z][a-z0-9+.-]*:#i', $target)
			|| strpos($target, '//') === 0)
		{
			return '';
		}

		return ltrim($target, '/');
	}
}

if (!function_exists('sgp_get_rand_logo'))
{
	function sgp_get_rand_logo()
	{
		global $user, $phpbb_root_path, $k_config;

		// Random logos are disabled config, so return default logo //
		if (empty($k_config) || empty($k_config['k_allow_rotating_logos']))
		{
			return $user->img('site_logo');
		}

		$style_path = basename($user->style['style_path']);
		$logos_dir = $phpbb_root_path . 'ext/phpbbireland/portal/styles/' . $style_path . '/theme/images/logos';

		$handle = @opendir($logos_dir);

		if (!$handle) // no handle so we don't have logo directory or we are attempting to login to ACP so we need to return the default logo //
		{
			return($user->img('site_logo'));
		}

		$images = array();
		while (false !== ($file = readdir($handle)))
		{
			if (preg_match('/\.(?:gif|jpe?g|png)$/i', $file))
			{
				$images[] = $file;
			}
		}
		closedir($handle);

		if (!$images)
		{
			return $user->img('site_logo');
		}

		$image = $images[array_rand($images)];
		$web_path = 'ext/phpbbireland/portal/styles/' . rawurlencode($style_path) . '/theme/images/logos/' . rawurlencode($image);

		return '<img src="' . utf8_htmlspecialchars($web_path) . '" alt="" /><br />';
	}
}


	/***
	* set config value phpbb code reused
	*/
if (!function_exists('set_k_config'))
{
	function set_k_config($config_name, $config_value, $is_dynamic = false)
	{
		global $db, $cache, $table_prefix;

		$k_config = $cache->get('k_config');

		$sql = 'UPDATE ' . K_VARS_TABLE . "
			SET config_value = '" . $db->sql_escape($config_value) . "'
			WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$result = $db->sql_query($sql);

		if (!$result)
		//if (!$db->sql_affectedrows() && !isset($k_config[$config_name]))
		{
			$sql = 'INSERT INTO ' . K_VARS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'config_name'   => $config_name,
				'config_value'  => $config_value,
				'is_dynamic'    => ($is_dynamic) ? 1 : 0));
			$db->sql_query($sql);
		}

		$k_config[$config_name] = $config_value;

		if (!$is_dynamic)
		{
			$cache->destroy('k_config');
			$cache->destroy('config');
		}
	}
}


/****
if (!function_exists('get_k_config_var'))
{
	function get_k_config_var($item)
	{
		define('K_CONFIG_TABLE',	$table_prefix . 'k_variables');

		if (isset($item))
		{
			return($item);
		}

		$sql = 'SELECT config_name, config_value
			FROM ' . K_CONFIG_TABLE . '
			WHERE config_name = ' . (int)$item;

		$row = $db->sql_fetchrow($result);

		//$k_config[$row['config_name']] = $row['config_value'];
		return $row['config_value'];
	}
}
*****/


if (!function_exists('k_progress_bar'))
{
	function k_progress_bar($percent)
	{
		// $percent = number between 0 and 100 //

		$ss = "";

		// define these in css
		$start = '<b class="green">';   // green
		$middl = '<b class="orange">';  // orange
		$endss = '<b class="red">';     // red

		$tens = $percent / 10; // how many tens //

		if ($percent % 10)
		{
			$i = 1;
		}
		else
		{
			$i = 0;
		}

		for ($i; $i < ($percent / 10); $i++)
		{
			$ss .= '|';
		}

		$start .= $ss . '</b>';

		if ($percent % 10)
		{
			$start .= $middl . '|' . '</b>' . $endss;
		}
		else
		{
			$start .= '' . $endss;
		}

		while ($i++ < 10)
		{
			$start .= '|';
		}

		$start .= '</b>';

		return ' [' . $start . ']';
	}
}


/***
* same as truncate_string() with ...
*/
if (!function_exists('sgp_checksize'))
{
	function sgp_checksize($txt, $len)
	{
		if (strlen($txt) > $len)
		{
			$txt = truncate_string($txt, $len);
			$txt .= '...';
		}
		return($txt);
	}
}


/***
* sort smilies
*/
if (!function_exists('smiley_sort'))
{
	function smiley_sort($a, $b)
	{
		if ( strlen($a['code']) == strlen($b['code']) )
		{
			return 0;
		}

		return ( strlen($a['code']) > strlen($b['code']) ) ? -1 : 1;
	}
}

/***
* search block search
*/
if (!function_exists('search_block_func'))
{
	function search_block_func()
	{
		global $lang, $template, $portal_config, $board_config, $keywords, $phpbb_root_path;

		$phpEx = substr(strrchr(__FILE__, '.'), 1);

		$template->assign_vars(array(
			"L_SEARCH_ADV"     => $lang['SEARCH_ADV'],
			"L_SEARCH_OPTION"  => (!empty($portal_config['search_option_text'])) ? $portal_config['search_option_text'] : $board_config['sitename'],
			'U_SEARCH'         => append_sid("{$phpbb_root_path}search.$phpEx", 'keywords=' . urlencode($keywords)),
		));
	}
}

/**
*	returns the users group name
*/
if (!function_exists('which_group'))
{
	function which_group($id)
	{
		global $db, $template;

		// Get group name for this user
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . (int) $id;

		$result = $db->sql_query($sql,650);

		$name = $db->sql_fetchfield('group_name');

		$db->sql_freeresult($result);

		return ($name);
	}
}


if (!function_exists('process_for_vars'))
{
	function process_for_vars($data)
	{
		global $config;
		global $k_config, $k_menus, $k_blocks, $k_pages, $k_groups, $k_resources;

		$a = array('{', '}');
		$b = array('','');

		$replace = array();

		foreach ($k_resources as $search)
		{
			$find = $search;

			// convert to normal text //
			$search = str_replace($a, $b, $search);
			$search = strtolower($search);

			if (isset($k_config[$search]))
			{
				$replace = (isset($k_config[$search])) ? $k_config[$search] : '';
				$data = str_replace($find, $replace, $data);
			}
			else if (isset($config[$search]))
			{
				$replace = (isset($config[$search])) ? $config[$search] : '';
				$data = str_replace($find, $replace, $data);
			}
		}
		return($data);
	}
}


// Stargate Random Banner mod //
if (!function_exists('get_user_data'))
{
	function get_user_data($id, $what = '')
	{
		global $db, $template, $user;

		if (!$id)
		{
			return($user->lang['NO_ID_GIVEN']);
		}

		// Get user info
		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $id;

		$result = $db->sql_query($sql,10);
		$row = $db->sql_fetchrow($result);

		$db->sql_freeresult($result);

		switch ($what)
		{
			case 'name':
				return($row['username']);

			case 'full':
				return(get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']));

			default:
				return;
		}
	}
}

/**
* templates
*/
if (!function_exists('portal_block_template'))
{
	function portal_block_template($block_file)
	{
		global $template, $phpbb_root_path;

		//if ($block_file == '') var_dump('Bug missing: '. $block_file);

		// Set template filename
		$template->set_filenames(array('block' => 'blocks/' . $block_file));

		//$template->set_filenames(array('block' => $phpbb_root_path .  'ext/phpbbireland/portal/styles/prosilver/template/blocks/' . $block_file));

		// Return templated data
		return $template->assign_display('block', true);
	}
}

if (!function_exists('process_for_admin_bbcodes'))
{
	function process_for_admin_bbcodes($data)
	{
		global $user;

		// later pull admin bbcodes from DB //

		if ($user->data['user_id'] == ANONYMOUS)
		{
			$data = str_replace("[you]", $user->lang['GUEST'], $data);
		}
		else
		{
			$data = str_replace("[you]", ('<span style="font-weight:bold; color:#' . $user->data['user_colour'] . ';">' . $user->data['username'] . '</span>'), $data);
		}
		return($data);
	}
}


/*
* Takes the page name
* Returns the pages id
*/
if (!function_exists('get_page_id'))
{
	function get_page_id($this_page_name)
	{
		global $db, $user, $k_pages;

		if (is_array($k_pages))
		{
			foreach ($k_pages as $page)
			{
				if ($page['page_name'] == $this_page_name)
				{
					return($page['page_id']);
				}
			}
		}
		else
		{
			throw new \phpbbireland\portal\exception('Not in array [' . $this_page_name . ']');
		}

	}
}

/**
* Convert Menu Name to language variable... leave alone if not found!
**/
if (!function_exists('get_menu_lang_name'))
{
	function get_menu_lang_name($input)
	{
		global $user;

		// Basic error checking //
		if ($input == '')
		{
			return('');
		}

		$block_title = $input;
		$name = strtoupper($input);
		$name = str_replace(" ","_", $name);
		$block_title = (!empty($user->lang[$name])) ? $user->lang[$name] : $block_title;

		return($block_title);
	}
}



if (!function_exists('s_get_vars_array'))
{
	function s_get_vars_array()
	{
		global $db, $template, $table_prefix;

		define('K_RESOURCES_TABLE',	$table_prefix . 'k_resources');

		$resources = array();

		$sql = 'SELECT * FROM ' . K_RESOURCES_TABLE  . ' ORDER BY word ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$resources[] = $row['word'];
		}

		$db->sql_freeresult($result);
		return($resources);
	}
}

if (!function_exists('s_get_vars'))
{
	function s_get_vars()
	{
		global $db, $template, $table_prefix;

		define('K_RESOURCES_TABLE',	$table_prefix . 'k_resources');

		$sql = "SELECT * FROM " . K_RESOURCES_TABLE  . " WHERE type = 'V' ORDER BY word ASC";

		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('adm_vars', array(
				'VAR' => $row['word'],
			));
		}
		$db->sql_freeresult($result);
	}
}

if (!function_exists('get_link_from_image_name'))
{
	function get_link_from_image_name($image)
	{
		if (strpos($image, '.gif'))
		{
			$lnk = explode(".gif", $image);
		}
		else if (strpos($image, '.png'))
		{
			$lnk = explode(".png", $image);
		}
		else if (strpos($image, '.jpg'))
		{
			$lnk = explode(".jpg", $image);
		}

		$lnk = str_replace('+','/', $lnk);
		$lnk = str_replace('@','?', $lnk);
		$lnk = str_replace('£','+', $lnk);

		return($lnk);
	}
}

/***
*	build and handles menus //
*
**/
if (!function_exists('generate_menus'))
{
	function generate_menus()
	{
		global $k_groups, $k_blocks, $k_menus;
		global $template, $phpbb_root_path, $auth, $user, $phpEx, $request, $config;
		static $process = 0;

		$menu_image_path = $phpbb_root_path . 'ext/phpbbireland/portal/images/block_images/menu/';

		// process all menus at once //
		if ($process)
		{
			return;
		}

		//$user->add_lang('portal/kiss_block_variables');
		$user->add_lang_ext('phpbbireland/portal', 'kiss_block_variables');

		$p_count = $k_menus ? max(array_map('intval', array_keys($k_menus))) : 0;

		$hash = $request->variable('hash', '');

		if (!function_exists('group_memberships'))
		{
			include($phpbb_root_path . 'includes/functions_user.'. $phpEx);
		}
		$memberships = array();
		$memberships = group_memberships(false, $user->data['user_id'], false);

		for ($i = 1; $i < $p_count + 1; $i++)
		{
			if (isset($k_menus[$i]['menu_type']))
			{
				$u_id = '';
				$isamp = '';

				$menu_view_groups = $k_menus[$i]['view_groups'];
				$menu_item_view_all = $k_menus[$i]['view_all'];

				// skip process if everyone can view this menus //
				if ($menu_item_view_all == 1)
				{
					$process_menu_item = true;
				}
				else
				{
					$process_menu_item = false;
				}

				if (!$process_menu_item)
				{
					$grps = explode(",", $menu_view_groups);

					if ($memberships)
					{
						foreach ($memberships as $member)
						{
							for ($j = 0; $j < count($grps); $j++)
							{
								if ($grps[$j] == $member['group_id'])
								{
									$process_menu_item = true;
								}
							}
						}
					}
				}

				if ($k_menus[$i]['append_uid'] == 1)
				{
					$isamp = '&amp';
					$u_id = $user->data['user_id'];
				}
				else
				{
					$u_id = '';
					$isamp = '';
				}

				if ($process_menu_item)
				{
					$name = strtoupper($k_menus[$i]['name']);
					$tmp_name = str_replace(' ','_', $name);
					$name = (!empty($user->lang[$tmp_name])) ? $user->lang[$tmp_name] : $k_menus[$i]['name'];
					$name = utf8_htmlspecialchars($name);
					$link_target = portal_sanitize_menu_target($k_menus[$i]['link_to']);

					if (preg_match('#^https?://#i', $link_target))
					{
						$link = $link_target;
					}
					else
					{
						if ($k_menus[$i]['append_sid'])
						{
							if (strpos($link_target, 'hash') !== false) // allow Mark forums read //
							{
								$link = ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums') : '';
							}
							else
							{
								$link = ($link_target && $auth->acl_get('a_') && !empty($user->data['is_registered'])) ? append_sid("{$phpbb_root_path}{$link_target}", false, true, $user->session_id) : '';
							}
						}
						else
						{
							$link = $link_target ? append_sid("{$phpbb_root_path}" . $link_target . $u_id) : '';
						}
					}
					$link = utf8_htmlspecialchars(html_entity_decode($link, ENT_QUOTES, 'UTF-8'));
					$menu_icon = basename(str_replace('\\', '/', $k_menus[$i]['menu_icon']));
					$menu_icon = preg_match('/^[a-z0-9_.-]+\.(?:gif|jpe?g|png|svg)$/i', $menu_icon) ? $menu_icon : '';
					$menu_icon_html = $menu_icon
						? '<img src="' . utf8_htmlspecialchars($menu_image_path . $menu_icon) . '" height="16" width="16" alt="" />'
						: '<img src="' . utf8_htmlspecialchars($menu_image_path . 'spacer.gif') . '" height="15" width="15" alt="" />';

					$is_sub_heading = ($k_menus[$i]['sub_heading']) ? true : false;

					// we use js to manage open ibn tab //
					switch ($k_menus[$i]['extern'])
					{
						case 1:
							$link_option = 'rel="external"';
						break;

						case 2:
							$link_option = 'target="_blank" rel="noopener noreferrer"';
						break;

						default:
							$link_option = '';
						break;
					}

					// can be reduce later...
					if ($k_menus[$i]['menu_type'] == NAV_MENUS)
					{
						$template->assign_block_vars('portal_nav_menus_row', array(
							'PORTAL_LINK_OPTION'	=> $link_option,
							'PORTAL_MENU_HEAD_NAME'	=> ($is_sub_heading) ? $name : '',
							'PORTAL_MENU_NAME' 		=> $name,
							'PORTAL_MENU_ICON'		=> $menu_icon_html,
							'U_PORTAL_MENU_LINK' 	=> ($k_menus[$i]['sub_heading']) ? '' : $link,
							'S_SOFT_HR'				=> $k_menus[$i]['soft_hr'],
							'S_SUB_HEADING' 		=> ($k_menus[$i]['sub_heading']) ? true : false,
						));
					}
					else if ($k_menus[$i]['menu_type'] == SUB_MENUS)
					{
						$template->assign_block_vars('portal_sub_menus_row', array(
							'PORTAL_LINK_OPTION'	=> $link_option,
							'PORTAL_MENU_HEAD_NAME'	=> ($is_sub_heading) ? $name : '',
							'PORTAL_MENU_NAME' 		=> $name,
							'PORTAL_MENU_ICON'		=> $menu_icon_html,
							'U_PORTAL_MENU_LINK' 	=> ($k_menus[$i]['sub_heading']) ? '' : $link,
							'S_SOFT_HR'				=> $k_menus[$i]['soft_hr'],
							'S_SUB_HEADING' 		=> ($k_menus[$i]['sub_heading']) ? true : false,
						));
					}
					else if ($k_menus[$i]['menu_type'] == LINKS_MENUS)
					{
						$template->assign_block_vars('portal_link_menus_row', array(
							'LINK_OPTION'					=> $link_option,
							'PORTAL_LINK_MENU_HEAD_NAME'	=> ($is_sub_heading) ? $name : '',
							'PORTAL_LINK_MENU_NAME'			=> ($is_sub_heading) ? '' : $name,
							'U_PORTAL_LINK_MENU_LINK'		=> ($is_sub_heading) ? '' : $link,
							'PORTAL_LINK_MENU_ICON'			=> ($k_menus[$i]['menu_icon'] == 'NONE') ? '' : $menu_icon_html,
							'S_SOFT_HR'						=> $k_menus[$i]['soft_hr'],
							'S_SUB_HEADING'					=> ($k_menus[$i]['sub_heading']) ? true : false,
						));
					}
				}
			}
		}
		$process = 1;

		$template->assign_vars(array(
			'S_USER_LOGGED_IN'	=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
			'U_INDEX'			=> append_sid("{$phpbb_root_path}index.$phpEx"),
			'U_PORTAL'			=> append_sid("{$phpbb_root_path}portal"),
		));
	}
}
