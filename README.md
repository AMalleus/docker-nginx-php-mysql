Веб-сервер в докер контейнерах в связке nginx, php + xdebug, mysql 

Команды:

```
Сборка контейнеров:
docker-compose build

Инициализация и поднятие контейнера. -d для detached:   
docker-compose up -d

Остановить контейнеры:
docker-compose stop

Удалить стопнутые контейнеры:
docker-compose rm
```

После этого на локальной машине добавляем DNS: php-docker.local 127.0.0.1 . 
Хотя и обычный localhost сойдет

Выполняемый PHP код смонтирован к ./code . Точка входа в public/index.php

Заходим в браузере по http://php-docker.local:8080 и попадаем в index.php

Статика http://php-docker.local:8080/static/1.jpg

1. mysql/init/custom.cnf :

Кастомный конфиг для mysql

2. mysql/init/create.sql :

Кастомный init для базы, если необходимо что-то сделать в БД, например, создать таблицы. 
Срабатывает каждый раз, когда база заново инициализируется.

3. rm -rf mysql/data/* :

Почистить все содержимое БД. После этого БД заново инициализируется при инициализации контейнера
