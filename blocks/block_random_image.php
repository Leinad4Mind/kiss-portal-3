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

global $k_config, $phpbb_root_path, $k_blocks;

$randomimage = '';
$block_centred = false;
$rand_folder = $phpbb_root_path . 'ext/phpbbireland/portal/images/random_images';
$rand_web_path = $phpbb_root_path . 'ext/phpbbireland/portal/images/random_images/';

foreach ($k_blocks as $blk)
{
	if ($blk['html_file_name'] == 'block_random_image.html')
	{
		$block_centred = ($blk['position'] == 'C') ? true : false;
		break;
	}
}

// quick report because people forget to add the image directory //
if (!is_dir($rand_folder) || !is_readable($rand_folder))
{
	$template->assign_vars(array(
		'RANDOM_ERROR'    => true,
		'MISSING_FOLDER'  => sprintf($user->lang['MISSING_FOLDER'], $rand_folder),
	));
	return;
}

$images = array();
foreach (new \DirectoryIterator($rand_folder) as $file_info)
{
	if ($file_info->isFile() && preg_match('/\.(?:gif|jpe?g|png|webp)$/i', $file_info->getFilename()))
	{
		$images[] = $file_info->getFilename();
	}
}

if ($images)
{
	$image = $images[random_int(0, count($images) - 1)];
	$randomimage = '<img src="' . utf8_htmlspecialchars($rand_web_path . rawurlencode($image)) . '" alt="" />';
}

$template->assign_vars(array(
	'RANDOMIMAGE'         => $randomimage,
	'S_BLOCK_IS_CENTRED'  => ($block_centred) ? true : false,
));
