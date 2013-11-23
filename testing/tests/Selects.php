<?php

use \CrossORM\DB;

class SelectsTest extends PHPUnit_Framework_TestCase
{

    function test_find_one()
    {
        $result = DB::factory()->for_table('test')->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

    function test_find_many()
    {
        $result = DB::factory()->for_table('test')->find_many();

        $this->assertGreaterThan(0,$result->count());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

    function test_as_array()
    {
        $result = DB::factory()->for_table('test')->as_array();

        $this->assertArrayHasKey(0,$result);
        $this->assertArrayHasKey(1,$result);

        echo json_encode(array(
            'result'        => $result,
        ), JSON_NUMERIC_CHECK);
    }

    function test_where_id_is()
    {
        $result = DB::factory()->for_table('test')->find_one();
        $result = DB::factory()->for_table('test')->where_id_is($result->id())->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

    function test_dynamic_method()
    {
        $Test = new Model_Test_2();
        $result = $Test->find_one();

        $Test = new Model_Test_2();
        $result = $Test->where_id($result->id())->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

    function test_model()
    {
        $result = Model_Test::factory()->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

    function test_model_without_factory()
    {
        $Test = new Model_Test();
        $result = $Test->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

    function test_model_2()
    {
        $result = DB::factory()->for_table('test')->find_one();
        $result = Model_Test_2::factory()->where_id_is($result->id())->find_one();
        $this->assertArrayHasKey('id',$result->as_array());

        echo json_encode(array(
            'result'        => $result->as_array(),
            'query'         => $result->get_query()
        ), JSON_NUMERIC_CHECK);
    }

}
