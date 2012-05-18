<?php

namespace CrossORM\Exceptions;

class Validation extends Exception {
	
	private $label;
	private $rule;
	private $args;
	private $input;
	
	function __construct($message = null, $code = 0, \Exception $previous = null)
	{
		
		if (is_object($message) OR is_array($message))
		{
			$message = (object) $message;
			
			foreach (array('label','rule','args','input','message') AS $key)
			{
				isset($message->{$key}) ?: $this->{$key} = $message->{$key};
			}
			
			$message = $message->message;
		}
		
		return parent::__construct($message, $code, $previous);
		
	}
	
	function getLabel()
	{
		return $this->label;
	}
	
	function getRule()
	{
		return $this->rule;
	}
	
	function getArgs()
	{
		return $this->args;
	}
	
	function getInput()
	{
		return $this->input;
	}
	
}