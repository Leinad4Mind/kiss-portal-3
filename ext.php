<?php
/**
*
* @package Kiss Portal extension for the phpBB Forum Software package 1.0.1
*
* @copyright (c) 2014 Michael O’Toole <http://www.phpbbireland.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

// This file is needed for phpBB3 to properly register the extension

namespace phpbbireland\portal;

class ext extends \phpbb\extension\base
{
	/**
	 * Only enable this release on the platform versions it was audited against.
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		return PHP_VERSION_ID >= 70400
			&& defined('PHPBB_VERSION')
			&& phpbb_version_compare(PHPBB_VERSION, '3.3.19', '>=')
			&& phpbb_version_compare(PHPBB_VERSION, '4.0.0-dev', '<');
	}
}
