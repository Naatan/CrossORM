<?php

namespace CrossORM\Security;

use \CrossORM\Core\Builder,
    \CrossORM\Exceptions,
    \CrossORM\Helpers;

/**
 * ACL Class
 */
class ACL
{

    protected static $actors;
    protected static $rules;
    protected static $rules_flat;

    /* depth > node > schema type */
    protected static $_schemas = array(
        1   => array(
            'tables'    => 'array'
        ),
        3   => array(
            'actions'   => 'string',
            'fields'    => 'array'
        ),
        5   => array(
            'actions'   => 'string'
        )
    );

    /**
     * Clear the ACL rules altogether (no arg) or for a specific actor
     *
     * @param   string|NULL         $actor
     *
     * @return  void
     */
    public static function clear($actor = NULL)
    {
        if ($actor != NULL)
        {
            foreach (static::$actors AS &$actor)
            {
                unset($actor);
            }

            unset(static::$rules->{$actor});
        } else
        {
            static::$actors = (object) array();
            static::$rules  = (object) array();
        }
    }

    /**
     * Creates / modifies settings for the given actor
     *
     * @param   string                  $name
     * @param   string                  $mode
     * @param   string                  $db_id
     *
     * @return  void
     */
    public static function set_actor($name, $mode = \CrossORM\MODE_WHITELIST, $db_id = \CrossORM\DB_ID_DEFAULT)
    {
        if ( !is_object(static::$actors))
        {
            static::$actors = (object) array();
        }

        $actor = static::_get_actor_id(array($name,$db_id));

        static::$actors->{$actor} = (object) array(
            'id'    => $actor,
            'name'  => $name,
            'mode'  => $mode,
            'db_id' => $db_id
        );
    }

    /**
     * Get actor settings
     *
     * @param   string|array            $name
     *
     * @return  object
     */
    public static function get_actor($name = \CrossORM\ACTOR_DEFAULT)
    {
        $actor = static::_get_actor_id($name);

        if ( !isset(static::$actors->{$actor}))
        {
            if (is_array($name))
            {
                static::set_actor($name[0], \CrossORM\MODE_WHITELIST, $name[1]);
            } else
            {
                static::set_actor($name);
            }
        }

        return static::$actors->{$actor};
    }

    /**
     * Get the active rules
     *
     * @param   bool            $flat
     *
     * @return  object
     */
    public static function get_rules($flat = false)
    {
        if ($flat)
        {
            return static::$rules_flat;
        } else
        {
            return static::$rules;
        }
    }

    /**
     * Check if actor has given permission
     *
     * @param   array|string            $rule
     * @param   array|string            $actor
     * @param   bool                    $fatal
     *
     * @return  bool
     */
    public static function has_permission($rule, $actor = NULL, $fatal = false)
    {
        if (is_array($rule))
        {
            $rule = implode('.',$rule);
        }

        $actor      = static::get_actor($actor);
        $whitelist  = $actor->mode == \CrossORM\MODE_WHITELIST;

        if ( !is_string($rule))
        {
            throw new Exceptions\ACL('Invalid rule format supplied to has_permission');
        }

        $rule = $actor->id . '.' . $rule;
        $rules = static::$rules_flat;

        $result = in_array($rule,$rules) == $whitelist;

        if ($fatal AND !$result)
        {
            throw new Exceptions\ACL('Access to the following control was denied: '.$rule);
        } else
        {
            return $result;
        }
    }

    /**
     * Set multiple rules at once
     *
     * @param   array                   $rules
     * @param   string                  $type
     * @param   array|string            $actor
     * @return  void
     */
    public static function set_rules($rules, $type = \CrossORM\RULE_TYPE_FULL, $actor = NULL)
    {
        foreach ($rules AS $rule)
        {
            static::set_rule($rule, $type, $actor);
        }
    }

    /**
     * Set a rule
     *
     * @param   array|object|string     $rule
     * @param   string                  $type
     * @param   array|string            $actor
     *
     * @return  void
     */
    public static function set_rule($rule,$type = \CrossORM\RULE_TYPE_FULL, $actor = NULL)
    {
        $actor = static::_get_actor_id($actor);

        static::_ensure_exists(static::$rules,$actor,'object');

        switch ($type)
        {
            case \CrossORM\RULE_TYPE_FULL:
                static::_set_rule_full($rule,$actor);
                break;
            case \CrossORM\RULE_TYPE_TABLE:
                static::_set_rule_table($rule,$actor);
                break;
            case \CrossORM\RULE_TYPE_FIELD:
                static::_set_rule_field($rule,$actor);
                break;
        }

        static::$rules_flat = Helpers::flatten_array(static::$rules);
    }

    /**
     * Set a rule from the top level
     *
     * @param   array|object            $rule
     * @param   string                  $actor
     *
     * @return  void
     */
    private static function _set_rule_full($rule,$actor)
    {
        if (is_string($rule))
        {
            $rule = static::_objectify_strings($rule);
        }

        static::_ensure_exists( static::$rules, $actor, 'object' );

        Helpers::object_merge(  static::$rules->{$actor}, $rule );
    }

    /**
     * Set a rule from the table level
     *
     * @param   array|object            $rule
     * @param   string                  $actor
     *
     * @return  void
     */
    private static function _set_rule_table($rule,$actor)
    {
        static::_ensure_exists( static::$rules->{$actor},                       'tables',   'object'    );
        static::_ensure_exists( static::$rules->{$actor}->tables->{$rule[0]},   'actions',  'array'     );
        static::_ensure_exists( static::$rules->{$actor}->tables->{$rule[0]},   'fields',   'object'    );

        Helpers::object_merge(  static::$rules->{$actor}->tables->{$rule[0]}, $rule[1] );

    }

