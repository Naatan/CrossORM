<?php

class Model_Test extends \CrossORM\Model {}

class Model_Test_2 extends \CrossORM\Model {
	
	/* Optional overrides */
	protected $table_name 	= 'test';
	protected $db_id		= 'default';
	
	protected $fields = array(
		'id'			=> array(
			'type'			=> 'int' // type implies validation
		),
		'name'			=> array(
			'type'			=> 'text',
			'validation'	=> 'maxlength[255],alphanumeric,name'
		)
	);

	public function _validate_name($value)
	{
		$blacklist = array('admin','moderator','god');
		return ! in_array($value,$blacklist);
	}

}