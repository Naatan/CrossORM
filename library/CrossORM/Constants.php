<?php

namespace CrossORM;

/************************************************** DEFAULTS */

define(__NAMESPACE__ . '\ACTOR_DEFAULT',		'default');
define(__NAMESPACE__ . '\DB_ID_DEFAULT',		'default');
define(__NAMESPACE__ . '\ID_COLUMN',			'id');

/************************************************** KEYWORDS */

define(__NAMESPACE__ . '\SELECT',				'select');
define(__NAMESPACE__ . '\UPDATE',				'update');
define(__NAMESPACE__ . '\INSERT',				'insert');
define(__NAMESPACE__ . '\DELETE',				'delete');

define(__NAMESPACE__ . '\ASC',					'ASC');
define(__NAMESPACE__ . '\DESC',					'DESC');

define(__NAMESPACE__ . '\EMPTY',				'CORM{NULL}');

/************************************************** CONDITIONALS */

define(__NAMESPACE__ . '\EQUAL',				'=');
define(__NAMESPACE__ . '\NOT_EQUAL',			'=');
define(__NAMESPACE__ . '\LIKE',					'LIKE');
define(__NAMESPACE__ . '\NOT_LIKE',				'NOT LIKE');
define(__NAMESPACE__ . '\GREATER_THAN',			'>');
define(__NAMESPACE__ . '\LESS_THAN',			'<');
define(__NAMESPACE__ . '\GREATER_THAN_EQUAL',	'>=');
define(__NAMESPACE__ . '\LESS_THAN_EQUAL',		'<=');
define(__NAMESPACE__ . '\IN',					'IN');
define(__NAMESPACE__ . '\NOT_IN',				'NOT IN');
define(__NAMESPACE__ . '\IS_NULL',				'IS NULL');
define(__NAMESPACE__ . '\IS_NOT_NULL',			'IS NOT NULL');

/************************************************** HELPERS */

define(__NAMESPACE__ . '\UNDEFINED',			'!@#--UNDEFINED--!@#');

/************************************************** QUERY STATES */

define(__NAMESPACE__ . '\STATE_FRESH',			'fresh');
define(__NAMESPACE__ . '\STATE_EXECUTED',		'executed');
define(__NAMESPACE__ . '\STATE_HYDRATED',		'hydrated');

/************************************************** ACL MODES */

define(__NAMESPACE__ . '\MODE_WHITELIST',		'whitelist');
define(__NAMESPACE__ . '\MODE_BLACKLIST',		'blacklist');

/************************************************** ACL RULES */

define(__NAMESPACE__ . '\RULE_TYPE_FULL',		'full');
define(__NAMESPACE__ . '\RULE_TYPE_TABLE',		'table');
define(__NAMESPACE__ . '\RULE_TYPE_TABLES',		'tables');
define(__NAMESPACE__ . '\RULE_TYPE_TABLE_ACTIONS','table_actions');
define(__NAMESPACE__ . '\RULE_TYPE_FIELD',		'field');
define(__NAMESPACE__ . '\RULE_TYPE_FIELDS',		'fields');

/************************************************** VALIDATION */

define(__NAMESPACE__ . '\VALIDATE_ON_ALL',		'all');
define(__NAMESPACE__ . '\VALIDATE_ON_SET',		'set');
define(__NAMESPACE__ . '\VALIDATE_ON_RUN',		'run');

/************************************************** FIELD TYPE */

define(__NAMESPACE__ . '\TYPE_TEXT',			'text');
define(__NAMESPACE__ . '\TYPE_INT',				'int');
define(__NAMESPACE__ . '\TYPE_UNIX_TIMESTAMP',	'unix_timestamp');
define(__NAMESPACE__ . '\TYPE_DATETIME',		'datetime');

/************************************************** HOOKS */

define(__NAMESPACE__ . '\HOOK_RUN_QUERY',		'run_query');