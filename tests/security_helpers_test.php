<?php
/**
 * Security helper tests.
 */

require_once dirname(__DIR__) . '/includes/sgp_functions.php';

class security_helpers_test extends phpbb_test_case
{
	public function test_block_lists_are_normalized()
	{
		$this->assertSame('2,7,3', portal_normalize_block_list('2, 7,2,-1,abc,3'));
		$this->assertSame('', portal_normalize_block_list('../etc/passwd'));
	}

	public function safe_menu_targets()
	{
		return array(
			array('portal.php', 'portal.php'),
			array('app.php/rules', 'app.php/rules'),
			array('https://www.phpbb.com/', 'https://www.phpbb.com/'),
		);
	}

	/**
	 * @dataProvider safe_menu_targets
	 */
	public function test_safe_menu_targets_are_kept($target, $expected)
	{
		$this->assertSame($expected, portal_sanitize_menu_target($target));
	}

	public function unsafe_menu_targets()
	{
		return array(
			array('javascript:alert(1)'),
			array('data:text/html,unsafe'),
			array('../config.php'),
			array("portal.php\nunsafe"),
		);
	}

	/**
	 * @dataProvider unsafe_menu_targets
	 */
	public function test_unsafe_menu_targets_are_rejected($target)
	{
		$this->assertSame('', portal_sanitize_menu_target($target));
	}
}
