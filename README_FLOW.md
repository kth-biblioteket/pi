# PI Project Guide and Flow

## What this project is about

PI is a legacy PHP web application used by KTH Library to support publishing-related workflows.

Main business areas:
- DiVA import processing (file-based workflow)
- ISBN handling (register/search/statistics)
- KTH staff lookup support
- Address correction/rule management in the sqlserwebb module

The system runs in Docker and uses MySQL for DiVA/ISBN/BIBMET-related tables.
Some modules in sqlserwebb also connect to Microsoft SQL Server through PDO sqlsrv.

## Main modules

- src/PI/DiVA
  - Main entry for menu and import workflows
  - Uses MySQL databases such as hant_diva, hant_isbn, and bibmet
- src/PI/ISBN
  - ISBN registration endpoints and related actions
  - Uses MySQL (hant_isbn)
- src/PI/sqlserwebb
  - Address/rule management area
  - Uses SQL Server (PDO sqlsrv) in many scripts
- dbinit
  - SQL initialization scripts for local MySQL startup
  - bibmet.sql, hant_diva.sql, hant_isbn.sql

## High-level architecture

```mermaid
flowchart LR
    U[User Browser] --> W[Apache + PHP Container]

    subgraph APP[PI PHP Application]
      D[DiVA module]
      I[ISBN module]
      S[sqlserwebb module]
      M[PHPMailer]
    end

    W --> D
    W --> I
    W --> S

    D --> MY[(MySQL)]
    I --> MY
    D --> M

    S --> MSSQL[(SQL Server)]

    subgraph LOCAL[Local docker-compose.local.yml]
      W
      MY
    end
```

## Navigation flow (typical)

```mermaid
flowchart TD
  A["/PI/DiVA/index.php"] --> B["d_importmeny.php"]
  B --> C["d_behandlafil_man_m_l_25.php"]
    C --> D[Parse uploaded txt file]
    D --> E[Store processing rows in MySQL tables]
    E --> F[Generate output files]
    F --> G[Send result mail via PHPMailer]

  A -. legacy menu path .-> H["d_meny.php"]
  H --> I["d_soek_personal.php"]
  H --> J["d_isbn_meny.php"]
  J --> K["d_ladda_isbn.php"]
  J --> L["d_soek_isbn.php"]
  J --> M["d_visa_statistik.php"]
```

## ISBN registration flow

```mermaid
sequenceDiagram
    participant Client as External form/client
    participant PHP as /PI/ISBN/spara.php
    participant DB as MySQL (hant_isbn)

    Client->>PHP: POST publication and author fields
    PHP->>DB: Check min ISBN threshold
    PHP->>DB: Validate duplicate title/type/KTH id
    alt Not duplicate
      PHP->>DB: Transaction: reserve ISBN from oanv_isbn
      PHP->>DB: Insert into reg_isbn
      PHP-->>Client: Return assigned ISBN confirmation
    else Duplicate
      PHP-->>Client: Return already assigned message
    end
```

## Data stores used in PI

```mermaid
flowchart LR
    D[DiVA workflows] --> HD[(hant_diva)]
    I[ISBN workflows] --> HI[(hant_isbn)]
    P[Import processing] --> BM[(bibmet)]
    R[sqlserwebb rules] --> BS[(BIBSTAT SQL Server)]
```

## Local runtime model

For local development, the repository includes:

- docker-compose.local.yml
- Dockerfile.local

Why this matters:

- Local profile skips SQL Server ODBC installation to avoid build issues with old package sources.
- DiVA/ISBN/MySQL flows work locally.
- sqlserwebb pages that require SQL Server connectivity may not fully work unless SQL Server access is available and configured.

## Key entry points

- DiVA menu: /PI/DiVA/index.php
- Import menu: /PI/DiVA/d_importmeny.php
- Legacy main menu: /PI/DiVA/d_meny.php
- ISBN menu: /PI/DiVA/d_isbn_meny.php
- sqlserwebb login: /PI/sqlserwebb/loggain.php

## Quick understanding summary

- PI is a multi-workflow PHP app, not a single API service.
- Most local functionality depends on MySQL and dbinit scripts.
- sqlserwebb is the main part that depends on SQL Server.
- The primary current start page leads into the DiVA import flow.
