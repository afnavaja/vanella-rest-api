<?php

namespace Vanella\Core;

use Vanella\Handlers\Helpers;
use PDO;

class Database
{

    private $_host = '';
    private $_username = '';
    private $_password = '';
    private $_dbname = '';
    private $_charset = 'utf8';
    private $_driver = '';
    private $_query = '';

    public function __construct($host = '', $username = '', $password = '', $db_name = '', $driver = 'mysql', $options = [])
    {
        $this->_host = $host;
        $this->_username = $username;
        $this->_password = $password;
        $this->_dbname = $db_name;
        $this->_driver = $driver;
        $this->_defaultDbPrimaryKeyName = isset($options['defaultPrimaryKeyName']) ? $options['defaultPrimaryKeyName'] : '';
    }

    /**
     * Creates a database connection
     */
    private function conn()
    {
        try {
            $dsn = $this->_driver . ':host=' . $this->_host . ';dbname=' . $this->_dbname . ';charset=' . $this->_charset;
            $pdo = new PDO($dsn, $this->_username, $this->_password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (\PDOException $e) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Displays the executed query string;
     */
    public function getExecutedQuery()
    {
        return $this->_query;
    }

    /**
     * Performs a SELECT statement
     *
     * @param string $table
     * @param string $fields
     * @param boolean $isDistinct
     */
    public function select($tableName, $fields = '*', $isDistinct = false)
    {
        $this->_currentTable = $tableName;
        $distinct = $isDistinct ? "DISTINCT " : "";
        $this->_query = "SELECT " . $distinct . $fields . " FROM " . $tableName;
        return $this;
    }

    /**
     * Performs an INSERT statement
     *
     * @param string $table
     * @param array $data
     */
    public function insert($tableName, $data = [])
    {
        $this->_currentTable = $tableName;
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'The data field cannot be empty',
            ];
        }

        $lastKey = $this->_lastKey($data);
        $columns = implode(',', array_keys($data));

        $this->_query = 'INSERT INTO ' . $tableName . ' (' . $columns . ') VALUES (';
        foreach ($data as $key => $values) {
            $this->_query .= !is_numeric($values) ? "'" . $values . "'" : $values;
            if ($key != $lastKey) {
                $this->_query .= ',';
            }
        }

        $this->_query .= ')';

        return $this;
    }

    /**
     * Performs an UPDATE statement
     *
     * @param string $table
     * @param array $data
     */
    public function update($tableName, $data = [])
    {
        $this->_currentTable = $tableName;
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'The data field cannot be empty',
            ];
        }

        $lastKey = $this->_lastKey($data);
        $this->_query = "UPDATE " . $tableName . " SET ";
        foreach ($data as $key => $value) {
            $this->_query .= $key . " = " . (is_numeric($value) ? $value : '"' . $value . '"');
            if ($key != $lastKey) {
                $this->_query .= ", ";
            }

        }

