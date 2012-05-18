<?php

namespace CrossORM;

class Helpers {
	
	/**
	 * Merge two objects recursively, can also input an array, basically enforces
	 * a json style array/object structure after merging both inputs as arrays
	 * 
	 * @param	object|array			$ob
	 * @param	object|array			$ob2
	 * 
	 * @return	object|array
	 */
	public static function object_merge(&$ob,$ob2)
	{
		$ob = static::objectify(
			array_merge_recursive(
				Helpers::object_to_array($ob),
				Helpers::object_to_array($ob2)
			)
		);
	}
	
	/**
	 * Turn array / object structure into json style structure
	 * 
	 * @param	array|object			$ob
	 * 
	 * @return	array|object			
	 */
	public static function objectify($ob)
	{
		return json_decode(json_encode($ob));
	}
	
	/**
	 * Convert object to array recursively
	 * 
	 * @param	object|array			$ob
	 * 
	 * @return	array
	 */
	public static function object_to_array($ob)
	{
		if (!is_array($ob) AND !is_object($ob))
		{
			return $ob;
		}
		
		$ob = (array) $ob;
		
		foreach ($ob AS $k => $v)
		{
			$ob[$k] = static::object_to_array($ob[$k]);
		}
		
		return $ob;
	}
	
	/**
	 * Flatten array, if input contains an object it will be converted to an array
	 * 
	 * @param	array|object			$array			
	 * @param	string					$parents
	 * 
	 * @return	array			
	 */
	public static function flatten_array($array, $parents = '')
	{
		if ( !is_array($array) AND !is_object($array))
		{
			return array($parents . $array);
		}
		
		if (empty($parents))
		{
			$array 	= static::object_to_array($array);
		}
		
		$flat 	= array();
		
		foreach ($array AS $k => $v)
		{
			if (is_numeric($k))
			{
				$k = '';
				$p = $parents;
			} else
			{
				$p = $parents . $k . '.';
				$flat[] = $parents . $k;
			}
			
			$flat = array_merge($flat,static::flatten_array($v,$p));
		}
		
		return $flat;
	}
	
}