FROM php:7.3-apache

RUN a2enmod rewrite && \
cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && \
docker-php-ext-install mysqli pdo pdo_mysql && \
apt-get update && \
apt-get -y install nano locales && \
sed -i '/en_GB.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen && \
sed -i '/sv_SE.UTF-8/s/^# //g' /etc/locale.gen && \
locale-gen && \
apt-get -y install gnupg2 && \
curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - && \
curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
apt-get update && \
ACCEPT_EULA=Y apt-get -y install msodbcsql17 unixodbc-dev && \
pecl install pdo_sqlsrv-5.9.0 && \
docker-php-ext-enable pdo_sqlsrv && \
apt-get -y remove gnupg2 unixodbc-dev && \
apt-get clean && \
rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV LANG en_GB.UTF-8
ENV LANGUAGE en_GB:en
ENV LC_ALL en_GB.UTF-8

# Disable error display in the custom php.ini file
RUN sed -i -e 's/^display_errors\s*=\s*On/display_errors = Off/g' $PHP_INI_DIR/php.ini

COPY ./src /var/www/html
