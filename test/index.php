<?php

use \CrossORM\DB;
use \CrossORM\Model;

require_once dirname(__FILE__) . '/../crossorm.php';

DB::factory(array(
	'driver'	=> 'pdo',
	'connection_string' => PHP_OS == 'Darwin' ? 'mysql:dbname=test;host=127.0.0.1;port=8889' : 'mysql:dbname=test;host=127.0.0.1',
	'username' => 'root',
	'password' => PHP_OS == 'Darwin' ? 'root' : '',
));

$result = DB::factory()->for_table('test')->where_id_is(1)->find_one();
var_dump($result->as_array());

//echo '<hr/>';
//
//$result->value = 'foobar';
//$result->save();