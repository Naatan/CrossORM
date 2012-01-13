<?php

namespace CrossORM;

/************************************************** DEFAULTS */

define('ACTOR_DEFAULT',			'default');
define('DB_ID_DEFAULT',			'default');
define('ID_COLUMN',				'id');

/************************************************** KEYWORDS */

define('SELECT',					'select');
define('UPDATE',					'update');
define('INSERT',					'insert');
define('DELETE',					'delete');

define('ASC',					'ASC');
define('DESC',					'DESC');

/************************************************** CONDITIONALS */

define('EQUAL',					'=');
define('NOT_EQUAL',				'=');
define('LIKE',					'LIKE');
define('NOT_LIKE',				'NOT LIKE');
define('GREATER_THAN',			'>');
define('LESS_THAN',				'<');
define('GREATER_THAN_EQUAL',		'>=');
define('LESS_THAN_EQUAL',		'<=');
define('IN',						'IN');
define('NOT_IN',					'NOT IN');
define('IS_NULL',				'IS NULL');
define('IS_NOT_NULL',			'IS NOT NULL');

/************************************************** HELPERS */

define('UNDEFINED',				'!@#--UNDEFINED--!@#');

/************************************************** QUERY STATES */

define('STATE_FRESH',			'fresh');
define('STATE_EXECUTED',			'executed');
define('STATE_HYDRATED',			'hydrated');

/************************************************** ACL MODES */

define('MODE_WHITELIST',			'whitelist');
define('MODE_BLACKLIST',			'blacklist');

/************************************************** ACL RULES */

define('RULE_TYPE_FULL',			'full');
define('RULE_TYPE_TABLE',		'table');
define('RULE_TYPE_TABLES',		'tables');
define('RULE_TYPE_TABLE_ACTIONS','table_actions');
define('RULE_TYPE_FIELD',		'field');
define('RULE_TYPE_FIELDS',		'fields');

/************************************************** VALIDATION */

define('VALIDATE_ON_ALL',		'all');
define('VALIDATE_ON_SET',		'set');
define('VALIDATE_ON_RUN',		'run');

