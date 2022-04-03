web: vendor/bin/heroku-php-nginx -C nginx_app.conf public/
queue-worker: php artisan queue:work --queue=high,low
schedule-worker: php artisan schedule:work
