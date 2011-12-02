<?php

use \CrossORM\DB;

require_once dirname(__FILE__) . '/../../crossorm.php';

class Acl extends PHPUnit_Framework_TestCase
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
	
	function set_testing_rules()
	{
		\CrossORM\ACL::clear();
		
		$rule =  array(
			'tables'	=> array(
				
				'test'	=> array(
					'actions'	=> array('select'),
					'fields'	=> array(
						array('id' => array('actions'	=> array('select','update'))),
						array('name' => array('actions'	=> array('select','update')))
					)
				)
			)
		);
		
		\CrossORM\ACL::set_rule($rule);
	}
	
	function set_testing_rules_wildcard()
	{
		\CrossORM\ACL::clear();
		
		//$rule =  array(
		//	'tables'	=> array(
		//		
		//		'test'	=> array(
		//			'actions'	=> array('select'),
		//			'fields'	=> array(
		//				array('*' => array('actions'	=> array('select','update'))),
		//			)
		//		)
		//	)
		//);
		//
		//\CrossORM\ACL::set_rule($rule);
		
		$rules = array(
			'tables.test.actions.select',
			'tables.test.fields.*.actions.select',
			'tables.test.fields.*.actions.update'
		);
		
		\CrossORM\ACL::set_rules($rules);
	}
	
	function test_set_field_rule()
	{
		\CrossORM\ACL::clear();
		
		$rule =  array(
			array('test', array('id' => array('actions'	=> array('select','update')))),
			array('test', array('name' => array('actions'	=> array('select','update'))))
		);
		
		echo "Rule:-bit-";
		echo json_encode($rule, JSON_NUMERIC_CHECK);
		echo "-bit-Result:-bit-";
		
		\CrossORM\ACL::set_rules($rule, RULE_TYPE_FIELD);
		
		$rules = \CrossORM\ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
	}
	
	function test_set_table_rule()
	{
		\CrossORM\ACL::clear();
		
		$rule = array('test',
			array(
				'actions'	=> array('select','insert','update','delete'),
			)
		);
		
		echo "Rule:-bit-";
		echo json_encode($rule, JSON_NUMERIC_CHECK);
		echo "-bit-";
		echo "Result:-bit-";
		
		\CrossORM\ACL::set_rule($rule,RULE_TYPE_TABLE);
		
		$rules = \CrossORM\ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
	}
	
	function test_set_full_rule()
	{
		\CrossORM\ACL::clear();
		
		$rule =  array(
			'tables'	=> array(
				
				'test'	=> array(
					'actions'	=> array('select')
				)
			)
		);
		
		echo "Rule:-bit-";
		echo json_encode($rule, JSON_NUMERIC_CHECK);
		echo "-bit-";
		echo "Result:-bit-";
		
		\CrossORM\ACL::set_rule($rule, RULE_TYPE_FULL);
		
		$rules = \CrossORM\ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
	}
	
	function test_set_mixed_rules()
	{
		\CrossORM\ACL::clear();
		
		$rule = array(
			array('test', array('id' => array('actions'	=> array('select','update')))),
			array('test', array('name' => array('actions'	=> array('select','update'))))
		);
		
		echo "FIELD Rule:-bit-";
		echo json_encode($rule, JSON_NUMERIC_CHECK);
		echo "-bit-";
		
		\CrossORM\ACL::set_rules($rule, RULE_TYPE_FIELD);
		
		$rule = array('test',
			array(
				'actions'	=> array('update','delete'),
			)
		);
		
		echo "TABLE Rule:-bit-";
		echo json_encode($rule, JSON_NUMERIC_CHECK);
		echo "-bit-";
		
		\CrossORM\ACL::set_rule($rule,RULE_TYPE_TABLE);
		
		$rule =  array(
			'tables'	=> array(
				
				'test'	=> array(
					'actions'	=> array('select')
				)
			)
		);
		
		echo "FULL Rule:-bit-";
		echo json_encode($rule, JSON_NUMERIC_CHECK);
		echo "-bit-";
		
		\CrossORM\ACL::set_rule($rule);
		
		echo "Result:-bit-";
		
		$rules = \CrossORM\ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
	}
	
	function test_has_permission()
	{
		$this->set_testing_rules();
		
		echo "Rules:-bit-";
		
		$rules = \CrossORM\ACL::get_rules();
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
		
		echo "-bit-Actor:-bit-";
		
		$actor = \CrossORM\ACL::get_actor();
		echo json_encode($actor, JSON_NUMERIC_CHECK);
		
		echo "-bit-has permission 'tables.test.actions.select':-bit-";
		
		$result = \CrossORM\ACL::has_permission('tables.test.actions.select');
		
		$this->assertEquals(true,$result);
		
		echo "Result:-bit-";
		
		echo json_encode($result, JSON_NUMERIC_CHECK);
	}
	
	function test_has_permission_blacklist()
	{
		$this->set_testing_rules();
		
		\CrossORM\ACL::set_actor(ACTOR_DEFAULT,MODE_BLACKLIST);
		
		echo "Rules:-bit-";
		
		$rules = \CrossORM\ACL::get_rules();
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
		
		echo "-bit-Actor:-bit-";
		
		$actor = \CrossORM\ACL::get_actor();
		echo json_encode($actor, JSON_NUMERIC_CHECK);
		
		echo "-bit-has permission 'tables.test.actions.select':-bit-";
		
		$result = \CrossORM\ACL::has_permission('tables.test.actions.select');
		
		//$this->assertEquals(false,$result);
		
		echo "Result:-bit-";
		
		echo json_encode($result, JSON_NUMERIC_CHECK);
	}
	
	function test_select_query()
	{
		$this->set_testing_rules_wildcard();
		
		echo "Rules:-bit-";
		
		$rules = \CrossORM\ACL::get_rules();
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
		
		echo "-bit-Actor:-bit-";
		
		$actor = \CrossORM\ACL::get_actor();
		echo json_encode($actor, JSON_NUMERIC_CHECK);
		
		echo "-bit-Result:-bit-";
		
		$result = DB::factory()->acl()->for_table('test')->find_one();
		$this->assertArrayHasKey('id',$result->as_array());
		
		echo json_encode($result->as_array(), JSON_NUMERIC_CHECK);
	}
	
	function test_select_query_blacklist()
	{
		$this->set_testing_rules_wildcard();
		
		\CrossORM\ACL::set_actor(ACTOR_DEFAULT,MODE_BLACKLIST);
		
		echo "Rules:-bit-";
		
		$rules = \CrossORM\ACL::get_rules();
		echo json_encode($rules, JSON_NUMERIC_CHECK);
		
		echo "-bit-Flat:-bit-";
		
		echo json_encode(\CrossORM\ACL::get_rules(true), JSON_NUMERIC_CHECK);
		
		echo "-bit-Actor:-bit-";
		
		$actor = \CrossORM\ACL::get_actor();
		echo json_encode($actor, JSON_NUMERIC_CHECK);
		
		echo "-bit-Result:-bit-";
		
		try {
			$result = DB::factory()->acl()->for_table('test')->find_one();
			$this->fail('ACL should cause failure');
		}
			catch(\CrossORM\Exceptions\ACL $e)
		{
			echo json_encode($e);
		}
	}

}