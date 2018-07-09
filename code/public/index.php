<?php

DEFINE('ROOT', dirname(dirname(__FILE__)));

require ROOT . '/vendor/autoload.php';

$app = new Slim\App();

$app->get('[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
    $params = $request->getQueryParams();

    $params['first'] = 1;

    return $response->withJson($params);
});

$app->group('/records', function() {
    $this->get('[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        $mysqli = new mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);
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
    });

    $this->get('/{recordId:\d+}[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        $mysqli = new mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);
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
    });

    $this->post('/create[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        $params = $request->getParsedBody();
        if (empty($params) || empty($params['text'])) {
            return $response->withJson([
                'error' => 'Не удалось создать запись',
                'reason' => 'Не хватает данных в POST: \'text\''
            ]);
        }

        $mysqli = new mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);
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
    });

    $this->post('/init[/]', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
        $mysqli = new mysqli('mysql', $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'], $_ENV['MYSQL_DATABASE']);

        /* проверка соединения */
        if ($mysqli->connect_errno) {
            return $response->withJson([
                'error' => 'Не удалось подключиться к mysql',
                'reason' => $mysqli->connect_error
            ]);
        }

        $query = 'CREATE TABLE record (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `text` TEXT NOT NULL,
    `authors` VARCHAR(255) NOT NULL
);';
        if ($mysqli->query($query) === true) {
            $data = [
                'success' => 'Таблица успешно инициализирована',
                'reason' => ''
            ];
        } else {
            $data = [
                'error' => 'Не удалось создать таблицу',
                'reason' => $mysqli->error
            ];
        }

        $mysqli->close();

        return $response->withJson($data);
    });
});

$app->run();
