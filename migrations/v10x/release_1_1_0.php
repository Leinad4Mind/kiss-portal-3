<?php
/**
 * phpBB 3.3.19 compatibility and feature cleanup migration.
 */

namespace phpbbireland\portal\migrations\v10x;

class release_1_1_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbbireland\portal\migrations\v10x\release_1_0_2');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('portal_version', '1.1.0')),
			array('config.update', array('portal_build', '3319-001')),
			array('config.update', array('portal_mod_version', '1.1.0')),
			array('custom', array(array($this, 'add_feature_defaults'))),
			array('module.remove', array(
				'ucp',
				'UCP_PORTAL_TITLE',
				array(
					'module_basename' => '\phpbbireland\portal\ucp\portal_module',
					'modes' => array('width'),
				),
			)),
		);
	}

	public function add_feature_defaults()
	{
		$defaults = array(
			'use_block_cookies' => '1',
			'k_poll_post_id' => '0',
			'k_poll_view' => '0',
			'k_poll_wide' => '0',
		);
		$table = $this->table_prefix . 'k_vars';

		$sql = 'SELECT config_name FROM ' . $table . '
			WHERE ' . $this->db->sql_in_set('config_name', array_keys($defaults));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			unset($defaults[$row['config_name']]);
		}
		$this->db->sql_freeresult($result);

		$rows = array();
		foreach ($defaults as $name => $value)
		{
			$rows[] = array('config_name' => $name, 'config_value' => $value);
		}
		if ($rows)
		{
			$this->db->sql_multi_insert($table, $rows);
		}
	}
}
