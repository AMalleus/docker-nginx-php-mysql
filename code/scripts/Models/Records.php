<?php

namespace Models;

use Database\DatabaseInterface;
use Database\Query;

class Records implements CRUDInterface
{

    /** @var string */
    public const TABLE = 'records';

    /** @var DatabaseInterface */
    private $db;

    /**
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function getOne(array $where = []): array
    {
        $result = $this->get($where);
        if (empty($result)) {
            throw new \Exception('Нет записи с таким id');
        }

        return reset($result);
    }

    /**
     * @param array $where
     * @return array
     * @throws \Exception
     */
    public function get(array $where = []): array
    {
        $query = new Query($this->db);
        $query->table(self::TABLE, 'r')
            ->fields(['r.*'])
            ->where($where);

        try {
            $result = $this->db->select($query);
        } catch (\Exception $e) {
            throw new \Exception('Не удалось получить записи', 0, $e);
        }

        return $result;
    }

    /**
     * Возвращает id записи
     *
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function insert(array $data): int
    {
        try {
            return $this->db->insert(self::TABLE, $data);
        } catch (\Exception $e) {
            throw new \Exception('Не удалось сохранить запись', 0, $e);
        }
    }

    /**
     * Возвращает количество затронутых записей
     *
     * @param int $id
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function update(int $id, array $data): int
    {
        if (empty($this->getOne(['id' => $id]))) {
            throw new \Exception('Такой записи не существует');
        }

        try {
            return $this->db->update(self::TABLE, $data, ['id' => $id]);
        } catch (\Exception $e) {
            throw new \Exception('Не удалось сохранить запись', 0, $e);
        }
    }

    /**
     * Возвращает количество затронутых записей
     *
     * @param array $data
     * @return int
     */
    public function delete(array $data): int
    {
        return $this->db->delete(self::TABLE, $data);
    }


    public function createTable(): bool
    {
        return $this->db->executeRaw(
            'CREATE TABLE records (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `text` TEXT NOT NULL,
  `authors` VARCHAR(255) NOT NULL
);'
        );
    }
}