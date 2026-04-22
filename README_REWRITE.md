# PI Modernization and Rewrite Guide

## Purpose

This document describes how to improve and gradually rewrite PI into a more coherent, maintainable, and production-ready system.

The goal is not to replace everything in one step. The safer approach is to stabilize the current application first, then extract clear boundaries, and only then modernize the internals.

## Current state

The repository is a legacy multi-page PHP application with these characteristics:

- plain PHP scripts with HTML mixed into request handlers
- direct database access from page scripts
- manual includes and shared globals/session state
- multiple business areas in one codebase: DiVA, ISBN, BIBMET, sqlserwebb
- mixed data sources: MySQL plus SQL Server for sqlserwebb
- infrastructure assumptions embedded in the app and container setup
- partial configuration via mounted files instead of a clear runtime config model

This works, but it creates predictable problems:

- business logic is hard to find and reuse
- database logic is duplicated across scripts
- configuration and secrets are easy to misconfigure
- local, test, and production runtime behavior differ too much
- the application has no clear domain boundaries or deployment contracts
- testing is difficult because page scripts do too much work directly

## Main problems to solve

### 1. No application structure

The code is organized mostly by pages and actions, not by domain or responsibility.

Result:

- one file often handles request parsing, validation, SQL, formatting, and response rendering
- changes in one workflow are easy to break elsewhere

### 2. Database access is tightly coupled to page scripts

SQL is embedded directly inside UI-facing PHP files.

Result:

- no reusable data layer
- difficult transaction handling
- difficult testing and review

### 3. Configuration is inconsistent

The app uses mounted config files and legacy assumptions around paths, credentials, and runtime state.

Result:

- fragile deployments
- environment drift between local and production
- secrets management is weaker than it should be

### 4. Production readiness is incomplete

The current repo can run, but it is missing the usual production-grade concerns as first-class parts of the system.

Examples:

- structured logging
- health checks
- application metrics
- error tracking
- automated tests in CI
- dependency update strategy
- security hardening and secret rotation

### 5. Module boundaries are unclear

The repository contains several concerns:

- DiVA workflows
- ISBN workflows
- BIBMET import processing
- sqlserwebb SQL Server-backed rules management
- email sending

These should not all evolve as one flat PHP surface.

## Recommended target architecture

Do not start with a big-bang rewrite. Use a staged modernization strategy.

Target shape:

- one deployable application, or a small number of clearly separated services
- clear modules with explicit boundaries
- framework-supported routing, dependency injection, validation, logging, and configuration
- business logic moved out of page scripts into services/use cases
- database access isolated in repository or gateway classes
- templates or frontend views separated from server-side actions
- background jobs for file processing and email when appropriate

## Recommended technical direction

Because no developers on the team currently write PHP, the best long-term direction is not PHP modernization. The better choice is a rewrite into a language the team can actively maintain.

For this repository, Python is the strongest default choice.

## Language choice: Python vs JavaScript

### Recommended option: Python

Python is the best fit for this system.

Why:

- the application is backend-heavy, not frontend-heavy
- it contains file processing, database-heavy workflows, and email sending
- it will likely benefit from background jobs and scheduled processing
- it integrates well with both MySQL and SQL Server
- it is a strong fit for internal business systems and admin-style workflows

This repo is not primarily a reactive UI product. It is a workflow application with forms, imports, database writes, reporting, and operational logic. That profile fits Python better than JavaScript.

Recommended Python stack:

- Django for the main application framework
- PostgreSQL for the new primary database unless there is a hard requirement to remain on MySQL
- Celery or RQ for background jobs and file-processing tasks
- pytest for tests
- SQLAlchemy or Django ORM depending on whether the rewrite is Django-first or service-first
- pyodbc or another supported SQL Server connector for the sqlserwebb migration path

Why Django specifically:

- strong support for forms, validation, sessions, auth, and admin workflows
- good fit for internal tools and operational back-office systems
- clear project structure out of the box
- mature ecosystem and predictable deployment model

### Secondary option: JavaScript or TypeScript

