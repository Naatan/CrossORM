<?php

namespace CrossORM\Drivers\PDO;

use CrossORM\DB,
    CrossORM\Security\ACL,
    CrossORM\Exceptions\Exception,
    PDO,
    PDOException;

class ORM extends \CrossORM\Core\ORM implements \CrossORM\Interfaces\ORM
{

    private $_identifier_quote_character;

    /**
     * Innitiate DB connection
     *
     * @returns object
     */
    public function connect()
    {
        try {
            $conn = new PDO(
                $this->_config->connection_string,
                isset($this->_config->username) ? $this->_config->username : null,
                isset($this->_config->password) ? $this->_config->password : null,
                isset($this->_config->driver_options) ? $this->_config->driver_options : null
            );
        }
            catch (PDOException $e)
        {
            throw new Exception($e);
        }

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_set_identifier_quote_character($conn);

        return $conn;
    }

    /**
     * Get the PDO driver in use
     *
     * @return String
     */
    protected function _driver()
    {
        $driver = strtolower(explode(':', $this->_config->connection_string)[0]);
        return $driver; // Allow for debugging
    }

    /**
     * Run the query
     *
     * @returns object
     */
    public function run()
    {

        if (!is_object($this->_last_query_result))
        {
            $this->build();
        }

        $this->validate_acl();

        try
        {

            $this->state(\CrossORM\STATE_EXECUTED);

            $this->_last_query_result = $this->_conn->prepare($this->_last_query[0]);
            return $this->_last_query_result->execute($this->_last_query[1]);

        }
            catch (PDOException $e)
        {
            throw new Exception($e);
        }

    }

    /**
     * Build the current query in SQL format
     *
     * @returns $this
     */
    function build()
    {
        $this->_last_query = array('',array());

        switch ($this->_build->query_type())
        {
            case \CrossORM\SELECT:
                $this->_build_select();
                break;
            case \CrossORM\UPDATE:
                $this->_build_update();
                break;
            case \CrossORM\INSERT:
                $this->_build_insert();
                break;
            case \CrossORM\DELETE:
                $this->_build_delete();
                break;
        }

        return $this;
    }

    /**
     * Build the current query as a select query
     *
     * @returns void
     */
    protected function _build_select()
    {
        $this->_last_query[0] = $this->_join_if_not_empty(" ", array(
            $this->_build_select_start(),
            $this->_build_where(),
            $this->_build_group_by(),
            $this->_build_order_by(),
            $this->_build_limit(),
            $this->_build_offset()
        ));
    }

    /**
     * Build the start of the SELECT statement
     *
     * @returns string
     */
    protected function _build_select_start()
    {
        $result_columns = array();

        foreach ($this->_build->select() AS $select)
        {
            if (is_array($select))
            {
                array_walk($select,array($this,'_quote_identifier'));
                $result_columns[] = $select[0] . ' AS ' . $select[1];
            } else
            {
                $result_columns[] = $this->_quote_identifier($select);
            }
        }

        $result_columns = join(', ',$result_columns);

        if (empty($result_columns))
        {
            $result_columns = '*';
        }

        $fragment = "SELECT {$result_columns} FROM " . $this->_quote_identifier($this->_build->table());

        if (!is_null($this->_build->table_alias()))
        {
            $fragment .= " " . $this->_quote_identifier($this->_build->table_alias());
        }

        return $fragment;
    }

    /**
     * Build the current query as an update query
     *
     * @returns void
     */
    protected function _build_update()
    {
        $this->_last_query[0] = $this->_join_if_not_empty(" ", array(
            $this->_build_update_start(),
            $this->_build_where(),
            $this->_build_limit()
        ));
    }

    /**
     * Build the start of the UPDATE statement
     *
     * @returns string
     */
    protected function _build_update_start()
    {
        $query = array();
        $query[] = "UPDATE " . $this->_quote_identifier($this->_build->table()) . " SET";

        $field_list = array();
        foreach ($this->_build->set() as $key => $value)
        {
            $field_list[] = $this->_quote_identifier($key) . " = ?";
            $this->_last_query[1][] = $value;
        }

        $query[] = join(", ", $field_list);
        return join(" ", $query) . " ";
    }

    /**
     * Build the current query as an insert query
     *
     * @returns void
     */
    protected function _build_insert()
    {
        $query = array();

        $query[] = "INSERT INTO";
        $query[] = $this->_quote_identifier($this->_build->table());
        $field_list = array_map(array($this, '_quote_identifier'), array_keys($this->_build->set()));
        $query[] = "(" . join(", ", $field_list) . ")";
        $query[] = "VALUES";

        $placeholders = join(", ", array_fill(0, count($this->_build->set()), "?"));
        $query[] = "({$placeholders})";

        $this->_last_query[1] = array_merge($this->_last_query[1],array_values($this->_build->set()));

        $this->_last_query[0] = join(" ", $query);
    }

