<?php

namespace Middlewares;

use Slim\Http\Request;
use Slim\Http\Response;

class InitRecords
{

    public const TABLE = 'record';

    public function __construct()
    {

    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
        $mysqli = new \mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);

        /* проверка соединения */
        if ($mysqli->connect_errno) {
            return $response->withJson([
                'error' => 'Не удалось подключиться к mysql',
                'reason' => $mysqli->connect_error
            ]);
        }

        $query =
'CREATE TABLE records (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `text` TEXT NOT NULL,
  `authors` VARCHAR(255) NOT NULL
);';

        if ($mysqli->query($query) !== true) {
            $data = [
                'error' => 'Не удалось создать таблицу',
                'reason' => $mysqli->error
            ];
            if (mb_strpos($data['reason'], sprintf('Table \'%s\' already exists', self::TABLE)) === false) {
                $mysqli->close();
                return $response->withJson($data);
            }
        }

        $mysqli->close();

        return $next($request, $response);
    }
}