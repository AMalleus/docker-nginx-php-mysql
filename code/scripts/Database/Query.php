<?php

namespace Database;

class Query
{

    /** @var DatabaseInterface */
    private $db;

    /** @var string */
    private $table;

    /** @var string */
    private $tableAlias;

    /** @var array */
    private $fields;

    /** @var array */
    private $where = [];

    /** @var array */
    private $order = [];

    /** @var array */
    private $group = [];

    /** @var int */
    private $limit;

    /** @var int */
    private $offset;

    /** @var array */
    private $join = [];

    /**
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $table
     * @param string $tableAlias
     * @return Query
     */
    public function table(string $table, string $tableAlias = null): Query
    {
        $this->table = $table;
        $this->tableAlias = $tableAlias;
        return $this;
    }

    /**
     * @param array $fields
     * @return Query
     */
    public function fields(array $fields): Query
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array $where
     * @return Query
     */
    public function where(array $where): Query
    {
        $this->where = array_merge($this->where, $where);
        return $this;
    }

    /**
     * @param array $order
     * @return Query
     */
    public function order(array $order): Query
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param array $group
     * @return Query
     */
    public function group(array $group): Query
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param int $limit
     * @return Query
     */
    public function limit(int $limit): Query
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return Query
     */
    public function offset(int $offset): Query
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param array $join
     * @return Query
     */
    public function join(array $join): Query
    {
        $this->join = $join;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function assemble(): string
    {
        if (empty($this->table) || empty($this->fields)) {
            throw new \Exception('Не указана главная таблица и поля');
        }

        $fields = implode(', ', $this->fields);
        $table = !empty($this->tableAlias) ? "{$this->table} AS {$this->tableAlias}" : $this->table;
        $query = sprintf('SELECT %s FROM %s', $this->db->escape($fields), $this->db->escape($table));

        if (!empty($this->where)) {
            $where = [];
            foreach ($this->where as $key => $value) {
                $where[] = sprintf('(%s="%s")', $this->db->escape($key), $this->db->escape($value));
            }
            $query .= sprintf(' WHERE %s', implode(' AND ', $where));
        }

        return $query;
    }
}