<?php

namespace CrossORM\Core;

use CrossORM\Security\ACL;

/**
 * ORM Class, where most of the magic happens
 */
abstract class ORM
{

    protected static $_connections;

    protected $_conn;
    protected $_config;

    protected $_state   = \CrossORM\STATE_FRESH;

    protected $_fields  = array();

    protected $_last_query;
    protected $_last_query_result;

    protected $_db_id   = \CrossORM\DB_ID_DEFAULT;
    protected $_actor   = \CrossORM\ACTOR_DEFAULT;

    protected $_acl     = false;

    protected $_model   = NULL;

    public $_build;

    /************************************************** INITIALIZATION */

    /**
     * Initiate a new instance of the ORM, and create the db connection
     * if it does not already exist
     *
     * @param   array|object            $config
     *
     * @return  $this
     */
    function __construct($config, $id = \CrossORM\DB_ID_DEFAULT)
    {
        $this->_config = (object) $config;
        $this->_db_id = $id;

        $identifier = sha1($this->_config->connection_string);

        if (isset(static::$_connections[$identifier]))
        {
            $this->_conn = static::$_connections[$identifier];
        } else
        {
            $this->_conn = $this->connect();
            static::$_connections[$identifier] = $this->_conn;
        }

        $this->_build   = new Builder;

        return $this;
    }

    /**
     * Create a new instance
     *
     * @param   array|object|NULL           $data
     *
     * @return  object
     */
    protected function create($data = NULL)
    {
        $this->_build->query_type(\CrossORM\INSERT);

        $class_name = get_class($this);

        $instance = new $this($this->_config, $this->_db_id);

        $instance->table($this->_build->table());
        $instance->id_column($this->_build->id_column());
        $instance->acl($this->_acl ? $this->_actor : false);

        if ( $data !== NULL )
        {
            $instance->hydrate($data);
            $instance->state(\CrossORM\STATE_HYDRATED);
        }

        return $instance;
    }

    /**
     * Hydrate this instance with data
     *
     * @param   array           $data
     *
     * @return  $this
     */
    protected function hydrate($data)
    {
        $this->set($data, NULL, true);

        return $this;
    }

    /**
     * Turn a result row into an instance
     *
     * @param   object|array            $row
     *
     * @return  object
     */
    protected function _create_instance_from_row($row)
    {
        $result = $this->create($row);

        $result->_last_query        = $this->_last_query;
        $result->_last_query_result = $this->_last_query_result;

        return $result;
    }

    /**
     * Set the query state
     *
     * @param   string          $state
     *
     * @return  string
     */
    protected function state($state = NULL)
    {
        if ($state == NULL)
        {
            return $this->_state;
        }

        return $this->_state = $state;
    }

    public function use_model($model)
    {
        $this->_modal = $model;
    }

    /************************************************** OVERLOADING */

    /**
     * Forward __get requests to @see #get
     *
     * @param   string          $key
     *
     * @return  mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Forward __set requests to @see set()
     *
     * @param   string          $key
     * @param   mixed           $value
     *
     * @return  mixed
     */
    public function __set($key, $value)
    {
        if ($this->state() == \CrossORM\STATE_HYDRATED)
        {
            $this->_build->query_type(\CrossORM\UPDATE);
        }

        $this->set($key, $value);
    }

    /**
     * Check if @see $_fields has the given entry
     *
     * @param   string          $key
     *
     * @return  bool
     */
    public function __isset($key)
    {
        return isset($this->_fields[$key]);
    }

    /************************************************** BUILDING QUERIES */

    /**
     * Toggle ACL and optionally set the actor to be used
     *
     * @param   bool|string         $enable         If a string is given it will be used as the actor
     *
     * @return  $this
     */
    function acl($enable = true)
    {
        if ( !is_bool($enable))
        {
            $this->_actor = $enable;
        }

        $this->_acl = (bool) $enable;

        return $this;
    }

    /**
     * Alias of @see table()
     *
     * @param   string          $table_name
     *
     * @return  $this
     */
    function for_table($table_name)
    {
        return $this->table($table_name);
    }

    /**
     * Define table name to base queries on
     *
     * @param   string          $table_name
     *
     * @return  $this
     */
    function table($table_name)
    {
        $this->_build->table($table_name);

        return $this;
    }

