<?php

use \CrossORM\DB;

class InsertsTest extends PHPUnit_Framework_TestCase
{
	
	function test_insert()
	{
		$query = DB::factory()->for_table('test');
		$query->name = 'test';
		$query->save();

		echo json_encode(array(
			'query' 		=> $query->get_query(), 
			'result' 		=> $query->as_array(), 
			'query2'		=> $query->get_query()
		), JSON_NUMERIC_CHECK);	
	}

}