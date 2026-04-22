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

1. Skapa folder på server med namnet på repot: "/local/docker/pi"
2. Skapa och anpassa docker-compose.yml i foldern

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

1. Skapa och anpassa .env(för composefilen) i foldern

```
DB_DATABASE=pi
DB_USER=pi_anv
DB_PASSWORD=xxxxxx
DB_ROOT_PASSWORD=xxxxxx
PATHPREFIX=/PI
DOMAIN_NAME=apps-ref.lib.kth.se
REPO_TYPE=ref
```

1. Skapa folder "local/docker/pi/dbinit"
2. Skapa init.sql från repots dbinit/init.sql
3. Skapa folder /local/docker/pi/DiVA/sqlserwebb/DATAFILER
4. Sätt rättigheter/ägare: sudo chown -R www-data:www-data /local/docker/pi/DiVA/sqlserwebb/DATAFILER
5. Skapa folder /local/docker/pi/DiVA/DATAFILER
6. Sätt rättigheter/ägare: sudo chown -R www-data:www-data /local/docker/pi/DiVA/DATAFILER
7. Skapa deploy_ref.yml i github actions
8. Skapa deploy_prod.yml i github actions
9. Github Actions bygger en dockerimage i github packages
10. Starta applikationen med docker compose up -d --build i "local/docker/pi"

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

### Local Endpoints

All URLs assume the app is running at `http://localhost:8080`.

> **Note:** Most pages require a valid session. Log in first via the relevant login page before navigating to protected pages.

---

#### DiVA / ISBN module

### Login

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/DiVA/index.php` | Start page |
| `http://localhost:8080/PI/DiVA/d_loggain.php` | DiVA login (credentials stored in `hant_diva` MySQL; use `root`/`root` locally) |
| `http://localhost:8080/PI/DiVA/d_h_loggain.php` | DiVA historical data login |
| `http://localhost:8080/PI/DiVA/loggain.php` | ISBN login (credentials stored in `hant_isbn` MySQL; redirects to ISBN menu) |

### Menus

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/DiVA/d_meny.php` | DiVA main menu |
| `http://localhost:8080/PI/DiVA/d_h_meny.php` | DiVA historical data menu |
| `http://localhost:8080/PI/DiVA/d_importmeny.php` | DiVA import menu |
| `http://localhost:8080/PI/DiVA/d_isbn_meny.php` | ISBN sub-menu |

### Staff search

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/DiVA/d_soek_personal.php` | Search KTH staff |
| `http://localhost:8080/PI/DiVA/d_visa_personal.php` | View staff member details |

### ISBN management

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/DiVA/d_soek_isbn.php` | Search ISBN records |
| `http://localhost:8080/PI/DiVA/d_visa_valt_isbn.php` | View selected ISBN record |
| `http://localhost:8080/PI/DiVA/d_visa_reg_isbn.php` | View registered ISBNs |
| `http://localhost:8080/PI/DiVA/d_visa_statistik.php` | ISBN registration statistics (monthly) |
| `http://localhost:8080/PI/DiVA/d_aendra_isbnreg.php` | Edit ISBN registration |
| `http://localhost:8080/PI/DiVA/d_ladda_isbn.php` | Upload/import ISBN file |

### File processing & restore

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/DiVA/d_behandlafil_man_m_l.php` | Manual file processing |
| `http://localhost:8080/PI/DiVA/d_behandlafil_man_m_l_25.php` | Manual file processing (variant) |
| `http://localhost:8080/PI/DiVA/d_aterstall.php` | Restore/reset records |
| `http://localhost:8080/PI/DiVA/d_aterstall_2.php` | Restore/reset records (step 2) |

---

#### BIBSTAT / sqlserwebb module

> **Note:** This module connects to SQL Server at `bibmet01.ug.kth.se`. Requires KTH network or VPN. The local Docker image is built for `linux/amd64` to support the Microsoft ODBC driver.

### Entry & menu

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/sqlserwebb/loggain.php` | BIBSTAT login — starts SQL Server session |
| `http://localhost:8080/PI/sqlserwebb/adressmeny.php` | BIBSTAT main menu (after login) |

