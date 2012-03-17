<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Add_multiple_group_column extends Migration {

	public function up()
	{
		$fields = array(
			'groups' => array(
				'type' => 'TEXT',
			)
		);
		
		$this->dbforge->add_column('navigation', $fields);
	}
	
	public function down()
	{
		$this->dbforge->drop_column('navigation', 'groups');
	}
}