    /**
     * Change the id column name
     *
     * @param   string          $id_column
     *
     * @return  $this
     */
    function id_column($id_column)
    {
        $this->_build->id_column($id_column);

        return $this;
    }

    /**
     * Alias the table we're selecting from
     *
     * @param   string          $alias
     *
     * @return  $this
     */
    function table_alias($alias)
    {
        $this->_build->table_alias($alias);

        return $this;
    }

    /**
     * Select one or multiple fields
     *
     * Any of the following will work as input
     *
     * * field1
     * * array(field1, alias_for_field1)
     * * array(field1,field2,field3)
     * * array(array(field1,alias_for_field1),array(field2,alias_for_field2))
     *
     * @param   array|string            $select
     *
     * @return  $this
     */
    function select($select)
    {
        $this->_build->select($column);

        return $this;
    }

    /**
     * Add standard where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\EQUAL, $value);

        return $this;
    }

    /**
     * Add standard where clause statement, links to @see where()
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_equal($column_name, $value)
    {
        return $this->where($column_name, $value);
    }

    /**
     * Add "is not" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_not_equal($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\NOT_EQUAL, $value);

        return $this;
    }

    /**
     * Get entry by id
     *
     * @param   int         $id
     *
     * @return  $this
     */
    function where_id_is($id)
    {
        $this->_build->clause($this->_build->id_column(), \CrossORM\EQUAL, $id);

        return $this;
    }

    /**
     * Add "like" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_like($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\LIKE, $value);

        return $this;
    }

    /**
     * Add "not like" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_not_like($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\NOT_LIKE, $value);

        return $this;
    }

    /**
     * Add "greater than" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_gt($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\GREATER_THAN, $value);

        return $this;
    }

    /**
     * Add "less than" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_lt($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\LESS_THAN, $value);

        return $this;
    }

    /**
     * Add "greater than or equal" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_gte($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\GREATER_THAN_EQUAL, $value);

        return $this;
    }

    /**
     * Add "less than or equal" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_lte($column_name, $value)
    {
        $this->_build->clause($column_name, \CrossORM\LESS_THAN_EQUAL, $value);

        return $this;
    }

    /**
     * Add "IN" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_in($column_name, $values)
    {
        $this->_build->clause($column_name, \CrossORM\IN, $value);

        return $this;
    }

    /**
     * Add "NOT IN" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_not_in($column_name, $values)
    {
        $this->_build->clause($column_name, \CrossORM\NOT_IN, $value);

        return $this;
    }

    /**
     * Add "IS NULL" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_NULL($column_name)
    {
        $this->_build->clause($column_name, \CrossORM\IS_NULL, $value);

        return $this;
    }

    /**
     * Add "IS NOT NULL" where clause statement
     *
     * @param   string                  $column_name
     * @param   string|int|float        $value
     *
     * @return  $this
     */
    function where_not_NULL($column_name)
    {
        $this->_build->clause($column_name, \CrossORM\IS_NOT_NULL, $value);

        return $this;
    }

    /**
     * Limit results
     *
     * @param   int         $limit
     *
     * @return  $this
     */
    function limit($limit)
    {
        $this->_build->limit($limit);

        return $this;
    }

    /**
     * Offset search entries
     *
     * @param   int         $offset
     *
     * @return  $this
     */
    function offset($offset)
    {
        $this->_build->offset($offset);

        return $this;
    }

    /**
     * Order field descending
     *
     * @param   string          $column_name
     *
     * @return  $this
     */
    function order_by_desc($column_name)
    {
        $this->_build->order_by($column_name, \CrossORM\DESC);
    }

    /**
     * Order field ascending
     *
     * @param   string          $column_name
     *
     * @return  $this
     */
    function order_by_asc($column_name)
    {
        $this->_build->order_by($column_name, \CrossORM\ASC);
    }

    /**
     * Group results
     *
     * @param   string          $column_name
     *
     * @return  $this
     */
    function group_by($column_name)
    {
        $this->_build->group_by($column_name);
    }

    /**
     * Set a field value
     *
     * @param   string|array            $key
     * @param   string|int|float|NULL   $value
     *
     * @return  $this
     */
    function set($key, $value = NULL, $hydrate = false)
    {
        if (!is_array($key))
        {
            $key = array($key => $value);
        }

        foreach ($key AS $k => $v)
        {
            $this->_fields[$k] = $v;
        }

        if ( ! $hydrate)
        {
            $this->_build->set($key, $value);
        }

        return $this;
    }

