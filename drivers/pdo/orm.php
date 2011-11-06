<?php

namespace CrossORM\Drivers\PDO;

use CrossORM\DB;
use CrossORM\Exception;
use PDO;
use PDOException;

/**
 *
 * CrossORM ORM libarary for PDO driver
 * This library is based on the excellent "Idiorm" library by Jamie Matthews
 *
 * Idiorm
 *
 * http://github.com/j4mie/idiorm/
 *
 * A single-class super-simple database abstraction layer for PHP.
 * Provides (nearly) zero-configuration object-relational mapping
 * and a fluent interface for building basic, commonly-used queries.
 *
 * BSD Licensed.
 *
 * Copyright (c) 2010, Jamie Matthews
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

require_once dirname(__FILE__) . '/model.php';

class ORM extends \CrossORM\ORM implements \CrossORM\Interfaces\ORM
{
	
	private $_values = array();
	private $_identifier_quote_character;

	function connect()
	{
		if (!is_object($this->_conn))
		{
			
			try {
				$conn = new PDO(
					$this->_config->connection_string,
					$this->_config->username,
					$this->_config->password,
					isset($this->_config->driver_options) ? $this->_config->driver_options : null
				);
			} catch (PDOException $e)
			{
				throw new Exception($e);
			}
			
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->_set_identifier_quote_character($conn);
			
		}
		
		return $conn;
	}
	
	public function run() {
		
		if (!is_object($this->_last_query_result))
		{
			$this->build();
		}
		
		try {
			
			$this->state(STATE_EXECUTED);
			
			$this->_last_query_result = $this->_conn->prepare($this->_last_query);
			return $this->_last_query_result->execute($this->_values);
		
		} catch (PDOException $e)
		{
			throw new Exception($e);
		}

	}

	function build()
	{
		$this->_values = array();
		
		switch ($this->_build->query_type()) {
			case SELECT:
				$this->_build_select();
				break;
		}
		
		return $this;
	}
	
	protected function _build_select() {
		$this->_last_query = $this->_join_if_not_empty(" ", array(
			$this->_build_select_start(),
			$this->_build_where(),
			$this->_build_group_by(),
			$this->_build_order_by(),
			$this->_build_limit(),
			$this->_build_offset(),
		));
	}
	
	/**
	 * Build the start of the SELECT statement
	 */
	protected function _build_select_start() {
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
	 * Build the WHERE clause(s)
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
			$this->_values[] = $clause[2];
		}

		return "WHERE " . join(" AND ", $where_conditions);
	}
	
	/**
	 * Build GROUP BY
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
	 */
	protected function _build_limit()
	{
		if (!is_null($this->_build->limit()))
		{
			return "LIMIT " . $this->_build->limit();
		}
		return '';
	}

	/**
	 * Build OFFSET
	 */
	protected function _build_offset()
	{
		if (!is_null($this->_build->offset()))
		{
			return "OFFSET " . $this->_build->offset();
		}
		return '';
	}
	
	/************************************************** RESULTS */
	
	public function _get_row($instantiate = false) {
		if ( $this->state() == STATE_FRESH)
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
	
	public function _get_rows($instantiate = false) {
		$rows = array();
		while ($row = $this->_get_row($instantiate)) {
			$rows[] = $row;
		}
		
		return $rows;
	}

	function count()
	{}

	function is_dirty($key)
	{}

	
	function force_all_dirty()
	{}

	function raw_query($query, $parameters)
	{}

	function where_raw($clause, $parameters)
	{}
	
	/************************************************** HELPERS */
	
	protected function _quote_identifier($identifier) {
		return $this->_identifier_quote_character . $identifier . $this->_identifier_quote_character;
	}
	
	protected function _set_identifier_quote_character($conn) {
		if ( !isset($this->_config->identifier_quote_character) )
		{
			switch($conn->getAttribute(PDO::ATTR_DRIVER_NAME)) {
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
		} else
		{
			$this->_identifier_quote_character = $this->_config->identifier_quote_character;
		}
		
	}
	
	/**
	 * Wrapper around PHP's join function which
	 * only adds the pieces if they are not empty.
	 */
	protected function _join_if_not_empty($glue, $pieces) {
		$filtered_pieces = array();
		foreach ($pieces as $piece) {
			if (is_string($piece)) {
				$piece = trim($piece);
			}
			if (!empty($piece)) {
				$filtered_pieces[] = $piece;
			}
		}
		return join($glue, $filtered_pieces);
	}

	
}