    /**
     * Build the current query as a delete query
     *
     * @returns void
     */
    protected function _build_delete()
    {
        $this->_last_query[0] = $this->_join_if_not_empty(" ", array(
            $this->_build_delete_start(),
            $this->_build_where(),
            $this->_build_limit(),
            $this->_build_offset()
        ));
    }

    /**
     * Build the start of the DELETE statement
     *
     * @returns string
     */
    protected function _build_delete_start()
    {
        return join(" ", array(
            "DELETE FROM",
            $this->_quote_identifier($this->_build->table())
        ));
    }

    /**
     * Build the WHERE clause(s)
     *
     * @returns string
     */
    protected function _build_where()
    {
        // If there are no WHERE clauses, return empty string
        if (count($this->_build->clauses()) === 0)
        {
            return '';
        }

        $where_conditions = array();
        foreach ($this->_build->clauses() as $clause)
        {
            $where_conditions[] = $clause[0] . ' ' . $clause[1] . ' ?';
            $this->_last_query[1][] = $clause[2];
        }

        return "WHERE " . join(" AND ", $where_conditions);
    }

    /**
     * Build GROUP BY
     *
     * @returns string
     */
    protected function _build_group_by()
    {
        if (count($this->_build->group_by()) === 0)
        {
            return '';
        }
        return "GROUP BY " . join(", ", $this->_build->group_by());
    }

    /**
     * Build ORDER BY
     *
     * @returns string
     */
    protected function _build_order_by()
    {
        if (count($this->_build->order_by()) === 0)
        {
            return '';
        }
        return "ORDER BY " . join(", ", $this->_build->order_by());
    }

    /**
     * Build LIMIT
     *
     * @returns string
     */
    protected function _build_limit()
    {
        if (!is_null($this->_build->limit()) && (
            $this->_driver() != 'sqlite' || // Sqlite does not support LIMIT in DELETE/UPDATE queries
            ! in_array($this->_build->query_type(), [\CrossORM\DELETE, \CrossORM\UPDATE])
        ))
        {
            return "LIMIT " . (int) $this->_build->limit();
        }
        return '';
    }

    /**
     * Build OFFSET
     *
     * @returns string
     */
    protected function _build_offset()
    {
        if (!is_null($this->_build->offset()))
        {
            return "OFFSET " . (int) $this->_build->offset();
        }
        return '';
    }

    /************************************************** RESULTS */

    /**
     * Get single row
     *
     * @param   bool            $instantiate    Instantiate result as ORM instance
     *
     * @returns array|object
     */
    public function _get_row($instantiate = false)
    {
        if ($this->state() == \CrossORM\STATE_FRESH)
        {
            $this->build()->run();
        }

        $row = $this->_last_query_result->fetch(PDO::FETCH_ASSOC);

        if ($row AND $instantiate)
        {
            $row = $this->_create_instance_from_row($row);
        }

        return $row;
    }

    /**
     * Get all rows returned by query
     *
     * @param   bool            $instantiate    Instantiate rows as ORM instances
     *
     * @returns array
     */
    public function _get_rows($instantiate = false)
    {
        $rows = array();
        while ($row = $this->_get_row($instantiate))
        {
            $rows[] = $row;
        }

        return $rows;
    }

    function insert_id()
    {
        return $this->_conn->lastInsertId();
    }

    /**
     * Count the number of results
     *
     * @returns int
     */
    function count()
    {}

    /************************************************** HELPERS */

    /**
     * Quote an identifier in the query
     *
     * @param   string          $identifier
     *
     * @returns string
     */
    protected function _quote_identifier($identifier)
    {
        return $this->_identifier_quote_character . $identifier . $this->_identifier_quote_character;
    }

    /**
     * Set the identifier quote character for the current connection, based on DB engine
     *
     * @param   object          $conn       Must provide connection, as this function is called during the initialisation
     *
     * @returns void
     */
    protected function _set_identifier_quote_character($conn)
    {
        if ( !isset($this->_config->identifier_quote_character) )
        {
            switch($conn->getAttribute(PDO::ATTR_DRIVER_NAME))
            {
                case 'pgsql':
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                case 'sybase':
                    $this->_identifier_quote_character = '"';
                case 'mysql':
                case 'sqlite':
                case 'sqlite2':
                default:
                    $this->_identifier_quote_character = '`';
            }
        }
            else
        {
            $this->_identifier_quote_character = $this->_config->identifier_quote_character;
        }

    }

    /**
     * Wrapper around PHP's join function which
     * only adds the pieces if they are not empty.
     *
     * @returns string
     */
    protected function _join_if_not_empty($glue, $pieces)
    {

        $filtered_pieces = array();
        foreach ($pieces as $piece)
        {
            if (is_string($piece))
            {
                $piece = trim($piece);
            }

            if (!empty($piece))
            {
                $filtered_pieces[] = $piece;
            }
        }

        return join($glue, $filtered_pieces);

    }


}
