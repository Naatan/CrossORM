<?php

use \CrossORM\DB;

require_once dirname(__FILE__) . '/../../crossorm.php';
require_once dirname(__FILE__) . '/_models.php';

class Selects extends PHPUnit_Framework_TestCase
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
	
	function test_find_one()
	{
		$result = DB::factory()->for_table('test')->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo json_encode($result->as_array());
	}
	
	function test_find_many()
	{
		$result = DB::factory()->for_table('test')->find_many();
		
		$this->assertGreaterThan(0,$result->count());
		
		echo $result->as_json();
	}
	
	function test_as_array()
	{
		$result = DB::factory()->for_table('test')->as_array();
		
		$this->assertArrayHasKey(0,$result);
		$this->assertArrayHasKey(1,$result);
		
		echo json_encode($result);
	}
	
	function test_where_id_is()
	{
		$result = DB::factory()->for_table('test')->find_one();
		$result = DB::factory()->for_table('test')->where_id_is($result->id())->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo json_encode($result->as_array());
	}
	
	function test_model()
	{
		$result = Model_Test::factory()->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo json_encode($result->as_array());
	}
	
	function test_model_without_factory()
	{
		$Test = new Model_Test();
		$result = $Test->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo json_encode($result->as_array());
	}
	
	function test_model_2()
	{
		$result = DB::factory()->for_table('test')->find_one();
		$result = Model_Test_2::factory()->where_id_is($result->id())->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo json_encode($result->as_array());
	}

}