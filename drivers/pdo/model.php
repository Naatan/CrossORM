<?php

namespace CrossORM\Drivers\PDO;

use CrossORM\DB;

class Model extends \Paris\Model implements \CrossORM\Interfaces\Model
{
	
	public function __call($method,$args)
	{
		return call_user_func_array(array($this->orm,$method),$args);
	}
	
}