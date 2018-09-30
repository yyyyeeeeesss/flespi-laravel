# Сервис для работы с postgres через mqtt-broker
Сервис админ панели здесь - https://codepen.io/yyyyeeeeesss/pen/PdrdYW

# Миграции (генерация 10 000 пользователей)
docker-compose exec php /usr/bin/php /var/flespi/artisan migrate

# Запуск приложения
docker-compose exec php /usr/bin/php /var/flespi/artisan bus:listen