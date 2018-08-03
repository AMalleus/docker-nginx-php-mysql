<?php

namespace Controllers\Rest\Records;

use Slim\Http\Request;
use Slim\Http\Response;

class GetOneRecord
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

        $query = 'SELECT * FROM record WHERE id = ' . $args['recordId'];
        $data = [];
        if ($result = $mysqli->query($query)) {
            if ($result->num_rows !== 0) {
                $data = $result->fetch_assoc();
            }
            $result->free();
            if (empty($data)) {
                $data = [
                    'error' => 'Не удалось получить данные по запросу: ' . $query,
                    'reason' => 'Ничего не найдено'
                ];
            }
        } else {
            $data = [
                'error' => 'Не удалось получить данные по запросу: ' . $query,
                'reason' => $mysqli->error
            ];
        }

        $mysqli->close();

        return $response->withJson($data);
    }
}