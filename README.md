# OYAMA

## BE

### Step 1:
- Install composer. Link: https://getcomposer.org/doc/00-intro.md#using-the-installer


### Step 2:
- Run cmd: composer install


### Step 4:
- Run cmd: php artisan storage:link


### Step 5: 

- Set email to .env (copy from .env.example and create schema mysql)
- MAIL_MAILER=smtp
- MAIL_HOST=smtp.gmail.com
- MAIL_PORT=587
- MAIL_USERNAME=
- MAIL_PASSWORD=
- MAIL_ENCRYPTION=tls
- MAIL_FROM_ADDRESS=null
- MAIL_FROM_NAME=


### Step 6: setting DB
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=
- DB_USERNAME=
- DB_PASSWORD=


### Step 7: Generation APP key
- Run cmd: php artisan key:generate


### Step 8: migrate DB
- Run cmd: php artisan migrate:fresh --seed


### Step 9: reload config
- Run cmd: php artisan config:cache


### Step 10:
- Run cmd: php artisan serve