<?php

namespace CrossORM;

class Model {
	
	public static $class_prefix = '\\Model_';
	public static $class_suffix = '';
	
	
	public static function factory($keyword, $id = null)
	{
		$table_name = self::_get_table_name($keyword);
		$class_name = self::_get_class_name($keyword);
		
		$orm = DB::factory($id);
		$orm->table($table_name);
		
		$model = new $class_name;
		$model->orm = $orm;
		
		return $model;
	}
	
	protected static function _get_class_name($class_name) {
		return static::$class_prefix . $class_name . static::$class_suffix;
	}
	
	/**
	 * Static method to get a table name given a class name.
	 * If the supplied class has a public static property
	 * named $_table, the value of this property will be
	 * returned. If not, the class name will be converted using
	 * the _class_name_to_table_name method method.
	 */
	protected static function _get_table_name($keyword)
	{
		$specified_table_name = self::_get_static_property(self::_get_class_name($keyword), '_table');
		if (is_null($specified_table_name))
		{
			return self::_class_name_to_table_name($keyword);
		}
		return $specified_table_name;
	}
	
	/**
	 * Static method to convert a class name in CapWords
	 * to a table name in lowercase_with_underscores.
	 * For example, CarTyre would be converted to car_tyre.
	 */
	protected static function _class_name_to_table_name($keyword)
	{
		return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $keyword));
	}
	
	/**
	 * Retrieve the value of a static property on a class. If the
	 * class or the property does not exist, returns the default
	 * value supplied as the third argument (which defaults to null).
	 */
	protected static function _get_static_property($keyword, $property, $default=null)
	{
		if (!class_exists($keyword) || !property_exists($keyword, $property))
		{
			return $default;
		}
		$properties = get_class_vars($keyword);
		return $properties[$property];
	}

	public function __call($method,$args)
	{
		return call_user_func_array(array($this->model,$method),$args);
	}
	
}