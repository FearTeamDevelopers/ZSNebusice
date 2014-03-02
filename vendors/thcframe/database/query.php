<?php

namespace THCFrame\Database;

use THCFrame\Core\Base as Base;
use THCFrame\Core\ArrayMethods as ArrayMethods;
use THCFrame\Database\Exception as Exception;

/**
 * Description of Query
 *
 * @author Tomy
 */
class Query extends Base
{

    /**
     * @readwrite
     */
    protected $_connector;

    /**
     * @read
     */
    protected $_from;

    /**
     * @read
     */
    protected $_fields;

    /**
     * @read
     */
    protected $_limit;

    /**
     * @read
     */
    protected $_offset;

    /**
     * @read
     */
    protected $_order = array();

    /**
     * @read
     */
    protected $_groupby;

    /**
     * @read
     */
    protected $_join = array();

    /**
     * @read
     */
    protected $_where = array();

    /**
     * @read
     */
    protected $_orwhere = array();

    /**
     * @read
     */
    protected $_having = array();

    /**
     * 
     * @param type $method
     * @return \THCFrame\Database\Exception\Implementation
     */
    protected function _getImplementationException($method)
    {
        return new Exception\Implementation(sprintf("%s method not implemented", $method));
    }

    /**
     * 
     * @param type $value
     * @return string
     */
    protected function _quote($value)
    {
        $connector = $this->getConnector();

        if (is_string($value)) {

            $escaped = $connector->escape($value);
            return "'{$escaped}'";
        }

        if (is_array($value)) {
            $buffer = array();

            foreach ($value as $i) {
                array_push($buffer, $this->_quote($i));
            }

            $buffer = join(", ", $buffer);
            return "({$buffer})";
        }

        if (is_null($value)) {
            return "NULL";
        }

        if (is_bool($value)) {
            return (int) $value;
        }

        return $connector->escape($value);
    }

