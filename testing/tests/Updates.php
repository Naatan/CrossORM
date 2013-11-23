<?php

use \CrossORM\DB;

class UpdatesTest extends PHPUnit_Framework_TestCase
{

    function test_update_one()
    {
        $data = array();

        $result = DB::factory()->for_table('test')->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        $data['before_data'] = $result->as_array();
        $data['before_query']= $result->get_query();

        $result->name = uniqid();
        $result->save();

        $data['after_data'] = $result->as_array();
        $data['after_query']= $result->get_query();

        echo json_encode($data, JSON_NUMERIC_CHECK);
    }

    function test_update_one_model()
    {
        $data = array();

        $result = Model_Test_2::factory()->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        $data['before_data'] = $result->as_array();
        $data['before_query']= $result->get_query();

        $result->name = uniqid();
        $result->save();

        $data['after_data'] = $result->as_array();
        $data['after_query']= $result->get_query();

        echo json_encode($data, JSON_NUMERIC_CHECK);
    }

    function test_update_many()
    {
        $result = DB::factory()->for_table('test')->where_like('name','4ed%')->limit(3);

        $result->name = uniqid();
        $result->save();

        echo json_encode(array(
            'update_query'      => $result->get_query(),
            'result_data'       => $result->as_array(),
            'select_query'      => $result->get_query(),
        ), JSON_NUMERIC_CHECK);
    }

    function test_update_resultset()
    {
        $data = array();

        $result = DB::factory()->for_table('test')->where_like('name','4ed%')->limit(3)->find_many();

        $data['before_data'] = $result->as_array();
        $data['query'] = $result->get_query();

        $result->name = uniqid();
        $result->save();

        $data['method_queries'] = $result->get_queries();
        $data['after_data'] = $result->as_array();

        echo json_encode($data, JSON_NUMERIC_CHECK);
    }

}
