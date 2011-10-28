<?php

use \CrossORM\DB;
use \CrossORM\Model;

require_once dirname(__FILE__) . '/../crossorm.php';

DB::factory(array(
	'driver'	=> 'pdo',
	'connection_string' => 'mysql:dbname=test;host=127.0.0.1',
	'username' => 'root',
	'password' => '',
));

class Model_testx extends \CrossORM\Drivers\PDO\Model {
	
	public static $_table = 'test';	
	
}

//$result = DB::factory()->for_table('test')->find_many();
$result = Model::factory('testx')->find_many();

foreach ($result AS $e) {
	var_dump($e->as_array());
}