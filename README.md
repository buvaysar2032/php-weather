# Yii2 Backend Template

Основа для backend проекта основанная на Yii2 Framework с интегрированным VueJS
Описание API: [phpDocumentor](https://docs.dev.peppers-studio.ru/yii-template/)

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/phpunit/phpunit/php)

## Требования к ПО

1. [PHP](https://www.php.net/downloads.php) версии 8.2 или новее (рекомендуется)

    Для установки чистого PHP на Windows необходимо:
    1. Распаковать архив
    2. Добавить распакованную папку (где находится php.exe) в [PATH](https://www.php.net/manual/ru/faq.installation.php#faq.installation.addtopath)
    3. Переименовать `php.ini-development` в `php.ini`
    4. Включить дополнительные расширения в php.ini:
        - bz2
        - curl
        - ctype
        - mbstring
        - exif
        - fileinfo
        - gd
        - imap
        - intl
        - mysqli
        - odbc
        - openssl
        - pdo_mysql
        - soap
        - sockets
        - sodium
        - xml или xsl
2. [NodeJS](https://nodejs.org/en/download/) версии 20 или новее
3. MySQL от версии 5.5 или MariaDB

## Инициализация

Запустить php скрипт [init-all](#скрипт-быстрого-разворачивания)

**Важно!** При применении этой команды могут затереться измененные руками файлы на сервере. Файлы для перезаписи берутся отсюда: `/environments/`
Чтобы файлы не менялись - нужно добавить флаг 'u' `php init-all -u`

## Запуск локального сервера

```shell
npm run start
```
Локальный сервер будет доступен по ссылке <http://localhost:9001>

Если используете Open Server и нужен только **hot-reload**:

```shell
npm run serve-js
```

## Разворачивание проекта на сервере

1. Слить содержимое в корень сайта
    - Вариант 1: скачать архив, загрузить файлы на хостинг
    - Вариант 2: напрямую копировать содержимое мастер-ветки репозитория на хостинг

2. Прописать webroot на htdocs

    2.1 Проверить что в Apache включен modrewrite и AllowOverwriteAll

    2.2 Если проект устанавливается на Nginx, то нужно настроить редирект всех неизвестных путей в index.php для локаций:
    - `/`
    - `/admin`
    - `/api`

3. Инициализировать приложение с помощью [init-all](#скрипт-быстрого-разворачивания):

    ```shell
    php init-all --env prod -a
    ```

    Возможные окружения:
    - **peppers** - Песочница PEPPERS
    - **dev** - DEV-Сервер Клиента
    - **prod** - PROD-Сервер Клиента

4. Для корректного обнаружения домена в консольном приложении и в очереди нужно указать URl в ENV переменной `APP_DOMAIN` или в `domainUrl` файла:

    >common\config\params-local.php

-------------------
После указанных выше действий панель администратора будет доступна по адресу `/admin/`, а точка входа для api - `/api/v1/`

В DEV окружении по ссылке `/api/v1/site/docs` доступен тестер методов API в [Swagger](https://petstore.swagger.io/) интерфейсе

Для отправки почты нужно внести настройки соединения в разделе "Настройки" в Админ.панели

## Скрипт быстрого разворачивания

В проекте есть скрипт для разворачивания проекта одной командой

```shell
php init-all
# или батник для Windows
init-all
```

Скрипт автоматически устанавливает и обновляет composer, применяет миграции и делает сборку JS

Он имеет следующие опции:

- `--env` - окружение, возможные значения: *peppers*, *dev*, *prod*
- `--domain` - доменное имя вместе с протоколом, которое приложение будет считать "своим" (необходимо для корректного обнаружения домена при отложенной отправке почты)
- `--db-host=127.0.0.1:3306 --db-name=some-db --db-user=some-user --db-password=some-password` - конфигурация БД, не задавать при вызове в docker контейнере
- `-a` - флаг для создания админа после выполнения всех инициализирующих скриптов
- `-u` - флаг для безопасного обновления уже развернутого проекта (будут применены новые миграции, а локальные конфигурации не будут перетерты)

Скрипт **НЕ делает**:

- Настройку ключей соц. сетей и чат бота
- Настройку доступов к почте
- Применение бекапов

## Https

Если сервер не может предоставить **https**, то в настройках для всех видов куки надо убрать флаг `secure`!

## Docker

1. Установить [docker](https://docs.docker.com/get-docker/) и [docker-compose](https://docs.docker.com/compose/install/)

2. Выполнить следующие команды для разворачивания контейнеров:

   ```shell
   # Сборка контейнеров по конфигурациям (обязательно исключать dev на бою)
   docker-compose -f ./docker-compose.yml -f ./docker-compose.dev.yml up -d
   # Инициализация конфигураций приложения с выбором окружения (полный список опций описан выше)
   # Не использовать флаги конфигурации БД, т.к. они уже настроены внутри конфига docker-compose!
   docker-compose exec -T php ./init-all  -a
   ```

Файлы БД хранятся в `/docker/mysql/data`, а папка проекта подключена внутрь контейнера

## Настройка cron

Примеры для настройки запуска скрипта `daemon/clear-backups` каждые 2 минуты:

Linux

1. Открыть для редактирования crontab:

    ```shell
    sudo crontab -e
    ```

2. Добавить строчку:
    > */2 * * * * <путь до бинарника>/php <root каталог сайта>/yii queue/run

OpenServer

1. Открыть `Настройки`>`Планировщик заданий`.

2. Заполнить все поля и нажать на кнопку `Добавить`.

    > */2 * * * * %realsitedir%\<каталог сайта>\yii queue/run

Isp Manager

1. Открыть `Инструменты`>`Планировщик заданий(cron)`.

2. В открывшимся окне, нажать на кнопку: `Создать`.

3. Прописать саму задачу (команду) и выбрать, период, ее выполнения, после чего, нажать: `OK`.

## Настройка systemd

Чтобы настроить запуск воркеров под управлением systemd, создайте конфиг с именем `yii-queue@.service` в папке `/etc/systemd/system` со следующими настройками:

```ini
[Unit]
Description=Yii Queue Worker %I
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
ExecStart=/usr/bin/php /var/www/<path_to_my_project>/yii queue/listen --verbose
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

Вместо `www-data` укажите пользователя у которого есть полный доступ к файлам сайта и к исполняемому файлу PHP.
Перезагрузите systemd, чтобы он увидел новый конфиг, с помощью команды:

```shell
systemctl daemon-reload
```

Набор команд для управления воркерами:

```shell
# Запустить два воркера
systemctl start yii-queue@1 yii-queue@2
# Получить статус запущенных воркеров
systemctl status "yii-queue@*"
# Остановить один воркер
systemctl stop yii-queue@2
# Остановить все воркеры
systemctl stop "yii-queue@*"
# Добавить воркеры в автозагрузку
systemctl enable yii-queue@1 yii-queue@2
```

## Настройка auto-deploy

В Gitlab репозитории `Settings` -> `CI / CD` -> `Variables` добавить следующие переменные:

- `HOST`: ip адрес сервера
- `FTP_USER`: имя FTP пользователя
- `FTP_PASSWORD`: пароль FTP пользователя
- `SSH_USER`: имя SSH пользователя
- `SSH_PRIVATE_KEY`: приватный ключ

-------------------
[google-docs-icon]: https://icons.iconarchive.com/icons/papirus-team/papirus-apps/24/google-docs-icon.png
![Google Doc][google-docs-icon] [Сводный документ](https://docs.google.com/document/d/1B4hUFv2Ocgokj8TeOJiLHMUbpuWPxg3rlscVOVamTJ8/edit?usp=sharing)

![Google Doc][google-docs-icon] [Настройки шаблона](https://docs.google.com/document/d/1T8tlEjj7-cFaCm-j4Pf9wi2_v0288quXb_U9IGEK0XE/edit#heading=h.9zs6uejuk65n)

![Google Doc][google-docs-icon] [Тестирование с помощью docker-compose](https://docs.google.com/document/d/1dKVLSUFN3Gac5nS_pZwa8JKYG-umfNu0qpfv4GEPXsY/edit)
