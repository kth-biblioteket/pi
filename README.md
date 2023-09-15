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


4.  Skapa folder "local/docker/pi/dbinit"
5. Skapa init.sql från repots dbinit/init.sql
6. Skapa deploy_ref.yml i github actions
7. Skapa deploy_prod.yml i github actions
8. Github Actions bygger en dockerimage i github packages
9. Starta applikationen med docker compose up -d --build i "local/docker/pi"





