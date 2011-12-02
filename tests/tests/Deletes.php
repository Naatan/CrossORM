<?php

use \CrossORM\DB;

require_once dirname(__FILE__) . '/../../crossorm.php';
require_once dirname(__FILE__) . '/_models.php';

class Deletes extends PHPUnit_Framework_TestCase
{
	
	function __construct()
	{
		DB::factory(array(
			'driver'	=> 'pdo',
			'connection_string' => PHP_OS == 'Darwin' ? 'mysql:dbname=test;host=127.0.0.1;port=8889' : 'mysql:dbname=test;host=127.0.0.1',
			'username' => 'root',
			'password' => PHP_OS == 'Darwin' ? 'root' : '',
		));
	}
	
	function test_delete()
	{
		$query = DB::factory()->for_table('test')->find_one();
		
		echo json_encode($query->get_query());
		
		echo '-bit-'."\n";
		
		echo json_encode($query->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($query->delete());
		
		echo '-bit-'."\n";
		
		echo json_encode($query->get_query());
		
	}

}