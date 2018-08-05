<?php

namespace Models;

interface CRUDInterface
{

    public function getOne(array $where = []): array;

    public function get(array $where = []): array;

    public function insert(array $data): int;

    public function update(int $id, array $data): int;

    public function delete(array $data): int;
}