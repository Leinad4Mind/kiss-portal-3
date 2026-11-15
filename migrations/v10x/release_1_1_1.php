<?php
/**
 * Correct the historical GitHub link seeded by release 1.0.2.
 */

namespace phpbbireland\portal\migrations\v10x;

class release_1_1_1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbbireland\portal\migrations\v10x\release_1_1_0');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('portal_version', '1.1.1')),
			array('config.update', array('portal_build', '3319-002')),
			array('config.update', array('portal_mod_version', '1.1.1')),
			array('custom', array(array($this, 'update_github_link'))),
		);
	}

	public function update_github_link()
	{
		$table = $this->table_prefix . 'k_link_images';
		$sql = 'UPDATE ' . $table . '
			SET ' . $this->db->sql_build_array('UPDATE', array(
				'link' => 'Kiss Portal GitHub',
				'url' => 'https://github.com/Leinad4Mind/portal',
				'image' => 'www.phpbbireland+kiss2.gif',
			)) . '
			WHERE ' . $this->db->sql_in_set('link', array('github')) . '
				OR ' . $this->db->sql_in_set('url', array('github.com/phpbbireland/portal'));
		$this->db->sql_query($sql);
	}
}
