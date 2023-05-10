# Определение базового образа
FROM php:7.4-apache

# Копирование файлов в контейнер
COPY index.php /var/www/html/

# Установка MySQL и расширений PHP для работы с MySQL
RUN apt-get update && \
    apt-get install -y mysql-client && \
    docker-php-ext-install mysqli pdo pdo_mysql

# Установка переменных среды для логина и пароля MySQL
ENV MYSQL_USER=myuser \
    MYSQL_PASSWORD=mypassword

# Добавление настроек Apache для PHP
RUN a2enmod rewrite
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Запуск Apache в фоновом режиме
CMD ["apache2-foreground"]