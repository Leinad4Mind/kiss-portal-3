<?php
/**
 * Read-only poll block for phpBB 3.3.
 *
 * Voting remains on viewtopic.php so phpBB performs its normal permission,
 * CSRF and vote-change checks.
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

global $auth, $db, $k_config, $phpbb_root_path, $phpEx, $template, $user;

$template->assign_var('BS_HAS_POLL', false);
$post_id = isset($k_config['k_poll_post_id']) ? (int) $k_config['k_poll_post_id'] : 0;
if (!$post_id)
{
	return;
}

$sql_array = array(
	'SELECT' => 't.topic_id, t.forum_id, t.poll_title, t.poll_start, t.poll_length,
		t.poll_max_options, p.bbcode_uid, p.bbcode_bitfield, p.enable_bbcode,
		p.enable_smilies, p.enable_magic_url,
		f.forum_password',
	'FROM' => array(
		TOPICS_TABLE => 't',
	),
	'LEFT_JOIN' => array(
		array(
			'FROM' => array(POSTS_TABLE => 'p'),
			'ON' => 'p.topic_id = t.topic_id',
		),
		array(
			'FROM' => array(FORUMS_TABLE => 'f'),
			'ON' => 'f.forum_id = t.forum_id',
		),
	),
	'WHERE' => $db->sql_in_set('p.post_id', array($post_id)) . '
		AND t.poll_start > 0
		AND t.topic_visibility = ' . ITEM_APPROVED . '
		AND p.post_visibility = ' . ITEM_APPROVED,
);

$result = $db->sql_query($db->sql_build_query('SELECT', $sql_array));
$poll = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$poll
	|| !$auth->acl_get('f_read', (int) $poll['forum_id'])
	|| !$auth->acl_get('f_list', (int) $poll['forum_id'])
	|| !empty($poll['forum_password']))
{
	return;
}

$sql = 'SELECT poll_option_id, poll_option_text, poll_option_total
	FROM ' . POLL_OPTIONS_TABLE . '
	WHERE topic_id = ' . (int) $poll['topic_id'] . '
	ORDER BY poll_option_id';
$result = $db->sql_query($sql);
$options = array();
$total_votes = 0;
while ($option = $db->sql_fetchrow($result))
{
	$options[] = $option;
	$total_votes += (int) $option['poll_option_total'];
}
$db->sql_freeresult($result);

if (!$options)
{
	return;
}

$text_options = ($poll['enable_bbcode'] ? OPTION_FLAG_BBCODE : 0)
	| ($poll['enable_smilies'] ? OPTION_FLAG_SMILIES : 0)
	| ($poll['enable_magic_url'] ? OPTION_FLAG_LINKS : 0);
$poll_title = generate_text_for_display(
	$poll['poll_title'],
	$poll['bbcode_uid'],
	$poll['bbcode_bitfield'],
	$text_options
);

foreach ($options as $option)
{
	$votes = (int) $option['poll_option_total'];
	$percentage = $total_votes ? round(($votes / $total_votes) * 100, 1) : 0;
	$template->assign_block_vars('b_poll_option', array(
		'POLL_OPTION_ID' => (int) $option['poll_option_id'],
		'POLL_OPTION_CAPTION' => generate_text_for_display(
			$option['poll_option_text'],
			$poll['bbcode_uid'],
			$poll['bbcode_bitfield'],
			$text_options
		),
		'POLL_OPTION_RESULT' => $votes,
		'POLL_OPTION_PERCENT' => $percentage . '%',
		'POLL_OPTION_PCT' => $percentage,
	));
}

$poll_end = (int) $poll['poll_length'] ? (int) $poll['poll_start'] + (int) $poll['poll_length'] : 0;
$view_topic = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . (int) $poll['forum_id'] . '&amp;t=' . (int) $poll['topic_id']);

$template->assign_vars(array(
	'BPOLL_QUESTION' => $poll_title,
	'BTOTAL_VOTES' => $total_votes,
	'BL_POLL_LENGTH' => $poll_end
		? sprintf($user->lang[$poll_end > time() ? 'POLL_RUN_TILL' : 'POLL_ENDED_AT'], $user->format_date($poll_end))
		: '',
	'BS_HAS_POLL' => true,
	'BU_VIEW_RESULTS' => $view_topic . '&amp;view=viewpoll',
));
