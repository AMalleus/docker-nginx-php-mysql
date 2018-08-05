<?php

namespace Database;

/**
 * affected_rows = 0, если ничего не произошло
 *      1, на insert
 *      2, на insert on duplicate update
 *
 * Class MySQL
 * @package Database
 */
class MySQL implements DatabaseInterface
{

    /** @var array */
    const FIELDS = [
        'text' => 'string',
        'varchar' => 'string',
        'int' => 'int',
    ];

    /** @var \mysqli */
    private $connect;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->connect();
    }

    /**
     * @throws \Exception
     */
    private function connect()
    {
        if (!$this->connect) {
            $this->connect = new \mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);
            if ($this->connect->connect_errno) {
                throw new \Exception($this->connect->connect_error);
            }
        }
    }

    /**
     * escape спец символов
     *
     * @param string $string
     * @return string
     */
    public function escape(string $string)
    {
        return $this->connect->real_escape_string($string);
    }

    /**
     * Вернет записи по запросу
     *
     * @param Query $query
     * @return array
     * @throws \Exception
     */
    public function select(Query $query): array
    {
        try {
            $query = $query->assemble();
        } catch (\Exception $e) {
            throw new \Exception('Не удалось собрать запрос', 0, $e);
        }

        $result = $this->connect->query($query, MYSQLI_STORE_RESULT);
        if (!$result) {
            $error = $this->connect->error;
            throw new \Exception($error);
        }

        if ($result->num_rows === 0) {
            $result->free();
            return [];
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);

        $types = [];
        $fields = $result->fetch_fields();
        foreach ($fields as $field) {
            $types[$field->name] = $this->mapType($field->type);
        }

        foreach ($data as &$item) {
            foreach ($item as $key => &$value) {
                settype($value, $types[$key]);
            }
        }

        return $data;
    }

    /**
     * Вернет id записи
     *
     * @param string $table
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function insert(string $table, array $data): int
    {
        [$values, $primary] = $this->insertCommon($table, $data);

        $query = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', array_keys($values)),
            implode(', ', array_values($values))
        );

        if (!$this->connect->query($query)) {
            $error = $this->connect->error;
            throw new \Exception($error);
        }

        return $this->connect->insert_id;
    }

    /**
     * Вернет id записи
     *
     * @param string $table
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function upsert(string $table, array $data): int
    {
        [$values, $primary] = $this->insertCommon($table, $data);

        $update = [];
        foreach ($values as $key => $value) {
            if ($key != $primary) {
                $update[] = sprintf('%s=%s', $key, $value);
            }
        }
        $query = sprintf('INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $table,
            implode(', ', array_keys($values)),
            implode(', ', $values),
            implode(', ', $update)
        );

        if (!$this->connect->query($query)) {
            $error = $this->connect->error;
            throw new \Exception($error);
        }

        return $this->connect->insert_id;
    }

    public function executeRaw(string $raw): bool
    {
        if ($this->connect->query($raw) === true) {
            $this->connect->close();
            return true;
        }

        throw new \Exception($this->connect->error);
    }

    /**
     * Возвращает количество затронутых записей
     *
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int
     * @throws \Exception
     */
    public function update(string $table, array $data, array $where): int
    {
        [$values, $primary] = $this->insertCommon($table, $data);

        $update = [];
        foreach ($values as $key => $value) {
            if ($key != $primary) {
                $update[] = sprintf('%s=%s', $key, $value);
            }
        }

        $whereFormatted = [];
        foreach ($where as $key => $value) {
            $whereFormatted[] = sprintf('%s=%s',
                $this->escape($key),
                sprintf('"%s"', $this->escape($value))
            );
        }

        $query = sprintf('UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $update),
            implode(', ', $whereFormatted)
        );

        if (!$this->connect->query($query)) {
            $error = $this->connect->error;
            throw new \Exception($error);
        }

        return $this->connect->affected_rows;
    }

    /**
     * common для insert, insert on duplicate update и update
     *
     * @param string $table
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function insertCommon(string $table, array $data): array
    {
        $fields = $this->getTableFields($table);
        $primary = $this->getPrimaryKey($fields);

        $diff = array_diff_key($fields, $data);

        if (!empty($diff)) {
            foreach ($diff as $item) {
                if ($item['Field'] !== $primary) {
                    throw new \Exception('WTF');
                }
            }
        }

        $types = [];
        foreach ($fields as $item) {
            foreach (self::FIELDS as $find => $type) {
                if (strpos($item['Type'], $find) !== false) {
                    $types[$item['Field']] = $type;
                }
            }
            if (empty($types[$item['Field']])) {
                throw new \Exception('Неизвестный тип ' . $item['Type']);
            }
        }

        $values = [];
        foreach ($data as $key => $value) {
            $values[$this->escape($key)] = $types[$key] == 'string' ?
                sprintf('"%s"', $this->escape($value)) : $this->escape($value);
        }

        return [$values, $primary];
    }

    /**
     * Возвращает количество затронутых записей
     *
     * @param string $table
     * @param array $where
     * @return int
     * @throws \Exception
     */
    public function delete(string $table, array $where): int
    {
        $whereFormatted = [];
        foreach ($where as $key => $value) {
            $whereFormatted[] = sprintf('%s=%s',
                $this->escape($key),
                sprintf('"%s"', $this->escape($value))
            );
        }

        $query = sprintf('DELETE FROM %s WHERE %s', $table, implode(' AND ', $whereFormatted));

        if (!$this->connect->query($query)) {
            $error = $this->connect->error;
            throw new \Exception($error);
        }

        return $this->connect->affected_rows;
    }

    public function mapType($field_type)
    {
        switch ($field_type)
        {
            case MYSQLI_TYPE_DECIMAL:
            case MYSQLI_TYPE_NEWDECIMAL:
            case MYSQLI_TYPE_FLOAT:
            case MYSQLI_TYPE_DOUBLE:
                return 'float';

            case MYSQLI_TYPE_BIT:
            case MYSQLI_TYPE_TINY:
            case MYSQLI_TYPE_SHORT:
            case MYSQLI_TYPE_LONG:
            case MYSQLI_TYPE_LONGLONG:
            case MYSQLI_TYPE_INT24:
            case MYSQLI_TYPE_YEAR:
            case MYSQLI_TYPE_ENUM:
                return 'int';

            case MYSQLI_TYPE_TIMESTAMP:
            case MYSQLI_TYPE_DATE:
            case MYSQLI_TYPE_TIME:
            case MYSQLI_TYPE_DATETIME:
            case MYSQLI_TYPE_NEWDATE:
            case MYSQLI_TYPE_INTERVAL:
            case MYSQLI_TYPE_SET:
            case MYSQLI_TYPE_VAR_STRING:
            case MYSQLI_TYPE_STRING:
            case MYSQLI_TYPE_CHAR:
            case MYSQLI_TYPE_GEOMETRY:
                return 'string';

            case MYSQLI_TYPE_TINY_BLOB:
            case MYSQLI_TYPE_MEDIUM_BLOB:
            case MYSQLI_TYPE_LONG_BLOB:
            case MYSQLI_TYPE_BLOB:
                return 'string';

            default:
                trigger_error("unknown type: $field_type");
                return 'string';
        }
    }

    private function getTableFields(string $table): array
    {
        $schema = $this->getSchema($table);

        $fields = [];
        foreach ($schema as $item) {
            $fields[$item['Field']] = $item;
        }
        return $fields;
    }

    private function getPrimaryKey(array $fields): ?string
    {
        foreach ($fields as $item) {
            if ($item['Key'] == 'PRI') {
                return $item['Field'];
            }
        }

        return null;
    }

    private function getSchema(string $table): array
    {
        $result = $this->connect->query("DESCRIBE {$table}");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}