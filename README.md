# KTH Library PI services

This repository contains KTH Library services for Publication Infrastructure (PI).
The application is built as a Docker image and deployed through GitHub Actions.

## Services

The PI application includes several legacy PHP services, including:

- ISBN tools
- KTH employee lookup
- DiVA address correction
- BIBMET / Web of Science address correction

## BIBMET production site

Production BIBMET address correction is available at:

<https://apps.lib.kth.se/PI/sqlserwebb/loggain.php>

Reference/test "sandbox" deployment is available at:

<https://apps-ref.lib.kth.se/PI/sqlserwebb/loggain.php>

## Runtime

The current Docker image is based on:

- PHP 8.3 Apache
- MySQL extensions for the older PI/DiVA/ISBN parts
- Microsoft ODBC Driver 18
- `sqlsrv` and `pdo_sqlsrv` PHP extensions for BIBMET

BIBMET uses Microsoft SQL Server. The SQL Server host is configured through
environment variables, not by editing PHP files at build time.

```env
MSSQL_HOST=bibmet01.ug.kth.se
MSSQL_TRUST_SERVER_CERTIFICATE=true
```

For local MSSQL development, `MSSQL_HOST` can point to the local SQL Server
container, for example:

```env
MSSQL_HOST=mssql
MSSQL_TRUST_SERVER_CERTIFICATE=true
```

## Deployment overview

The deployed container image is pulled from GitHub Container Registry:

```txt
ghcr.io/kth-biblioteket/pi:${REPO_TYPE}
```

Typical deployment values:

```env
PATHPREFIX=/PI
DOMAIN_NAME=apps.lib.kth.se
REPO_TYPE=main
MSSQL_HOST=bibmet01.ug.kth.se
MSSQL_TRUST_SERVER_CERTIFICATE=true
```

For reference:

```env
PATHPREFIX=/PI
DOMAIN_NAME=apps-ref.lib.kth.se
REPO_TYPE=ref
MSSQL_HOST=bibmet01.ug.kth.se
MSSQL_TRUST_SERVER_CERTIFICATE=true
```

## Example Docker Compose deployment

```yaml
version: "3.6"

services:
  pi:
    container_name: pi
    depends_on:
      - pi-db
    image: ghcr.io/kth-biblioteket/pi:${REPO_TYPE}
    restart: unless-stopped
    environment:
      MSSQL_HOST: ${MSSQL_HOST:-bibmet01.ug.kth.se}
      MSSQL_TRUST_SERVER_CERTIFICATE: ${MSSQL_TRUST_SERVER_CERTIFICATE:-true}
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.pi.rule=Host(`${DOMAIN_NAME}`) && PathPrefix(`${PATHPREFIX}`)"
      - "traefik.http.routers.pi.entrypoints=websecure"
      - "traefik.http.routers.pi.tls=true"
      - "traefik.http.routers.pi.tls.certresolver=myresolver"
    volumes:
      - /local/docker/pi/config.php.inc:/var/www/html/PI/ISBN/config.php.inc
      - /local/docker/pi/config.php.inc:/var/www/html/PI/DiVA/config.php.inc
      - /local/docker/pi/DiVA/DATAFILER:/var/www/html/PI/DiVA/DATAFILER
      - /local/docker/pi/DiVA/sqlserwebb/DATAFILER:/var/www/html/PI/DiVA/sqlserwebb/DATAFILER
    networks:
      - apps-net

  pi-db:
    container_name: pi-db
    image: mysql:8.0
    volumes:
      - persistent-pi-db:/var/lib/mysql
      - ./dbinit:/docker-entrypoint-initdb.d
    restart: unless-stopped
    command:
      - --default-authentication-plugin=mysql_native_password
      - --character-set-server=utf8mb4
      - --collation-server=utf8mb4_unicode_ci
      - --sql_mode=STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
    environment:
      LANG: C.UTF-8
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    networks:
      - apps-net

volumes:
  persistent-pi-db:

networks:
  apps-net:
    external: true
```

## Server setup checklist

1. Create the deployment directory, for example:

   ```bash
   /local/docker/pi
   ```

2. Add a `docker-compose.yml` based on the example above.
3. Add an `.env` file for Compose variables.
4. Create `/local/docker/pi/dbinit` and copy `dbinit/init.sql` there.
5. Create the DiVA upload directories:

   ```bash
   /local/docker/pi/DiVA/DATAFILER
   /local/docker/pi/DiVA/sqlserwebb/DATAFILER
   ```

6. Set ownership on upload directories:

   ```bash
   sudo chown -R www-data:www-data /local/docker/pi/DiVA/DATAFILER
   sudo chown -R www-data:www-data /local/docker/pi/DiVA/sqlserwebb/DATAFILER
   ```

7. Start the stack:

   ```bash
   docker compose up -d
   ```

## Local BIBMET MSSQL development

The recommended local setup for BIBMET development lives in the sibling
`bibmet-tools` repository:

```bash
cd ../bibmet-tools
make run
```

That starts a local SQL Server container and a PHP container configured with
`MSSQL_HOST=mssql`.
