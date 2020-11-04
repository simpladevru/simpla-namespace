<?php

namespace Root\api\components\db;

use Root\api\Database;
use Root\helpers\Debug;

class Query
{
    private $connection;

    private $columns = [];

    private $from    = [];

    private $where   = [];

    private $join    = [];

    private $group   = [];

    private $order   = [];

    private $limit   = [];

    private $offset  = [];

    private $alias;

    public function __construct(Database $connection)
    {
        $this->connection = $connection;
    }

    public function alias(string $alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function select(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function from($table)
    {
        $this->from[] = $table;
        return $this;
    }

    public function join($type, $table, $on = '', $params = [])
    {
        $this->join[] = [$type, $table, $on, $params];
    }

    public function inner_join($table, $on = '', $params = [])
    {
        $this->join('INNER JOIN', $table, $on, $params);
    }

    public function left_join($table, $on = '', $params = [])
    {
        $this->join('LEFT JOIN', $table, $on, $params);
    }

    public function right_join($table, $on = '', $params = [])
    {
        $this->join('RIGHT JOIN', $table, $on, $params);
    }

    public function where($query, $params, $condition = 'AND')
    {
        $this->where[] = ['cnd' => $condition, 'query' => $query, 'params' => (array) $params];
        return $this;
    }

    public function group_by($value)
    {
        $this->group = $value;
        return $this;
    }

    public function order_by($value)
    {
        $this->order[] = $value;
        return $this;
    }

    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }

    public function offset($value)
    {
        $this->offset = $value;
        return $this;
    }

    public function __toString()
    {
        $sql = $this->connection->placehold('SELECT ' . join(', ', $this->columns));
        $sql .= $this->connection->placehold(' FROM ' . join(', ', $this->from));

        if (!empty($this->where)) {
            $sql .= ' WHERE';
            foreach ($this->where as $i => $where) {
                $sql .= (($i > 0) ? ' ' . $where['cnd'] . ' ' : ' ')
                    . $this->connection->placehold($where['query'], ...$where['params']);
            }
        }

        if (!empty($this->limit)) {
            $sql .= $this->connection->placehold(' LIMIT ?', $this->limit);
        }

        if (!empty($this->offset)) {
            $sql .= $this->connection->placehold(' OFFSET ?', $this->offset);
        }

        return $sql;
    }

    public function execute()
    {
        $query = (string) $this;
        if (!$this->connection->query($query)) {
            new \Exception('Wrong query sql ' . $query);
        }
        $this->execute = true;
        return $this;
    }

    public function one($field = null)
    {
        return $this->execute()->connection->result($field);
    }

    public function all($field = null)
    {
        return $this->execute()->connection->results($field);
    }

    public function count($field = '*')
    {
        //  return $this->connection->results($field);
    }
}