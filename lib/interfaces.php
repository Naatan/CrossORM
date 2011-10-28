<?php

namespace CrossORM\Interfaces;

interface ORM {
	
	static function for_table($table_name);
	static function set_db($db);
	static function get_db();
	static function get_last_query();
	static function get_query_log();
	function create($data=null);
	function use_id_column($id_column);
	function find_one($id=null);
	function find_many();
	function count();
	function force_all_dirty();
	function raw_query($query, $parameters);
	function table_alias($alias);
	function select($column, $alias=null);
	function join($table, $constraint, $table_alias=null);
	function where($column_name, $value);
	function where_equal($column_name, $value);
	function where_not_equal($column_name, $value);
	function where_id_is($id);
	function where_like($column_name, $value);
	function where_not_like($column_name, $value);
	function where_gt($column_name, $value);
	function where_lt($column_name, $value);
	function where_gte($column_name, $value);
	function where_lte($column_name, $value);
	function where_in($column_name, $values);
	function where_not_in($column_name, $values);
	function where_null($column_name);
	function where_not_null($column_name);
	function where_raw($clause, $parameters=array());
	function limit($limit);
	function offset($offset);
	function order_by_desc($column_name);
	function order_by_asc($column_name);
	function group_by($column_name);
	function as_array();
	function get($key);
	function id();
	function set($key, $value);
	function is_dirty($key);
	function save();
	function delete();
	
}

interface Model {
	
	function has_one($associated_class_name, $foreign_key_name=null);
	function has_many($associated_class_name, $foreign_key_name=null);
	function belongs_to($associated_class_name, $foreign_key_name=null);
	function has_many_through($associated_class_name, $join_class_name=null, $key_to_base_table=null, $key_to_associated_table=null);
	function set_orm($orm);
	function get($property);
	function set($property, $value);
	function is_dirty($property);
	function as_array();
	function save();
	function delete();
	function id();
	
}