        return $this;
    }

    /**
     * Performs a DELETE statement
     *
     * @param string $tableName
     */
    public function delete($tableName = null)
    {
        $this->_currentTable = $tableName;
        $this->_query = 'DELETE FROM ' . $tableName;
        return $this;
    }

    /**
     * Performs a custom query statement
     *
     * @param string $tableName
     */
    public function customQuery($statement = "")
    {
        $this->_query = $statement;
        return $this;
    }

    /**
     * Adds a WHERE clause
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     */
    public function where($fieldName, $value, $operator = '=')
    {
        $this->_whereClause($fieldName, $value, $operator, 'WHERE');
        return $this;
    }

    /**
     * Adds an AND WHERE clause
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     * 
     * @return object
     */
    public function andWhere($fieldName, $value, $operator = '=')
    {
        $this->_whereClause($fieldName, $value, $operator, 'AND');
        return $this;
    }

    /**
     * Adds an OR WHERE clause
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     * 
     * @return $this
     */
    public function orWhere($fieldName, $value, $operator = '=')
    {
        $this->_whereClause($fieldName, $value, $operator, 'OR');
        return $this;
    }

    /**
     * Adds a inner join clause
     *
     * @param string $firstTable
     * @param string $firstTableColumn
     * @param string $secondTable
     * @param string $secondTableColumn
     * 
     * @return $this
     */
    public function innerJoin($firstTable, $firstTableColumn, $secondTable, $secondTableColumn)
    {
        $this->_joinClause('INNER JOIN', $firstTable, $firstTableColumn, $secondTable, $secondTableColumn);
        return $this;
    }

    /**
     * Adds a left join clause
     *
     * @param string $firstTable
     * @param string $firstTableColumn
     * @param string $secondTable
     * @param string $secondTableColumn
     * 
     * @return $this
     */
    public function leftJoin($firstTable, $firstTableColumn, $secondTable, $secondTableColumn)
    {
        $this->_joinClause('LEFT OUTER JOIN', $firstTable, $firstTableColumn, $secondTable, $secondTableColumn);
        return $this;
    }

    /**
     * Adds a right join clause
     *
     * @param string $firstTable
     * @param string $firstTableColumn
     * @param string $secondTable
     * @param string $secondTableColumn
     * 
     * @return $this
     */
    public function rightJoin($firstTable, $firstTableColumn, $secondTable, $secondTableColumn)
    {
        $this->_joinClause('RIGHT OUTER JOIN', $firstTable, $firstTableColumn, $secondTable, $secondTableColumn);
        return $this;
    }

    /**
     * Adds a order by clause
     *
     * @param string $columns
     * @param string $sort
     * 
     * @return $this
     */
    public function orderBy($columns, $sort = 'ASC')
    {
        $this->_query .= ' ORDER BY ' . $columns . ' ' . $sort;
        return $this;
    }

    /**
     * Adds a group by clause
     *
     * @param string $columns
     *
     * @return $this
     */
    public function groupBy($columns)
    {
        $this->_query .= ' GROUP BY ' . $columns;
        return $this;
    }

    /**
     * Adds a limit by clause
     *
     * @param string $value
     *
     * @return $this
     */
    public function limit($value)
    {
        $this->_query .= ' LIMIT ' . $value;
        return $this;
    }

    /**
     * Adds a offset by clause
     *
     * @param string $value
     *
     * @return $this
     */
    public function offset($value)
    {
        $this->_query .= ' OFFSET ' . $value;
        return $this;
    }

    /**
     * Executes select statement
     * expecting multiple results
     *
     * @return array
     */
    public function all()
    {
        try {
            $statement = $this->conn()->prepare($this->_query);
            $statement->execute();

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }

            return $data;
        } catch (\PDOException $e) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Executes select statement
     * expecting one results
     *
     * @return array
     */
    public function one()
    {
        try {
            $statement = $this->conn()->prepare($this->_query);
            $statement->execute();

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }

            return !empty($data[0]) ? $data[0] : [];
        } catch (\PDOException $e) {
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Executes whatever query you may pass in
     *
     * @return void
     */
    public function execute()
    {
        $db = $this->conn();
        try {

            $db->beginTransaction();
            $db->prepare($this->_query)->execute();
            $lastInsertedId = $db->lastInsertId();

            $db->commit();

            return $lastInsertedId;
        } catch (\PDOException $e) {
            $db->rollBack();
            Helpers::renderAsJson([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adds a WHERE clause
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     * @param string $prefix
     *
     * @return $this
     */
    private function _whereClause($fieldName, $value, $operator = '=', $prefix = 'WHERE')
    {
        $this->_query .= ' ' . $prefix . ' ' . $fieldName . ' ' . $operator . ' ' . (is_numeric($value) ? $value : '"' . $value . '"');
        return $this;
    }

    /**
     * Adds a JOIN clause
     *
     * @param string $type
     * @param string $secondTable
     * @param string $secondTableColumn
     * @param string $firstTableColumn
     *
     * @return void
     */
    private function _joinClause($type, $firsTable, $firstTableColumn, $secondTable, $secondTableColumn)
    {
        $this->_query .= ' ' . $type . ' ' . $secondTable . ' ON ' . $firsTable . '.' . $firstTableColumn . ' = ' . $secondTable . '.' . $secondTableColumn;
    }

    /**
     * Returns the last key of an array
     *
     * @param array $data
     * @return string
     */
    private function _lastKey($data = array())
    {
        $data1 = array_keys($data);
        $data2 = array_pop($data1);
        return $data2;
    }

}