JavaScript can work, but it is not the best default choice for this repo unless the future team is already much stronger in Node.js or TypeScript than in Python.

It would be more attractive if:

- the organization wants one language across frontend and backend
- the future UI is expected to be much richer and API-driven
- the team already has strong operational experience with Node.js services

If JavaScript is chosen, use TypeScript rather than plain JavaScript.

Recommended JS/TS stack:

- NestJS for a structured backend
- PostgreSQL as the main database
- BullMQ or another worker system for background processing
- Prisma or TypeORM for persistence
- Jest or Vitest for tests

### Recommendation summary

If the goal is to replace this legacy PHP system with a maintainable, production-ready codebase that matches the application's actual workload, choose Python.

Choose JavaScript or TypeScript only if team capability strongly favors it.

## Rewrite strategy

There are two realistic paths.

### Option A: Rewrite incrementally into Python

This is the recommended path.

Suggested approach:

- keep the current PHP app running during migration
- rewrite one bounded workflow at a time
- start with ISBN first because it is relatively well-bounded
- move DiVA import processing next
- migrate sqlserwebb later because its SQL Server dependency makes it a separate integration concern

Why this is the best fit:

- the team can maintain the new code
- the architecture can be designed around current business boundaries instead of page scripts
- Python is a better long-term fit for workflow processing, admin logic, and jobs

### Option B: Rewrite selected workflows into separate services

This is only worth doing after the domain boundaries are understood and tested.

Possible split:

- ISBN as its own service
- import/file-processing pipeline as its own worker-oriented service
- sqlserwebb as a separately deployed integration app because it depends on SQL Server

This can make sense long term, but it is more expensive and easier to get wrong if attempted too early.

## Proposed module boundaries

If the code remains in one repository, reorganize it around domains instead of page names.

Suggested top-level application areas:

- `src/Application`
  - use cases, orchestration, transactions
- `src/Domain`
  - domain models, business rules, value objects
- `src/Infrastructure`
  - MySQL, SQL Server, mail, filesystem, logging, external integrations
- `src/Web`
  - controllers, request validation, response mapping, templates or APIs

Suggested business modules:

- `DiVA`
- `ISBN`
- `BIBMET`
- `SqlSerWebb`
- `Shared`

## Production-ready baseline

Before any larger rewrite, the system should meet this minimum baseline.

### Runtime and dependencies

- choose one supported target runtime for the rewrite, preferably Python
- manage dependencies through a standard package manager and lockfile
- stop vendoring libraries directly unless there is a specific reason
- pin dependency versions and scan them in CI

### Configuration and secrets

- replace mounted ad hoc config files with environment-based configuration
- use a single configuration contract for all environments
- keep secrets out of the repository and out of image layers
- document every required environment variable

### Containers and deployment

- keep one production Dockerfile and one local/dev override only if truly necessary
- add explicit health checks for app and database
- run the app as a non-root user where possible
- make logs go to stdout/stderr in structured form
- define rollback-safe deployment behavior

### Observability

- add application logs with correlation/request IDs
- capture application errors centrally
- add simple health endpoints such as `/health` and `/ready`
- define basic metrics: request count, error rate, job failures, queue depth if jobs are introduced

### Security

- audit all direct SQL construction and remove unsafe query patterns
- use prepared statements everywhere
- add CSRF protection to state-changing forms
- review session settings and cookie flags
- validate uploads by size, type, and storage path
- avoid exposing detailed PHP errors in production

### Quality gates

- add unit tests for business-critical ISBN and import logic
- add integration tests for database workflows
- add a smoke test for the main entry points
- run linting, static analysis, and tests in CI on every pull request

## Practical migration plan

### Phase 1: Stabilize what exists

Goal: reduce operational risk without changing core behavior.

Actions:

- centralize configuration loading
- remove remaining hardcoded credentials and runtime assumptions
- standardize database connection creation
- standardize error handling and logging
- add minimal health checks and CI validation
- document all databases, tables, and external dependencies

Deliverable:

- the current application still works, but it is easier to run and safer to deploy

### Phase 2: Extract shared infrastructure

Goal: stop repeating low-level code in page scripts.

Actions:

- create shared database clients for MySQL and SQL Server
- create mail service wrapper around PHPMailer
- create shared config object
- create reusable validation helpers
- create a small request/response abstraction or introduce a framework front controller

Deliverable:

- page scripts become thinner and easier to replace over time

### Phase 3: Move business logic into services

Goal: separate use cases from HTTP pages.

Examples:

- `ReserveIsbnService`
- `RegisterIsbnService`
- `ProcessDivaImportService`
- `SyncOrcidService`
- `ManageAddressRuleService`

Deliverable:

- the important rules live in tested service classes instead of in page scripts

### Phase 4: Introduce modern web entry points

Goal: replace direct script-per-page routing with a coherent application surface.

Actions:

- introduce controller-based routing
- validate all input at the edge
- move rendering into templates or a frontend layer
- keep old endpoints temporarily as adapters until migration is complete

Deliverable:

- new code enters through a consistent architecture while old pages still work during transition

### Phase 5: Split deployable units only if needed

Goal: separate modules only after they are stable and well understood.

Good candidates:

- async import processing
- sqlserwebb, because it has different infrastructure needs
- ISBN, if it needs a separate lifecycle or API consumers

Deliverable:

- smaller deployment units with lower blast radius, if the operational value justifies the complexity

## Immediate high-value refactors in this repo

These changes would improve coherence quickly without requiring a rewrite.

1. Introduce one shared bootstrap file.
   It should initialize config, logging, session handling, and database factories.

2. Move all DB credentials and database names behind one config layer.
   The app currently mixes environment-specific assumptions with runtime code.

3. Create dedicated data access classes per database.
   At minimum:
   - `HantDivaRepository`
   - `HantIsbnRepository`
   - `BibmetRepository`
   - `BibstatRepository`

4. Wrap email sending behind a service.
   Page scripts should not instantiate PHPMailer directly.

5. Extract ISBN logic first.
   ISBN reservation/registration is a relatively bounded domain and is a strong candidate for the first clean service layer.

6. Isolate file processing from browser requests.
   Long-running import jobs should eventually move to background processing instead of happening entirely in request/response flow.

7. Treat sqlserwebb as an integration boundary.
   It depends on SQL Server and should be separated logically even if it stays in the same repo at first.

## Suggested directory shape after a first modernization step

```text
src/
  Application/
    DiVA/
    ISBN/
    BIBMET/
    SqlSerWebb/
  Domain/
    DiVA/
    ISBN/
    BIBMET/
    Shared/
  Infrastructure/
    Database/
    Mail/
    Logging/
    Filesystem/
    Config/
  Web/
    Controller/
    Middleware/
    View/
public/
tests/
config/
docker/
```

## What should not be done

- do not rewrite everything at once
- do not change database schema and application behavior in the same large step unless there is strong test coverage
- do not introduce microservices just to appear modern
- do not keep adding new features to legacy page scripts once replacement services exist

## A sensible end-state

A coherent production-ready PI would look like this:

- a supported target runtime, preferably Python
- package-managed dependencies with a lockfile
- framework-based request lifecycle
- typed service layer for business logic
- isolated data access for MySQL and SQL Server
- background processing for heavy file workflows
- structured logs, health checks, and CI quality gates
- environment-driven config and secret management
- clear ownership boundaries between DiVA, ISBN, BIBMET, and sqlserwebb

## Recommended next steps

1. Confirm Python as the rewrite target and select Django or FastAPI.
2. Model the ISBN workflow first as the first replacement module.
3. Extract shared config, DB, and mail integrations behind clean service interfaces.
4. Add tests around ISBN and DiVA import behavior before larger migrations.
5. Decide whether sqlserwebb stays inside the app or becomes a separately deployed integration module.

If this project is going to be improved under active production use, incremental modernization is the correct strategy. A full rewrite should only happen after the domain behavior is captured by tests and the system boundaries are explicit.