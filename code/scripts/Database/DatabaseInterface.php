<?php

namespace Database;

interface DatabaseInterface
{

    public function escape(string $string);

    public function select(Query $query): array;

    public function insert(string $table, array $data): int;

    public function update(string $table, array $data, array $where): int;

    public function upsert(string $table, array $data): int;

    public function delete(string $table, array $where): int;

    public function executeRaw(string $raw): bool;
}