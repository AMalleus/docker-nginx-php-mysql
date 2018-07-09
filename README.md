Веб-сервер в докер контейнерах в связке nginx, php + xdebug, mysql 

Сборка

```
docker-compose build
docker-compose up -d
```

После этого на локальной машине добавляем DNS: php-docker.local 127.0.0.1 . 
Хотя и обычный localhost сойдет

Выполняемый PHP код смонтирован к ./code . Точка входа в public/index.php

Заходим в браузере по http://php-docker.local:8080 и попадаем в index.php

Статика http://php-docker.local:8080/static/1.jpg
