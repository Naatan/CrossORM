<?php

use \CrossORM\DB;

require_once dirname(__FILE__) . '/db.php';

DB::factory(array(
	'driver'	=> 'pdo',
	'connection_string' => 'mysql:dbname=test;host=127.0.0.1;port=8889',
	'username' => 'root',
	'password' => 'root',
));

$result = DB::factory()->for_table('test')->find_many();

foreach ($result AS $e) {
	var_dump($e->as_array());
}