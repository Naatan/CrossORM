<?php

use \CrossORM\DB,
	\CrossORM\Security\ACL;

class AclTest extends PHPUnit_Framework_TestCase
{
	
	function set_testing_rules()
	{
		ACL::clear();
		
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
		
		ACL::set_rule($rule);
	}
	
	function set_testing_rules_wildcard()
	{
		ACL::clear();
		
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
		//ACL::set_rule($rule);
		
		$rules = array(
			'tables.test.actions.select',
			'tables.test.fields.*.actions.select',
			'tables.test.fields.*.actions.update'
		);
		
		ACL::set_rules($rules);
	}
	
	function test_set_field_rule()
	{
		$result = array();

		ACL::clear();
		
		$rule =  array(
			array('test', array('id' => array('actions'	=> array('select','update')))),
			array('test', array('name' => array('actions'	=> array('select','update'))))
		);

		ACL::set_rules($rule, \CrossORM\RULE_TYPE_FIELD);
		
		$rules = ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode(array(
			'rule' 		=> $rule, 
			'result' 	=> $rules, 
			'flat' 		=> ACL::get_rules(true)
		), JSON_NUMERIC_CHECK);
	}
	
	function test_set_table_rule()
	{
		ACL::clear();
		
		$rule = array('test',
			array(
				'actions'	=> array('select','insert','update','delete'),
			)
		);
		
		ACL::set_rule($rule, \CrossORM\RULE_TYPE_TABLE);
		
		$rules = ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode(array(
			'rule' 		=> $rule, 
			'result' 	=> $rules, 
			'flat' 		=> ACL::get_rules(true)
		), JSON_NUMERIC_CHECK);
	}
	
	function test_set_full_rule()
	{
		ACL::clear();
		
		$rule =  array(
			'tables'	=> array(
				
				'test'	=> array(
					'actions'	=> array('select')
				)
			)
		);
		
		ACL::set_rule($rule, \CrossORM\RULE_TYPE_FULL);
		
		$rules = ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode(array(
			'rule' 		=> $rule, 
			'result' 	=> $rules, 
			'flat' 		=> ACL::get_rules(true)
		), JSON_NUMERIC_CHECK);
	}
	
	function test_set_mixed_rules()
	{
		ACL::clear();
		
		$field_rule = array(
			array('test', array('id' => array('actions'	=> array('select','update')))),
			array('test', array('name' => array('actions'	=> array('select','update'))))
		);
		
		ACL::set_rules($field_rule, \CrossORM\RULE_TYPE_FIELD);
		
		$table_rule = array('test',
			array(
				'actions'	=> array('update','delete'),
			)
		);
		
		ACL::set_rule($table_rule, \CrossORM\RULE_TYPE_TABLE);
		
		$full_rule =  array(
			'tables'	=> array(
				
				'test'	=> array(
					'actions'	=> array('select')
				)
			)
		);
		
		ACL::set_rule($full_rule);
		
		$rules = ACL::get_rules();
		
		$this->assertObjectHasAttribute('default_default',$rules,'ACL Rules missing default actor at root of object');
		
		echo json_encode(array(
			'field_rule' 		=> $field_rule, 
			'table_rule' 		=> $table_rule, 
			'full_rule' 		=> $full_rule, 
			'result' 			=> $rules, 
			'flat' 				=> ACL::get_rules(true)
		), JSON_NUMERIC_CHECK);
	}
	
	function test_has_permission()
	{
		$this->set_testing_rules();
		
		$rules = ACL::get_rules();
		$actor = ACL::get_actor();
		$result = ACL::has_permission('tables.test.actions.select');

		$this->assertEquals(true,$result);
		
		echo json_encode(array(
			'rules' 		=> $rules, 
			'flat' 			=> ACL::get_rules(true), 
			'actor'			=> $actor,
			'test'			=> 'tables.test.actions.select',
			'result'		=> $result
		), JSON_NUMERIC_CHECK);		
	}
	
	function test_has_permission_blacklist()
	{
		$this->set_testing_rules();
		
		ACL::set_actor(\CrossORM\ACTOR_DEFAULT, \CrossORM\MODE_BLACKLIST);
		
		$rules = ACL::get_rules();
		$actor = ACL::get_actor();
		$result = ACL::has_permission('tables.test.actions.select');
		
		$this->assertEquals(false,$result);

		echo json_encode(array(
			'rules' 		=> $rules, 
			'flat' 			=> ACL::get_rules(true), 
			'actor'			=> $actor,
			'test'			=> 'tables.test.actions.select',
			'result'		=> $result
		), JSON_NUMERIC_CHECK);		
	}
	
	function test_select_query()
	{
		$this->set_testing_rules_wildcard();
		
		$rules = ACL::get_rules();
		$actor = ACL::get_actor();

		$result = DB::factory()->acl()->for_table('test')->find_one();

		$this->assertArrayHasKey('id',$result->as_array());

		echo json_encode(array(
			'rules' 		=> $rules, 
			'flat' 			=> ACL::get_rules(true), 
			'actor'			=> $actor,
			'result'		=> $result->as_array()
		), JSON_NUMERIC_CHECK);	
	}
	
	function test_select_query_blacklist()
	{
		$this->set_testing_rules_wildcard();
		
		ACL::set_actor(\CrossORM\ACTOR_DEFAULT, \CrossORM\MODE_BLACKLIST);
		
		$rules = ACL::get_rules();
		$actor = ACL::get_actor();
		
		try {
			$result = DB::factory()->acl()->for_table('test')->find_one();
			$this->fail('ACL should cause failure');
		}
			catch(\CrossORM\Exceptions\ACL $e)
		{
			echo json_encode(array(
				'rules' 		=> $rules, 
				'flat' 			=> ACL::get_rules(true), 
				'actor'			=> $actor,
				'result'		=> stripslashes(json_encode($e))
			), JSON_NUMERIC_CHECK);	
		}
		
	}

}