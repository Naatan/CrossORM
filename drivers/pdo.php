<?php

namespace CrossORM\Drivers;

use CrossORM\DB;

class pdo extends \Idiorm\ORM {
	
	protected $_conn;
	
	public function __construct($config)
	{
		
		static::configure($config);
		static::_setup_db();
		
		$this->_conn = parent::get_db();
		
	}
	
	public static function configure($key, $value=null) {
		if (is_array($key))
		{
			return static::$_config = array_merge(static::$_config,$key);
		}
		
		if (is_null($value))
		{
			$value = $key;
			$key = 'connection_string';
		}
		static::$_config[$key] = $value;
	}
	
	public static function get_db() {
		return DB::factory()->connection();
	}
	
	public function connection() {
		return $this->_conn;
	}
	
	public static function for_table($table_name) {
		return DB::factory()->table($table_name);
	}
	
	public function table($table_name) {
		$this->_table_name = $table_name;
		return $this;
	}
	
}