<?php

namespace Controllers\Rest\Records;

use Slim\Http\Request;
use Slim\Http\Response;

class GetAllRecords
{

    public function __construct()
    {

    }


    public function __invoke(Request $request, Response $response, array $args)
    {
        $mysqli = new \mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);
        if ($mysqli->connect_errno) {
            return $response->withJson([
                'error' => 'Не удалось подключиться к mysql',
                'reason' => $mysqli->connect_error
            ]);
        }

        $query = 'SELECT * FROM record';
        $data = [];
        if ($result = $mysqli->query($query, MYSQLI_STORE_RESULT)) {
            if ($result->num_rows !== 0) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
            }
            $result->free();
        } else {
            return $response->withJson([
                'error' => 'Не удалось получить данные по запросу: ' . $query,
                'reason' => $mysqli->error
            ]);
        }

        $mysqli->close();

        return $response->withJson($data);
    }
}