    /**
     * 
     * @return type
     */
    protected function _buildSelect()
    {
        $fields = array();
        $where = $order = $limit = $join = "";
        $template = "SELECT %s FROM %s %s %s %s %s %s %s";

        foreach ($this->fields as $table => $_fields) {
            foreach ($_fields as $field => $alias) {
                if (is_string($field)) {
                    $fields[] = "{$field} AS {$alias}";
                } else {
                    $fields[] = $alias;
                }
            }
        }

        $joinedFields = join(", ", $fields);

        $_join = $this->join;
        if (!empty($_join)) {
            $join = join(" ", $_join);
        }

        $_where = $this->where;
        $_orwhere = $this->orwhere;
        $joinedWhere = '';
        if (!empty($_where)) {
            $joinedWhere .= join(" AND ", $_where);
        }

        if (!empty($_orwhere)) {
            $joinedWhere .= join(" OR ", $_orwhere);
        }

        if ($joinedWhere != '') {
            $where = "WHERE {$joinedWhere}";
        }

        $_groupBy = $this->groupby;
        $groupBy = $having = '';
        if (!empty($_groupBy)) {
            $groupBy = "GROUP BY {$_groupBy}";

            $_having = $this->having;
            if (!empty($_having)) {
                $joinedHaving = join(" AND ", $_having);
                $having = "HAVING {$joinedHaving}";
            }
        }

        $_order = $this->order;
        if (!empty($_order)) {
            $joindeOrder = join(", ", $_order);
            $order = "ORDER BY {$joindeOrder}";
        }

        $_limit = $this->limit;
        if (!empty($_limit)) {
            $_offset = $this->offset;

            if ($_offset) {
                $limit = "LIMIT {$_limit}, {$_offset}";
            } else {
                $limit = "LIMIT {$_limit}";
            }
        }

        return sprintf($template, $joinedFields, $this->from, $join, $where, $groupBy, $having, $order, $limit);
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    protected function _buildInsert($data)
    {
        $fields = array();
        $values = array();
        $template = "INSERT INTO `%s` (`%s`) VALUES (%s)";

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $values[] = $this->_quote($value);
        }

        $fields = join("`, `", $fields);
        $values = join(", ", $values);

        return sprintf($template, $this->from, $fields, $values);
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    protected function _buildUpdate($data)
    {
        $parts = array();
        $where = $limit = "";
        $template = "UPDATE %s SET %s %s %s";

        foreach ($data as $field => $value) {
            $parts[] = "{$field} = " . $this->_quote($value);
        }

        $parts = join(", ", $parts);

        $_where = $this->where;
        if (!empty($_where)) {
            $joined = join(", ", $_where);
            $where = "WHERE {$joined}";
        }

        $_limit = $this->limit;
        if (!empty($_limit)) {
            $_offset = $this->offset;
            $limit = "LIMIT {$_limit} {$_offset}";
        }

        return sprintf($template, $this->from, $parts, $where, $limit);
    }

    /**
     * 
     * @return type
     */
    protected function _buildDelete()
    {
        $where = $limit = "";
        $template = "DELETE FROM %s %s %s";

        $_where = $this->where;
        if (!empty($_where)) {
            $joined = join(", ", $_where);
            $where = "WHERE {$joined}";
        }

        $_limit = $this->limit;
        if (!empty($_limit)) {
            $_offset = $this->offset;
            $limit = "LIMIT {$_limit} {$_offset}";
        }

        return sprintf($template, $this->from, $where, $limit);
    }

    /**
     * 
     * @return type
     * @throws Exception\Connector
     */
    public function getConnector()
    {
        if (empty($this->_connector)) {
            $database = Registry::get("database");

            if (!$database) {
                throw new Exception\Connector("No connector availible");
            }

            $this->_connector = $database->initialize();
        }

        return $this->_connector;
    }

    /**
     * 
     * @param type $data
     * @return int
     * @throws Exception\Sql
     */
    public function save($data)
    {
        $isInsert = count($this->_where) == 0;

        if ($isInsert) {
            $sql = $this->_buildInsert($data);
        } else {
            $sql = $this->_buildUpdate($data);
        }

        $result = $this->connector->execute($sql);

        if ($result === false) {
            throw new Exception\Sql(sprintf("SQL: %s", $this->connector->getLastError()));
        }

        if ($isInsert) {
            return $this->connector->lastInsertId;
        }

        return 0;
    }

    /**
     * 
     * @return type
     * @throws Exception\Sql
     */
    public function delete()
    {
        $sql = $this->_buildDelete();
        $result = $this->connector->execute($sql);

        if ($result === false) {
            throw new Exception\Sql(sprintf("SQL: %s", $this->connector->getLastError()));
        }

        return $this->connector->affectedRows;
    }

    /**
     * 
     * @param type $from
     * @param type $fields
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function from($from, $fields = array("*"))
    {
        if (empty($from)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_from = $from;

        if ($fields) {
            $this->_fields[$from] = $fields;
        }

        return $this;
    }

    /**
     * 
     * @param type $join
     * @param type $on
     * @param type $fields
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function join($join, $on, $alias = null, $fields = array())
    {
        if (empty($join)) {
            throw new Exception\Argument("Invalid argument");
        }

        if (empty($on)) {
            throw new Exception\Argument("Invalid argument");
        }

        if (NULL !== $alias) {
            $this->_fields += array($alias => $fields);
            $this->_join[] = "JOIN {$join} {$alias} ON {$on}";
        } else {
            $this->_fields += array($join => $fields);
            $this->_join[] = "JOIN {$join} ON {$on}";
        }

        return $this;
    }

    /**
     * 
     * @param type $limit
     * @param type $page
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function limit($limit, $page = 1)
    {
        if (empty($limit)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_limit = $limit;

        if ($page - 1 < 0) {
            $this->_offset = 0;
        } else {
            $this->_offset = $limit * ($page - 1);
        }

        return $this;
    }

    /**
     * 
     * @param type $order
     * @param type $direction
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function order($order, $direction = "asc")
    {
        if (empty($order)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_order[] = $order . " " . $direction;

        return $this;
    }

    /**
     * 
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function where()
    {
        $arguments = func_get_args();

        if (count($arguments) < 1) {
            throw new Exception\Argument("Invalid argument");
        }

        $arguments[0] = preg_replace("#\?#", "%s", $arguments[0]);

        foreach (array_slice($arguments, 1, null, true) as $i => $parameter) {
            $arguments[$i] = $this->_quote($arguments[$i]);
        }

        $this->_where[] = call_user_func_array("sprintf", $arguments);

        return $this;
    }

    /**
     * 
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function orwhere()
    {
        $arguments = func_get_args();

        if (count($arguments) < 1) {
            throw new Exception\Argument("Invalid argument");
        }

        $arguments[0] = preg_replace("#\?#", "%s", $arguments[0]);

        foreach (array_slice($arguments, 1, null, true) as $i => $parameter) {
            $arguments[$i] = $this->_quote($arguments[$i]);
        }

        $this->_orwhere[] = call_user_func_array("sprintf", $arguments);

        return $this;
    }

    /**
     * 
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function having()
    {
        $arguments = func_get_args();

        if (count($arguments) < 1) {
            throw new Exception\Argument("Invalid argument");
        }

        $arguments[0] = preg_replace("#\?#", "%s", $arguments[0]);

        foreach (array_slice($arguments, 1, null, true) as $i => $parameter) {
            $arguments[$i] = $this->_quote($arguments[$i]);
        }

        $this->_having[] = call_user_func_array("sprintf", $arguments);

        return $this;
    }

    /**
     * 
     * @param type $field
     * @return \THCFrame\Database\Query
     * @throws Exception\Argument
     */
    public function groupby($field)
    {
        if (empty($field)) {
            throw new Exception\Argument("Invalid argument");
        }

        $this->_groupby = $field;

        return $this;
    }

    /**
     * 
     * @return type
     */
    public function first()
    {
        $limit = $this->_limit;
        $offset = $this->_offset;

        $this->limit(1);

        $all = $this->all();
        $first = ArrayMethods::first($all);

        if ($limit) {
            $this->_limit = $limit;
        }
        if ($offset) {
            $this->_offset = $offset;
        }

        return $first;
    }

    /**
     * 
     * @return type
     */
    public function count()
    {
        $limit = $this->limit;
        $offset = $this->offset;
        $fields = $this->fields;

        $this->_fields = array($this->from => array("COUNT(1)" => "rows"));

        $this->limit(1);
        $row = $this->first();

        $this->_fields = $fields;

        if ($fields) {
            $this->_fields = $fields;
        }
        if ($limit) {
            $this->_limit = $limit;
        }
        if ($offset) {
            $this->_offset = $offset;
        }

        return $row["rows"];
    }

    /**
     * 
     * @return type
     */
    public function assembleSelect()
    {
        $sql = $this->_buildSelect();
        return (string) $sql;
    }

}