    /************************************************** EXECUTION */

    /**
     * Save the current modifications to the database
     *
     * @return  $this
     */
    public function save($query_type = NULL)
    {

        // Optionally override the query_type
        if ($query_type != NULL)
        {
            $this->_build->query_type($query_type);
        }

        $type = $this->_build->query_type();

        // Auto detect query type if its not set to UPDATE or INSERT
        if ( !in_array($type,array(\CrossORM\UPDATE,\CrossORM\INSERT)))
        {
            if ($this->state() == \CrossORM\STATE_HYDRATED)
            {
                $this->_build->query_type(\CrossORM\UPDATE);
            }
                else
            {
                $this->_build->query_type(\CrossORM\INSERT);
            }
        }

        // If querying upon a hydrated instance, limit the query to this db entry
        if ($type == \CrossORM\UPDATE AND $this->state() == \CrossORM\STATE_HYDRATED)
        {
            $this->where_id_is($this->id());
            $this->limit(1);
        }

        $this->build()->run();

        // If this was an INSERT query, set the state to FRESH and define the inserted ID
        // as a clause so that a followup SELECT query is executed properly
        if ($type == \CrossORM\INSERT)
        {
            $this->state(\CrossORM\STATE_FRESH);
            $this->where_id_is($this->insert_id());
            $this->limit(1);
        }

        return $this;
    }

    /**
     * Perform a delete query based on the current critera's set in the builder
     *
     * @return  $this
     */
    public function delete()
    {
        if ($this->state() == \CrossORM\STATE_HYDRATED)
        {
            $this->where_id_is($this->id());
            $this->limit(1);
        }

        $this->_build->query_type(\CrossORM\DELETE);

        $this->build()->run();

        return $this;
    }

    /**
     * Validate if we have permission to run the query, throws @see Exceptions\ACL if not.
     *
     * @return  void
     */
    public function validate_acl()
    {
        if ( !$this->_acl)
        {
            return;
        }

        ACL::validate_query($this->_build,array($this->_actor,$this->_db_id));
    }

    public function get_query()
    {
        return $this->_last_query;
    }

    /************************************************** RESULTS */

    /**
     * Get entry from @see $_fields
     *
     * @param   string          $key
     *
     * @return  mixed
     */
    public function get($key)
    {
        return isset($this->_fields[$key]) ? $this->_fields[$key] : NULL;
    }

    /**
     * Get the defined fields
     *
     * @return array
     */
    public function fields()
    {
        return $this->_fields;
    }

    /**
     * Return results as array
     *
     * @return  object|array
     */
    function as_array($contextual = true)
    {
        if ( $this->state() == \CrossORM\STATE_FRESH)
        {
            $this->_build->query_type(\CrossORM\SELECT);

            if ($this->_build->limit()==1)
            {
                return (array) $this->_get_row();
            }
                else
            {
                return $this->_get_rows();
            }
        }
            else
        {
            if ($contextual)
            {
                return $this->_fields;
            }
                else
            {
                return array($this->_fields);
            }

        }
    }

    /**
     * Return results as JSON
     *
     * @return  string
     */
    function as_json($contextual = true)
    {
        $result = $this->as_array();
        return json_encode($result, JSON_BIGINT_AS_STRING | JSON_NUMERIC_CHECK);
    }

    /**
     * Find and return one entry
     *
     * @return  object
     */
    function find_one()
    {
        $this->_build->query_type(\CrossORM\SELECT);

        $this->_build->limit(1);

        return $this->_get_row(true);
    }

    /**
     * Find and return multiple entries
     *
     * @return  Resultset
     */
    function find_many()
    {
        $this->_build->query_type(\CrossORM\SELECT);

        return new Resultset($this->_get_rows(true), $this->_last_query);
    }

    /**
     * Get the ID of this instance
     *
     * @return  int|string
     */
    function id()
    {
        if ( $this->state() == \CrossORM\STATE_FRESH)
        {
            return $this->find_one()->{$this->_build->id_column()};
        } else
        {
            return $this->_fields[$this->_build->id_column()];
        }
    }

}
