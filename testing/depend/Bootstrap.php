<?php

$ds = DIRECTORY_SEPARATOR;
$up = '..' . $ds;

define('BASEDIR', realpath(dirname(__FILE__) . $ds . $up . $up . 'library') . $ds);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Autoloader.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Models.php';

\CrossORM\DB::factory(array(
	'driver'	=> 'pdo',
	'connection_string' => 'mysql:dbname=test;host=127.0.0.1',
	'username' => 'root',
	'password' => '',
));