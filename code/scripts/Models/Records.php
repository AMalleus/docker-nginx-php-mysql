<?php

namespace Models;

use Database\DatabaseInterface;

class Records implements CRUDInterface
{

    /** @var string */
    private const TABLE = 'records';

    /** @var DatabaseInterface */
    private $db;

    /**
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }
}