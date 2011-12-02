<?php

class Model_Test extends \CrossORM\Model {}

class Model_Test_2 extends \CrossORM\Model {
	/* Optional overrides */
	protected $table_name 	= 'test';
	protected $db_id		= 'default';
}