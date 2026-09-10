FROM php:8.3-apache-bookworm

# pdo_sqlsrv — connects to SQL Server natively, no SQL translation needed.
# Pin to bookworm (Debian 12): Microsoft's ODBC driver repo targets Debian 12.
# apt-key is removed in modern Debian; use gpg --dearmor keyring instead.
RUN a2enmod rewrite && \
cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && \
docker-php-ext-install mysqli pdo pdo_mysql && \
apt-get update && \
apt-get -y install --no-install-recommends nano locales && \
sed -i '/en_GB.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen && \
sed -i '/sv_SE.UTF-8/s/^# //g' /etc/locale.gen && \
locale-gen

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl gnupg2 unixodbc-dev \
    && apt-get clean

# Download and install the Microsoft ODBC Driver for SQL Server
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] \
        https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18

# Install the PDO SQLSRV extension
RUN pecl install sqlsrv pdo_sqlsrv

# Enable the PDO SQLSRV extension
RUN docker-php-ext-enable sqlsrv pdo_sqlsrv

ENV LANG=en_GB.UTF-8
ENV LANGUAGE=en_GB:en
ENV LC_ALL=en_GB.UTF-8

# Disable error display
RUN sed -i -e 's/^display_errors\s*=\s*On/display_errors = Off/g' $PHP_INI_DIR/php.ini

## Se till att det går att ladda up lite större filer
RUN sed -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 20M/g' $PHP_INI_DIR/php.ini && \
    sed -i -e 's/post_max_size = 8M/post_max_size = 20M/g' $PHP_INI_DIR/php.ini

COPY ./src /var/www/html

# Replace the hardcoded production hostname with an env-var fallback so this same
# image can be pointed at a different SQL Server (e.g. local/dev testing) via
# MSSQL_HOST. Defaults to the exact previous behavior when unset.
RUN find /var/www/html -name "*.php" -exec sed -i \
    -e "s|\$hostname = \"bibmet01.ug.kth.se\";|\$hostname = getenv('MSSQL_HOST') ?: 'bibmet01.ug.kth.se';|" \
    {} \;

## Sätt ägarskap på upload-kataloger
RUN chown -R www-data:www-data /var/www/html/PI/DiVA/DATAFILER
## Sätt ägarskap på upload-kataloger
RUN chown -R www-data:www-data /var/www/html/PI/sqlserwebb/DATAFILER
