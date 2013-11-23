<?php

namespace CrossORM\Core;

/**
 * Builder class
 *
 * Prepares the query structure so that the driver can then build it however it wants
 */
class Builder {

    private $query_type         = \CrossORM\SELECT;

    private $affected_fields    = array();

    private $id_column          = \CrossORM\ID_COLUMN;

    private $table              = '';
    private $table_alias        = '';
    private $joins              = array();
    private $select             = array();
    private $set                = array();
    private $id                 = '';
    private $clause             = array();
    private $orderby            = array();
    private $groupby            = array();
    private $offset             = NULL;
    private $limit              = NULL;

    /**
     * Contructor
     *
     * @return  void
     */
    public function __construct()
    {
        $this->select('*');
    }

    /**
     * Get the current query elements, mostly for debugging purposes
     *
     * @return  object
     */
    public function get_query_dump()
    {
        return (object) array(
            'query_type'        => $this->query_type,
            'affected_fields'   => $this->affected_fields,
            'id_column'         => $this->id_column,
            'tables'            => $this->tables,
            'select'            => $this->select,
            'set'               => $this->set,
            'id'                => $this->id,
            'clause'            => $this->clause,
            'orderby'           => $this->orderby,
            'orderdir'          => $this->orderdir,
            'groupby'           => $this->groupby,
            'offset'            => $this->offset,
            'limit'             => $this->limit,
        );
    }

    /**
     * Get fields being affected in this query, useful for ACL validation
     *
     * @return  array
     */
    public function get_affected_fields()
    {
        return $this->affected_fields;
    }

    /**
     * Set / get the type of query being executed
     *
     * @param   string|NULL         $type
     *
     * @return  string
     */
    public function query_type($type = NULL)
    {
        if ($type == NULL)
        {
            return $this->query_type;
        }

        return $this->query_type = $type;
    }

    /**
     * Set / get the id column
     *
     * @param   string|NULL         $field
     *
     * @return  string
     */
    public function id_column($field = NULL)
    {
        if ($field == NULL)
        {
            return $this->id_column;
        }

        return $this->id_column = $field;
    }

    /**
     * Set / get the select fields
     *
     * @param   string|array|NULL           $select
     *
     * @return  array
     */
    public function select($select = NULL)
    {
        if ($select == NULL)
        {
            return $this->select;
        }

        if ( ! is_array($select))
        {
            $select = array($select);
        }

        foreach ($select AS $field)
        {
            if (is_array($field))
            {
                $this->_use_field($field[0]);
            } else
            {
                $this->_use_field($field);
            }
        }

        return $this->select = array_merge($this->select,$select);
    }

    /**
     * Set / get the conditional clause
     *
     * @param   string|NULL         $column_name
     * @param   string              $separator
     * @param   string|int          $value
     *
     * @return  array
     */
    public function clause($column_name = NULL, $separator, $value)
    {
        if ($column_name == NULL)
        {
            return $this->clause;
        }

        if ($column_name == $this->id_column() AND $this->limit() == NULL)
        {
            $this->limit(1);
        }

        $this->_use_field($column_name);

        return $this->clause[] = array($column_name, $separator, $value);
    }

    /**
     * Set / get conditional clauses
     *
     * @param   array|NULL          $clauses
     *
     * @return  array
     */
    public function clauses($clauses = NULL)
    {
        if ($clauses == NULL)
        {
            return $this->clause;
        }

        foreach ($clauses AS $clause)
        {
            $this->clause($clause);
        }

        return $this->clause;
    }

    /**
     * Set / get the table to be used
     *
     * @param   string|NULL         $table_name
     *
     * @return  string
     */
    public function table($table_name = NULL)
    {
        if ($table_name == NULL)
        {
            return $this->table;
        }

        return $this->table = $table_name;
    }

    /**
     * Set / get the table alias
     *
     * @param   string|NULL         $table_name
     *
     * @return  string
     */
    public function table_alias($table_name = NULL)
    {
        if ($table_name == NULL)
        {
            return $this->table_alias;
        }

        return $this->table_alias = $table_name;
    }

    /**
     * Set / get the limit
     *
     * @param   int|NULL            $limit
     *
     * @return  int
     */
    public function limit($limit = NULL)
    {
        if ($limit == NULL)
        {
            return $this->limit;
        }

        return $this->limit = (int) $limit;
    }

    /**
     * Set / get the offset
     *
     * @param   int|NULL            $offset
     * @return  int
     */
    public function offset($offset = NULL)
    {
        if ($offset == NULL)
        {
            return $this->offset;
        }

        return $this->offset = (int) $offset;
    }

    /**
     * Set / get an order by clause
     *
     * @param   string|NULL         $column_name
     * @param   string|NULL         $dir
     *
     * @return  array
     */
    public function order_by($column_name = NULL, $dir = NULL)
    {
        if ($column_name == NULL)
        {
            return $this->orderby;
        }

        $this->_use_field($column_name);

        return $this->orderby[] = array($column_name, $dir);
    }

    /**
     * Set / get a group by clause
     *
     * @param   string|NULL         $column_name
     *
     * @return  array
     */
    public function group_by($column_name = NULL)
    {
        if ($column_name == NULL)
        {
            return $this->groupby;
        }

        $this->_use_field($column_name);

        return $this->groupby[] = $column_name;
    }

    /**
     * Set fields to be updated / inserted
     *
     * @param   string|array|NULL           $entries
     * @param   string|int|NULL             $value
     *
     * @return  array
     */
    public function set($entries = NULL, $value = NULL)
    {
        if ($entries == NULL)
        {
            return $this->set;
        }

        if ( !is_array($entries))
        {
            $entries = array($entries => $value);
        }

        foreach ($entries AS $key => $value)
        {
            $this->set[$key] = $value;
            $this->_use_field($key);
        }

        return $this->set;
    }

    /**
     * Get a value that is to be set
     *
     * @param   string          $key
     *
     * @return  string      Can return the UNDEFINED constant if the key is not being set
     */
    public function get($key)
    {
        return isset($this->set[$key]) ? $this->set[$key] : UNDEFINED;
    }

    /**
     * Mark a specific field as used
     *
     * @param   string          $field
     *
     * @return  array
     */
    public function _use_field($field)
    {
        return $this->_use_fields(array($field));
    }

    /**
     * Mark specific fields as used
     *
     * @param   array           $fields
     *
     * @return  array
     */
    public function _use_fields($fields)
    {
        return $this->affected_fields = array_unique(
            array_merge(
                $this->affected_fields,
                $fields
            )
        );
    }

    /**
     * Unset array entries by value
     *
     * @param   array               $array
     * @param   string|int          $value
     *
     * @return  array
     */
    private function _array_unset_value(array $array,$value)
    {
        if ( !in_array($value,$array))
        {
            return $array;
        }

        $numeric = isset($array[0]);

        foreach ($array AS $k => $entry)
        {
            if ($entry == $value)
            {
                unset($array[$k]);
            }
        }

        if ($numeric)
        {
            $array = array_values($array);
        }

        return $array;
    }

}
