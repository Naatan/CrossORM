<?php

class Model_Test extends \CrossORM\Core\Model {}

class Model_Test_2 extends \CrossORM\Core\Model {
	
	/* Optional overrides */
	protected $table_name 	= 'test';
	protected $db_id		= 'default';
	
	protected $fields = array(
		'id'			=> array(
			'type'			=> \CrossORM\TYPE_INT // type implies validation
		),
		'name'			=> array(
			'type'			=> \CrossORM\TYPE_TEXT,
			'validation'	=> 'maxlength[255],alphanumeric,name'
		),
		'value'			=> array(
			'type'			=> \CrossORM\TYPE_TEXT,
			'default'		=> NULL
		)
	);

	public function _validate_name($value)
	{
		$blacklist = array('admin', 'moderator', 'god');
		return ! in_array($value, $blacklist);
	}

}