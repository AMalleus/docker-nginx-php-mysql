<?php

namespace Controllers\Rest\Records;

use Slim\Http\Request;
use Slim\Http\Response;

class CreateRecord
{

    public function __construct()
    {

    }


    public function __invoke(Request $request, Response $response, array $args)
    {
        $params = $request->getParsedBody();
        if (empty($params) || empty($params['text'])) {
            return $response->withJson([
                'error' => 'Не удалось создать запись',
                'reason' => 'Не хватает данных в POST: \'text\''
            ]);
        }

        $mysqli = new \mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);
        if ($mysqli->connect_errno) {
            return $response->withJson([
                'error' => 'Не удалось подключиться к mysql',
                'reason' => $mysqli->connect_error
            ]);
        }

        $query = sprintf(
            'INSERT INTO record (text, authors) VALUES ("%s", "%s");',
            $mysqli->escape_string($params['text']),
            $mysqli->escape_string($params['authors'] ?? '')
        );
        if ($result = $mysqli->query($query)) {
            $data = [
                'success' => 'Запись успешно создана',
                'reason' => 'ID записи: ' . $mysqli->insert_id
            ];
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