<?php

use \CrossORM\DB;

require_once dirname(__FILE__) . '/../../crossorm.php';
require_once dirname(__FILE__) . '/_models.php';

class Updates extends PHPUnit_Framework_TestCase
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
	
	function test_update_one()
	{
		$result = DB::factory()->for_table('test')->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo 'Before:-bit-';
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->get_query());
		
		echo '-bit-After:-bit-';
		
		$result->name = uniqid();
		$result->save();
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->get_query());
	}
	
	function test_update_one_model()
	{
		$result = Model_Test_2::factory()->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo 'Before:-bit-';
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->get_query());
		
		echo '-bit-After:-bit-';
		
		$result->name = uniqid();
		$result->save();
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->get_query());
	}
	
	function test_update_many()
	{
		$result = DB::factory()->for_table('test')->where_like('name','4ed%')->limit(3);
		
		$result->name = uniqid();
		$result->save();
		
		echo json_encode($result->get_query());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->get_query());
	}
	
	function test_update_resultset()
	{
		$result = DB::factory()->for_table('test')->where_like('name','4ed%')->limit(3)->find_many();
		
		echo 'Before:-bit-';
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		$mr = $result->get_query()->_get_method_results();
		echo json_encode($mr[0]);
		
		echo '-bit-After:-bit-';
		
		$result->name = uniqid();
		$result->save();
		
		echo json_encode($result->as_array());
		
		echo '-bit-'."\n";
		
		echo json_encode($result->get_query()->_get_method_results());
	}

}