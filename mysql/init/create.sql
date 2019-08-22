# Скрипт для инициализации БД

USE docker;
SET charset utf8;

CREATE TABLE `records`
(
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `text` TEXT NOT NULL,
    `authors` VARCHAR(255) NOT NULL
);

INSERT INTO `records` (text, authors)
VALUES
('Запись №1', 'Авторы к записи 1'),
('Запись №2', 'Авторы к записи 2'),
('Запись №3', 'Авторы к записи 3');