    /**
     * Set a rule from the field level
     *
     * @param   array|object            $rule
     * @param   string                  $actor
     *
     * @return  void
     */
    private static function _set_rule_field($rule,$actor)
    {
        static::_ensure_exists( static::$rules->{$actor},                       'tables',   'object'    );
        static::_ensure_exists( static::$rules->{$actor}->tables,               $rule[0],   'object'    );
        static::_ensure_exists( static::$rules->{$actor}->tables->{$rule[0]},   'actions',  'array'     );
        static::_ensure_exists( static::$rules->{$actor}->tables->{$rule[0]},   'fields',   'object'    );

        Helpers::object_merge(  static::$rules->{$actor}->tables->{$rule[0]}->fields, $rule[1] );
    }

    /**
     * Validate the given query build
     *
     * @param   Builder                 $builder
     * @param   array|string            $actor
     *
     * @return  void
     */
    public static function validate_query(Builder $builder, $actor)
    {
        switch ($builder->query_type())
        {
            case \CrossORM\SELECT:
                return static::_validate_select_query($builder, $actor);
                break;
            case \CrossORM\INSERT:
                return static::_validate_insert_query($builder, $actor);
                break;
            case \CrossORM\DELETE:
                return static::_validate_delete_query($builder, $actor);
                break;
            case \CrossORM\UPDATE:
                return static::_validate_update_query($builder, $actor);
                break;
        }
    }

    /**
     * Validate the given SELECT query
     *
     * @param   Builder                 $builder
     * @param   array|string            $actor
     *
     * @return  void
     */
    private static function _validate_select_query(Builder $builder, $actor)
    {
        foreach ($builder->get_affected_fields() AS $field)
        {
            static::has_permission(array('tables',$builder->table(),'fields',$field,'actions','select'), $actor, true);
        }

        if (count($builder->get_affected_fields()))
        {
            static::has_permission(array('tables',$builder->table(),'fields','*','actions','select'), $actor, true);
        }
    }

    /**
     * Convert an flattened array to a properly structured object
     *
     * @param   array|string            $array
     * @return  object
     */
    private static function _objectify_strings($array)
    {
        $result = array();

        if ( !is_array($array))
        {
            $array = array($array);
        }

        foreach ($array AS $string)
        {
            $bits   = explode('.',$string);
            $schema = '';
            $parent =& $result;

            for ($x=0;$x<count($bits);$x++)
            {
                $bit = $bits[$x];

                switch ($schema)
                {
                    case 'string':
                        $parent[] = $bit;
                        $bit = count($parent)-1;
                        break;
                    case 'array':
                        $parent = array_merge_recursive($parent,array($bit => array()));
                        break;
                    default:
                        $parent[$bit] = array();
                        break;
                }

                $schema = '';
                if (isset(static::$_schemas[$x+1]) AND isset(static::$_schemas[$x+1][$bit]))
                {
                    $schema = static::$_schemas[$x+1][$bit];
                }

                if (isset($parent[$bit]) AND is_array($parent[$bit]))
                {
                    $parent =& $parent[$bit];
                }
            }

        }

        return Helpers::objectify($result);
    }

    /**
     * Checks if object / array entry exists and creates it if not
     *
     * @param   array|object            $ob
     * @param   string|int              $entry
     * @param   string                  $type
     *
     * @return  array|object
     */
    private static function _ensure_exists(&$ob,$entry,$type='')
    {
        $is_array = is_array($ob);
        $ob = (object) $ob;

        if (isset($ob->{$entry}))
        {
            return;
        }

        switch ($type)
        {
            case 'array':
                $ob->{$entry} = array();
                break;
            case 'object':
                $ob->{$entry} = (object) array();
                break;
            default:
                $ob->{$entry} = '';
                break;
        }

        if ($is_array)
        {
            $ob = (array) $ob;
        }
    }

    /**
     * Get actor id based on actor name and db id, falls back on defaults
     *
     * @param   string|array            $actor
     *
     * @return  string
     */
    private static function _get_actor_id($actor = NULL)
    {
        if (empty($actor))
        {
            $actor = \CrossORM\ACTOR_DEFAULT;
        }

        if ( !is_array($actor))
        {
            $actor = array($actor, \CrossORM\DB_ID_DEFAULT);
        }

        return implode('_',$actor);
    }

}

//$rule = (object) array(
//  'type'  => RULE_TYPE_FULL,
//  'rule'  => array(/* See $rules below - contents of default_default */),
//
//  /* OR */
//
//  'type'  => RULE_TYPE_TABLE,
//  'rule'  => array('table_name', /* See $rules below - single entry of tables */),
//
//  /* OR */
//
//  'type'  => RULE_TYPE_FIELD,
//  'rule'  => array('table_name', '- see $rules below - single entry of fields -'),
//
//);

//$actor = array(
//
//  'name'  => 'default',
//  'mode'  => 'whitelist',
//  'db_id' => 'default'
//
//);
//
//$rules = array(
//
//  'default_default'   => array( /* name_db-id */
//
//      'tables'    => array(
//
//          'test'  => array(
//              'actions'   => array('select','insert','update','delete'),
//              'fields'        => array(
//
//                  'id'    => array(
//                      'actions'   => array('select','update')
//                  ),
//                  'name'  => array(
//                      'actions'   => array('select','update')
//                  )
//
//              )
//          )
//
//      )
//  )
//
//
//);
