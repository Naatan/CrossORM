<?php

namespace CrossORM\Interfaces;

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