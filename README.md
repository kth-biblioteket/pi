# KTH Bibliotekets Tjänster för Publiceringens Infrastruktur(PI)

## Funktioner
Startas i en Dockercontainer

###
Deploy via github actions som anropar en webhook

### Hanterar ISBN, Leta KTH-anställda, Adressrättning DiVA, BIBMET

#### Dependencies
php:7.3-apache
mysqli pdo pdo_mysql


##### Installation

1.  Skapa folder på server med namnet på repot: "/local/docker/pi"
2.  Skapa och anpassa docker-compose.yml i foldern
```txt
version: "3.6"

services:
  pi:
    container_name: pi
    depends_on:
      - pi-db
    image: ghcr.io/kth-biblioteket/pi:${REPO_TYPE}
    restart: unless-stopped
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
      - "apps-net"

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
      - "apps-net"

volumes:
  persistent-pi-db:

networks:
  apps-net:
    external: true
```

3.  Skapa och anpassa .env(för composefilen) i foldern
```
DB_DATABASE=pi
DB_USER=pi_anv
DB_PASSWORD=xxxxxx
DB_ROOT_PASSWORD=xxxxxx
PATHPREFIX=/PI
DOMAIN_NAME=apps-ref.lib.kth.se
REPO_TYPE=ref
```


4. Skapa folder "local/docker/pi/dbinit"
5. Skapa init.sql från repots dbinit/init.sql
6. Skapa folder /local/docker/pi/DiVA/sqlserwebb/DATAFILER
7. Sätt rättigheter/ägare: sudo chown -R www-data:www-data /local/docker/pi/DiVA/sqlserwebb/DATAFILER
8. Skapa folder /local/docker/pi/DiVA/DATAFILER
9. Sätt rättigheter/ägare: sudo chown -R www-data:www-data /local/docker/pi/DiVA/DATAFILER
10. Skapa deploy_ref.yml i github actions
11. Skapa deploy_prod.yml i github actions
12. Github Actions bygger en dockerimage i github packages
13. Starta applikationen med docker compose up -d --build i "local/docker/pi"

## Local Development (without Traefik)

Use the dedicated local compose file: `docker-compose.local.yml`.

Local setup uses:

- local image build from this repository (no GHCR pull)
- `Dockerfile.local` (skips SQL Server ODBC installation for local use)
- internal bridge network (no external `apps-net` required)
- MySQL initialization from `dbinit/`
- ports `8080` (web) and `3307` (MySQL)

### Step-by-step: run locally

1. Open a terminal in the repository root.

2. Build and start containers:
   - `docker compose -f docker-compose.local.yml up -d --build`

3. Verify containers are running:
   - `docker compose -f docker-compose.local.yml ps`

4. Open the app:
   - `http://localhost:8080/PI/DiVA/index.php`

5. (Optional) Follow logs:
   - `docker compose -f docker-compose.local.yml logs -f`

6. Stop containers:
   - `docker compose -f docker-compose.local.yml down`

7. Stop and remove database volume (reset local DB):
   - `docker compose -f docker-compose.local.yml down -v`

### Troubleshooting

- If you previously tried to build with `Dockerfile` and saw `msodbcsql17` errors, use only:
  - `docker compose -f docker-compose.local.yml up -d --build`
- If `8080` or `3307` is already in use, stop conflicting services and run again.
- If you changed SQL files under `dbinit/`, recreate the DB volume:
  - `docker compose -f docker-compose.local.yml down -v`
  - `docker compose -f docker-compose.local.yml up -d --build`

### Notes

- `docker-compose.yml` is production-oriented and expects external networking and environment variables for GHCR/Traefik.
- Local DB config is provided through:
  - `src/PI/DiVA/config.php.inc`
  - `src/PI/ISBN/config.php.inc`

## Architecture and Flow

For a project overview with Mermaid charts (architecture, navigation, and data flow), see:

- `README_FLOW.md`
