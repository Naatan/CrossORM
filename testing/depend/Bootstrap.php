<?php

$ds = DIRECTORY_SEPARATOR;
$up = '..' . $ds;

if ( ! defined('BASEDIR'))
{
    define('BASEDIR', realpath(dirname(__FILE__) . $ds . $up . $up . 'library') . $ds);
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Autoloader.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Models.php';

\CrossORM\DB::factory(array(
    'driver'    => 'PDO',
    'connection_string' => 'sqlite:' . dirname(__FILE__) . $ds . 'testing.db'
));
