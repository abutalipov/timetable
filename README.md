##Проект учета расписания
    php не ниже 7 версии
    mysql не ниже 5,7
#installing
    устанавливаем связи
    composer install
    
    указать в конфиге данные базы данных в .env
    
    обновляем конфиги
    php artisan optimize

    делаем миграцию с сидами
    php artisan migrate --seed
#пользователь
    логин пароль
    admin/admin
#особенности 
    в любом запросе к api должен присутствовать заголовок Content-Type с значением application/json
    
    для отладки рекомендуется использовать по Insomnia
    для работы с документацией API используется либо веб ресурс https://editor.swagger.io/ либо ПО stoplight
    REST API находится в файле rest.yaml
