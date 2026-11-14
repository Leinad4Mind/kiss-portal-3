<?php
/**
*
* Kiss Portal extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Michael O’Toole <http://www.phpbbireland.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


// Anonymous users can't select a style...
if ($user->data['user_id'] == ANONYMOUS)
{
	return;
}

global $user, $request, $template, $phpbb_root_path, $phpEx, $db, $k_blocks;


$style_count = 0;
$style_select = '';
$this_page = ltrim((string) parse_url($user->page['page'], PHP_URL_PATH), '/');
if (!preg_match('#^(?:app\.' . preg_quote($phpEx, '#') . '/)?[a-z0-9_/-]+\.' . preg_quote($phpEx, '#') . '$#i', $this_page))
{
	$this_page = 'index.' . $phpEx;
}

// rebuild forum and topic (viewforum, viewtopic) //
$appends = array();
$fo = $request->variable('f', 0);
$to = $request->variable('t', 0);

if ($fo != 0)
{
	$appends[] = 'f=' . $fo;
}
if ($to != 0)
{
	$appends[] = 't=' . $to;
}

foreach ($k_blocks as $blk)
{
	if ($blk['html_file_name'] == 'block_style_select.html')
	{
		$block_cache_time = $blk['block_cache_time'];
		break;
	}
}
$block_cache_time = (isset($block_cache_time) ? $block_cache_time : $k_config['k_block_cache_time_default']);

$sql = 'SELECT style_id, style_name
	FROM ' . STYLES_TABLE . '
	WHERE style_active = 1
	ORDER BY LOWER(style_name) ASC';
$result = $db->sql_query($sql, $block_cache_time);

while ($row = $db->sql_fetchrow($result))
{
	$query = array_merge(array('style=' . (int) $row['style_id']), $appends);
	$url = append_sid("{$phpbb_root_path}{$this_page}", implode('&amp;', $query));
	++$style_count;

	$style_select .= '<option value="' . utf8_htmlspecialchars($url) . '"' . ($row['style_id'] == $user->data['user_style'] ? ' selected="selected"' : '') . '>' . utf8_htmlspecialchars(sgp_checksize($row['style_name'], 16)) . '</option>';
}
$db->sql_freeresult($result);

if (strlen($style_select))
{
	$template->assign_var('STYLE_SELECT', $style_select);
}

$template->assign_vars(array(
	'STYLE_COUNT'	=> $style_count,
	'S_SHOW_PERM'	=> false,
));
