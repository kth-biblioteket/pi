FROM php:7.3-apache

RUN a2enmod rewrite && \
cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && \
docker-php-ext-install mysqli pdo pdo_mysql && \
apt-get update && \
apt-get -y install nano locales && \
sed -i '/en_GB.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen && \
sed -i '/sv_SE.UTF-8/s/^# //g' /etc/locale.gen && \
locale-gen

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unixodbc unixodbc-dev gnupg \
    && apt-get clean

# Download and install the Microsoft ODBC Driver for SQL Server
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
RUN curl https://packages.microsoft.com/config/debian/9/prod.list > /etc/apt/sources.list.d/mssql-release.list
RUN apt-get update && ACCEPT_EULA=Y apt-get install -y msodbcsql17

# Install the PDO SQLSRV extension
RUN pecl install pdo_sqlsrv-5.9.0

# Enable the PDO SQLSRV extension
RUN docker-php-ext-enable pdo_sqlsrv

ENV LANG en_GB.UTF-8
ENV LANGUAGE en_GB:en
ENV LC_ALL en_GB.UTF-8

# Disable error display
RUN sed -i -e 's/^display_errors\s*=\s*On/display_errors = Off/g' $PHP_INI_DIR/php.ini

## Se till att det går att ladda up lite större filer
RUN sed -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 20M/g' $PHP_INI_DIR/php.ini && \
    sed -i -e 's/post_max_size = 8M/post_max_size = 20M/g' $PHP_INI_DIR/php.ini

COPY ./src /var/www/html

## Sätt ägarskap på upload-kataloger
RUN chown -R www-data:www-data /var/www/html/PI/DiVA/DATAFILER
## Sätt ägarskap på upload-kataloger
RUN chown -R www-data:www-data /var/www/html/PI/sqlserwebb/DATAFILER
