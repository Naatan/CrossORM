<?php

use \CrossORM\DB;

class DeletesTest extends PHPUnit_Framework_TestCase
{

	function test_delete()
	{
		$query = DB::factory()->for_table('test')->find_one();

		echo json_encode(array(
			'query' 		=> $query->get_query(), 
			'result' 		=> $query->as_array(), 
			'delete'		=> $query->delete(),
			'query2'		=> $query->get_query()
		), JSON_NUMERIC_CHECK);	
		
	}

}