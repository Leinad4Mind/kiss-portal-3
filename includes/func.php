<?php
/**
*
* Kiss Portal extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Michael O’Toole <http://www.phpbbireland.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbbireland\portal\includes;

class func
{
	/* @var \phpbb\config */
	protected $config;

	/* @var \phpbb\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbbireland\portal */
	protected $portal;

	/* @var string phpBB root path */
	protected $root_path;

	/* @var string phpBB root path used by legacy block includes */
	protected $phpbb_root_path;

	/* @var string phpEx */
	protected $php_ext;

	public function process_block_modules()
	{
		global $phpbb_root_path, $config, $table_prefix, $helper;
		global $db, $user, $avatar_img, $request, $template, $auth;
		global $k_config, $k_groups, $k_blocks, $page_header;
		global $phpbb_path_helper;

		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpbb_path_helper->get_php_ext();
		$user->add_lang_ext('phpbbireland/portal', 'kiss_block_variables');

		$block_cache_time  = $k_config['k_block_cache_time_default'];
		$blocks_width 	   = $config['blocks_width'];
		$blocks_enabled    = $config['blocks_enabled'];
		$use_block_cookies = isset($k_config['use_block_cookies']) ? (bool) $k_config['use_block_cookies'] : true;

		if (!$blocks_enabled)
		{
			$template->assign_vars(array(
				'PORTAL_MESSAGE' => $user->lang('BLOCKS_DISABLED'),
			));
		}

		$all = '';
		$show_center = $show_left = $show_right = false;
		$LB = $CB = $RB = array();
		$active_blocks = array();
		$arr = array();

		// if styles use large block images change path to images //
		$block_image_path = $phpbb_root_path . 'ext/phpbbireland/portal/images/block_images/block/';
		$big_image_path   = $phpbb_root_path . 'ext/phpbbireland/portal/images/block_images/large/';

		$this_page = explode(".", $user->page['page']);
		$this_page_name = $this->get_current_page();
		$user_id = $user->data['user_id'];

//		$logo = sgp_get_rand_logo();
//		var_dump($logo);

		$theme = rawurlencode($user->style['style_path']);
		$arrange_blocks = $request->variable('arrange', 0) === 1
			&& $user->data['user_id'] != ANONYMOUS
			&& $auth->acl_get('u_k_portal')
			&& strpos($this_page_name, 'portal') !== false;

		$template->assign_vars(array(
			'EXT_TEMPLATE_PATH'		=> $phpbb_root_path . 'ext/phpbbireland/portal/styles/' . $theme,
			'EXT_IMAGE_PATH'		=> $phpbb_root_path . 'ext/phpbbireland/portal/images/',
			'MOD_IMAGE_LANG_PATH'	=> $phpbb_root_path . 'ext/phpbbireland/portal/styles/' . $theme . '/theme/' . $user->data['user_lang'] . '/',
			//'SITE_LOGO_IMG'			=> $logo,
		));

		include_once($phpbb_root_path . 'ext/phpbbireland/portal/includes/sgp_functions.' . $this->php_ext);

		// Grab data for this user //
		$sql = "SELECT group_id, user_type, user_style, user_avatar, user_avatar_type, username, user_left_blocks, user_center_blocks, user_right_blocks
			FROM " . USERS_TABLE . "
			WHERE user_id = " . (int) $user_id;

		if ($result = $db->sql_query($sql))
		{
			$row = $db->sql_fetchrow($result);

			$user_avatar = $row['user_avatar'];
			$user_style = $row['user_style'];
			$usertype = $row['user_type'];
			$groupid = $row['group_id'];

			$left = portal_normalize_block_list($row['user_left_blocks']);
			$LB = explode(',', $left);
			$center = portal_normalize_block_list($row['user_center_blocks']);
			$CB = explode(',', $center);
			$right = portal_normalize_block_list($row['user_right_blocks']);
			$RB = explode(',', $right);

			$LCR = array_values(array_unique(array_filter(array_map('intval', array_merge($LB, $CB, $RB)))));
			$all = implode(',', $LCR);

		}
		else
		{
			trigger_error($user->lang['ERROR_USER_TABLE']);
		}

		// Process block positions for members only //
		if ($user->data['user_id'] != ANONYMOUS)
		{
			if ($use_block_cookies
				&& isset($_COOKIE[$config['cookie_name'] . '_sgp_left'])
				&& isset($_COOKIE[$config['cookie_name'] . '_sgp_center'])
				&& isset($_COOKIE[$config['cookie_name'] . '_sgp_right']))
			{
				$left = $request->variable($config['cookie_name'] . '_sgp_left', '', false, \phpbb\request\request_interface::COOKIE);
				$left = str_replace("left[]=", "", $left);
				$left = str_replace("&amp;", ',', $left);
				$left = portal_normalize_block_list($left);
				$LBA = explode(',', $left);

				$center = $request->variable($config['cookie_name'] . '_sgp_center', '', false, \phpbb\request\request_interface::COOKIE);
				$center = str_replace("center[]=", "", $center);
				$center = str_replace("&amp;", ',', $center);
				$center = portal_normalize_block_list($center);
				$CBA = explode(',', $center);

				$right = $request->variable($config['cookie_name'] . '_sgp_right', '', false, \phpbb\request\request_interface::COOKIE);
				$right = str_replace("right[]=", "", $right);
				$right = str_replace("&amp;", ',', $right);
				$right = portal_normalize_block_list($right);
				$RBA = explode(',', $right);

				$LB = $LBA;
				$CB = $CBA;
				$RB = $RBA;
				$LCR = array_values(array_unique(array_filter(array_map('intval', array_merge($LB, $CB, $RB)))));
				$all = implode(',', $LCR);

				// Store the complete three-column layout, including empty columns.
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_left_blocks = ' . "'" . $db->sql_escape($left) . "'" . ', user_center_blocks = ' . "'" . $db->sql_escape($center) . "'" . ', user_right_blocks = ' . "'" . $db->sql_escape($right) . "'" . '
					WHERE user_id = ' . (int) $user->data['user_id'];
				$db->sql_query($sql);
				$template->assign_var('S_CLEAR_CACHE', true);
			}

		if (!$LCR)
			{
				$sql = "SELECT *
					FROM " . K_BLOCKS_TABLE . "
					WHERE active = 1
						AND (view_pages <> '0')
						ORDER BY ndx ASC";
			}
			else
			{
				$sql = "SELECT *
					FROM " . K_BLOCKS_TABLE . "
					WHERE active = 1
						AND (view_pages <> '0')
						AND " . $db->sql_in_set('id', $LCR) . "
				ORDER BY ndx ASC";
			}
		}
		else
		{
			$sql = "SELECT *
				FROM " . K_BLOCKS_TABLE . "
				WHERE active = 1
						AND (view_pages <> '0')
						ORDER BY ndx ASC";
		}

		$result = $db->sql_query($sql, $block_cache_time);

		while ($row = $db->sql_fetchrow($result))
		{
			$active_blocks[] = $row;
			$arr[$row['id']] = explode(','  , $row['view_pages']);
		}
		$db->sql_freeresult($result);

		if ($all && count($active_blocks) > 1)
		{
			$layout_order = array_flip($LCR);
			usort($active_blocks, function ($first, $second) use ($layout_order)
			{
				$first_order = isset($layout_order[(int) $first['id']]) ? $layout_order[(int) $first['id']] : PHP_INT_MAX;
				$second_order = isset($layout_order[(int) $second['id']]) ? $layout_order[(int) $second['id']] : PHP_INT_MAX;

				return $first_order <=> $second_order;
			});
		}

		$id = $request->variable('style', 0);

		if ($id)
		{
			if (strstr($this_page_name, 'portal'))
			{
				$page_id = get_page_id('portal');
			}
			else if (strstr($this_page_name, 'index'))
			{
				$page_id = get_page_id('index');
			}
			else
			{
				$page_id = get_page_id($this_page_name);
			}
		}
		else
		{
			$page_id = get_page_id($this_page_name);
		}

		//var_dump('RETURNED: ' . $this_page_name . ' ID: ' . $page_id);

		if ($page_id == 0)
		{
			$page_id = $this_page[0];
			$page_id = get_page_id($this_page[0]);
		}

		foreach ($active_blocks as $active_block)
		{
			$filename = pathinfo(basename(str_replace('\\', '/', $active_block['html_file_name'])), PATHINFO_FILENAME);
			if (!preg_match('/^block_[a-z0-9_-]+$/i', $filename))
			{
				continue;
			}
			if (file_exists($phpbb_root_path . 'ext/phpbbireland/portal/blocks/' . $filename . '.' . $this->php_ext))
			{
				if (in_array($page_id, $arr[$active_block['id']]))
				{
					//var_dump('process_block_modules > foreach returned' . $filename);
					include($phpbb_root_path . 'ext/phpbbireland/portal/blocks/' . $filename . '.' . $this->php_ext);
				}
			}
		}

		if (!function_exists('group_memberships'))
		{
			include($phpbb_root_path . 'includes/functions_user.'. $this->php_ext);
		}
		$memberships = array();
		$memberships = group_memberships(false, $user->data['user_id'], false);

		// Main processing of block data here //
		if ($active_blocks)
		{
			$L = $R = $C = 0;
			foreach ($active_blocks as $row)
			{
				$block_position = $row['position'];

				// override default position with user designated position //
				if (in_array($row['id'], $LB))
				{
					$block_position		= 'L';
				} else if (in_array($row['id'], $CB))
				{
					$block_position		= 'C';
				} else if (in_array($row['id'], $RB))
				{
					$block_position		= 'R';
				}

				$block_id           = $row['id'];
				$block_ndx          = $row['ndx'];
				$block_title        = $row['title'];
				$block_active       = $row['active'];
				$block_type         = $row['type'];
				$block_view_groups  = $row['view_groups'];
				$block_view_all     = $row['view_all'];
				$block_scroll       = $row['scroll'];
				$block_height       = $row['block_height'];
				$html_file_name     = $row['html_file_name'];
				$img_file_name      = $row['img_file_name'];
				$view_pages         = $row['view_pages'];

				$arr = explode(',', $view_pages);
				$grps = explode(",", $block_view_groups);
				$process_block = false;
				$block_title = get_menu_lang_name($row['title']);

				// process blocks for different groups //
				if (isset($memberships))
				{
					foreach ($memberships as $member)
					{
						// First we check to see if the view_all over-ride is set (saves having to enter all groups) //
						if ($block_view_all)
						{
							$process_block = true;
						}
						else
						{
							for ($j = 0; $j < $jcount = count($grps); $j++) // now we loop for all group the user is in //
							{
								if ($grps[$j] == $member['group_id'])
								{
									$process_block = true;
								}
							}
						}
					}
					//unset($memberships);
				}

				if ($process_block && in_array($page_id, $arr))
				{
					switch ($block_position)
					{
						case 'L':
								$left_block_ary[$L]    = $html_file_name;
								$left_block_id[$L]     = $block_id;
								$left_block_ndx[$L]    = $block_ndx;
								$left_block_title[$L]  = $block_title;
								$left_block_img[$L]    = $img_file_name;
								$left_block_scroll[$L] = $block_scroll;
								$left_block_height[$L] = $block_height;
								$L++;
								$show_left = true;//show_blocks($this_page_name, $block_position);
						break;
						case 'C':
								$center_block_ary[$C]    = $html_file_name;
								$center_block_id[$C]     = $block_id;
								$center_block_ndx[$C]    = $block_ndx;
								$center_block_title[$C]  = $block_title;
								$center_block_img[$C]    = $img_file_name;
								$center_block_scroll[$C] = $block_scroll;
								$center_block_height[$C] = $block_height;
								$C++;
								$show_center = true;//show_blocks($this_page_name, $block_position);
						break;
						case 'R':
								$right_block_ary[$R]    = $html_file_name;
								$right_block_id[$R]     = $block_id;
								$right_block_ndx[$R]    = $block_ndx;
								$right_block_title[$R]  = $block_title;
								$right_block_img[$R]    = $img_file_name;
								$right_block_scroll[$R] = $block_scroll;
								$right_block_height[$R] = $block_height;
								$R++;
								$show_right = true;//show_blocks($this_page_name, $block_position);
						break;
						default:
					}
					///var_dump($html_file_name);
				}
			}
		}

		//unset($active_blocks);

		if (isset($left_block_ary) && $show_left)
		{
			foreach ($left_block_ary as $block => $value)
			{
				$template->assign_block_vars('left_block_files', array(
					'LEFT_BLOCKS'           => $this->build_block_modules($value),
					'LEFT_BLOCK_ID'         => 'L_' .$left_block_id[$block],
					'LEFT_BLOCK_TITLE'      => $left_block_title[$block],
					'LEFT_BLOCK_SCROLL'     => $left_block_scroll[$block],
					'LEFT_BLOCK_HEIGHT'     => $left_block_height[$block],
					'LEFT_BLOCK_IMG'        => ($left_block_img[$block]) ? $block_image_path . $left_block_img[$block] : $block_image_path . 'none.gif',
					'LEFT_BLOCK_IMG_2'      => (file_exists($big_image_path . $left_block_img[$block])) ? $big_image_path  . $left_block_img[$block] : $big_image_path . 'none.png',
					'S_CONTENT_FLOW_BEGIN'  => ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
					'S_CONTENT_FLOW_END'    => ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
				));
			}
		}
		//unset($left_block_ary);

		if (isset($right_block_ary) && $show_right)
		{
			foreach ($right_block_ary as $block => $value)
			{
				$template->assign_block_vars('right_block_files', array(
					'RIGHT_BLOCKS'          => $this->build_block_modules($value),
					'RIGHT_BLOCK_ID'        => 'R_' .$right_block_id[$block],
					'RIGHT_BLOCK_TITLE'     => $right_block_title[$block],
					'RIGHT_BLOCK_SCROLL'    => $right_block_scroll[$block],
					'RIGHT_BLOCK_HEIGHT'    => $right_block_height[$block],
					'RIGHT_BLOCK_IMG'       => ($right_block_img[$block]) ? $block_image_path . $right_block_img[$block] : $block_image_path . 'none.gif',
					'RIGHT_BLOCK_IMG_2'     => (file_exists($big_image_path . $right_block_img[$block])) ? $big_image_path  . $right_block_img[$block] : $big_image_path . 'none.png',
					'S_CONTENT_FLOW_BEGIN'  => ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
					'S_CONTENT_FLOW_END'    => ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
				));
			}
		}
		//unset($right_block_ary);

		if (isset($center_block_ary) && $show_center)
		{
			foreach ($center_block_ary as $block => $value)
			{
				$template->assign_block_vars('center_block_files', array(
					'CENTER_BLOCKS'        => $this->build_block_modules($value),
					'CENTER_BLOCK_ID'      => 'C_' .$center_block_id[$block],
					'CENTER_BLOCK_TITLE'   => $center_block_title[$block],
					'CENTER_BLOCK_SCROLL'  => $center_block_scroll[$block],
					'CENTER_BLOCK_HEIGHT'  => $center_block_height[$block],
					'CENTER_BLOCK_IMG'     => ($center_block_img[$block]) ? $block_image_path . $center_block_img[$block] : $block_image_path . 'none.gif',
					'CENTER_BLOCK_IMG_2'   => (file_exists($big_image_path . $center_block_img[$block])) ? $big_image_path  . $center_block_img[$block] : $big_image_path . 'none.png',
					'S_CONTENT_FLOW_BEGIN' => ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
					'S_CONTENT_FLOW_END'   => ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
				));
			}
		}
		//unset($center_block_ary);


		$avatar_data = array(
			'avatar' => $user->data['user_avatar'],
			'avatar_width' => $user->data['user_avatar_width'],
			'avatar_height' => $user->data['user_avatar_height'],
			'avatar_type' => $user->data['user_avatar_type'],
		);


		$template->assign_vars(array(
			'ASSETS_PATH'            => $phpbb_root_path . 'ext/phpbbireland/portal/styles/' . rawurlencode($user->style['style_path']) . '/template/assets/',
			//'AVATAR'                  => get_user_avatar($user->data['user_avatar'], $user->data['user_avatar_type'], $user->data['user_avatar_width'], $user->data['user_avatar_height']),
			'AVATAR'                  => phpbb_get_avatar($avatar_data, $user->lang['USER_AVATAR'], false),
			'BLOCK_WIDTH'             => $blocks_width . 'px',
			'PORTAL_ACTIVE'           => $config['portal_enabled'],
			'PORTAL_BUILD'            => $config['portal_build'],
			'PORTAL_VERSION'          => $config['portal_version'],
			'SITE_NAME'               => $config['sitename'],
			'S_USER_LOGGED_IN'        => ($user->data['user_id'] != ANONYMOUS) ? true : false,
			'S_SHOW_LEFT_BLOCKS'      => $show_left,
			'S_SHOW_RIGHT_BLOCKS'     => $show_right,
			'S_BLOCKS_ENABLED'        => $blocks_enabled,
			'S_K_FOOTER_IMAGES_ALLOW' => ($k_config['k_footer_images_allow']) ? true : false,
			'S_CONTENT_FLOW_BEGIN'    => ($user->lang['DIRECTION'] == 'ltr') ? 'left' : 'right',
			'S_CONTENT_FLOW_END'      => ($user->lang['DIRECTION'] == 'ltr') ? 'right' : 'left',
			'USER_NAME'               => $user->data['username'],
			'USERNAME_FULL'           => get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour']),

			'U_INDEX'                 => append_sid("{$phpbb_root_path}index.$this->php_ext"),
			'U_PORTAL'                => append_sid("{$phpbb_root_path}portal.$this->php_ext"),
			'U_STAFF'                 => append_sid("{$phpbb_root_path}memberlist.$this->php_ext", 'mode=leaders'),
			'U_SEARCH_BOOKMARKS'      => append_sid("{$phpbb_root_path}ucp.$this->php_ext", 'i=main&mode=bookmarks'),

			'U_PORTAL_ARRANGE'        => append_sid("{$phpbb_root_path}app.$this->php_ext/portal.php", 'arrange=1'),
			'S_ARRANGE'               => $arrange_blocks,
			'COOKIE_NAME'             => $config['cookie_name'],
			'HIDE_IMG'		=> '<img src="ext/phpbbireland/portal/images/hide.png"  alt="' . $user->lang['SHOWHIDE'] . '" title="' . $user->lang['SHOWHIDE'] . '" height="16" width="14" />',
			'MOVE_IMG'		=> '<img src="ext/phpbbireland/portal/images/move.png"  alt="' . $user->lang['MOVE'] . '" title="' . $user->lang['MOVE'] . '" height="16" width="14" />',
			'SHOW_IMG'		=> '<img src="ext/phpbbireland/portal/images/show.png"  alt="' . $user->lang['SHOW'] . '" title="' . $user->lang['SHOW'] . '" height="16" width="14" />',
		));

	}

	public function build_block_modules($block_file)
	{
		///var_dump('func.php > build_block_modules(' . $block_file . ') - called for each block!');
		global $template;
		$block_file = basename(str_replace('\\', '/', $block_file));
		if (!preg_match('/^block_[a-z0-9_-]+\.html$/i', $block_file))
		{
			return '';
		}
		$template->set_filenames(array('block' => '@phpbbireland_portal' . '/blocks/' . $block_file));
		return $template->assign_display('block', true);
	}

	/*
	* return the current page
	*/
	public function get_current_page()
	{
		global $user;

		$this_page = explode(".", $user->page['page']);

		if ($this_page[0] == 'app')
		{
			$this_page_name = explode("/", $this_page[1]);
			return($this_page_name[1]);
		}
		else
		{
			$this_page_name = $this_page;
			return($this_page_name[0]);
		}
	}
}