### Primary feature pages (accessible from menu)

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/sqlserwebb/regel_organisation.php` | Organisation matching rules |
| `http://localhost:8080/PI/sqlserwebb/regel_full_adress.php` | Full-address matching rules |
| `http://localhost:8080/PI/sqlserwebb/regel_centra.php` | Centra matching rules |
| `http://localhost:8080/PI/sqlserwebb/regel_organisation_typ.php` | Organisation-type matching rules |
| `http://localhost:8080/PI/sqlserwebb/organisationsnamn.php` | Organisation name lookup |
| `http://localhost:8080/PI/sqlserwebb/laddaorgregler.php` | Load/import organisation rules |
| `http://localhost:8080/PI/sqlserwebb/senaste_koerning.php` | Latest rule-run results |
| `http://localhost:8080/PI/sqlserwebb/regel_ej_traeff.php` | Rules with no matches |
| `http://localhost:8080/PI/sqlserwebb/bestaell_listor.php` | Order/export lists |

### Secondary pages (reached from feature pages)**

| URL | Description |
|-----|-------------|
| `http://localhost:8080/PI/sqlserwebb/visa_regler_o.php` | View organisation rules |
| `http://localhost:8080/PI/sqlserwebb/visa_regler_f_a.php` | View full-address rules |
| `http://localhost:8080/PI/sqlserwebb/visa_regler_c.php` | View centra rules |
| `http://localhost:8080/PI/sqlserwebb/visa_regler_o_typ.php` | View organisation-type rules |
| `http://localhost:8080/PI/sqlserwebb/visa_regler_unorgid.php` | View rules for unknown org IDs |
| `http://localhost:8080/PI/sqlserwebb/visa_regler_ej_traeff.php` | View rules with no hits |
| `http://localhost:8080/PI/sqlserwebb/visa_organisation.php` | View organisation record |
| `http://localhost:8080/PI/sqlserwebb/visa_dubb_c.php` | View centra duplicates |
| `http://localhost:8080/PI/sqlserwebb/splittringsdubbletter.php` | Split/merge duplicate records |
| `http://localhost:8080/PI/sqlserwebb/ny_regel_o.php` | Add new organisation rule |
| `http://localhost:8080/PI/sqlserwebb/ny_regel_f_a.php` | Add new full-address rule |
| `http://localhost:8080/PI/sqlserwebb/ny_regel_c.php` | Add new centra rule |
| `http://localhost:8080/PI/sqlserwebb/ny_regel_o_typ.php` | Add new organisation-type rule |
| `http://localhost:8080/PI/sqlserwebb/ny_organisation.php` | Add new organisation |
| `http://localhost:8080/PI/sqlserwebb/aendra_regel_o.php` | Edit organisation rule |
| `http://localhost:8080/PI/sqlserwebb/aendra_regel_f_a.php` | Edit full-address rule |
| `http://localhost:8080/PI/sqlserwebb/aendra_regel_c.php` | Edit centra rule |
| `http://localhost:8080/PI/sqlserwebb/aendra_regel_o_typ.php` | Edit organisation-type rule |
| `http://localhost:8080/PI/sqlserwebb/aendra_organisation.php` | Edit organisation |
| `http://localhost:8080/PI/sqlserwebb/koer_regel_o.php` | Run organisation rule |
| `http://localhost:8080/PI/sqlserwebb/koer_regel_c.php` | Run centra rule |
| `http://localhost:8080/PI/sqlserwebb/koer_regel_o_typ.php` | Run organisation-type rule |
| `http://localhost:8080/PI/sqlserwebb/lista_1.php` | List view 1 |
| `http://localhost:8080/PI/sqlserwebb/lista_2.php` | List view 2 |
| `http://localhost:8080/PI/sqlserwebb/traeff_regler_o_typ.php` | Organisation-type rule hits |

---

## Architecture and Flow

For a project overview with Mermaid charts (architecture, navigation, and data flow), see:

- `README_FLOW.md`

## Modernization Guide

For a repo-specific plan to improve or rewrite PI into a more coherent production-ready system, see:

- `README_REWRITE.md`
