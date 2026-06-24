# KIS List — Library API

A simple REST API for a library system, allowing staff to track and update the status of books (available / borrowed). Built as a recruitment assignment for KIS List.

## Tech Stack

- PHP 8.3
- Symfony 7.2
- PostgreSQL 16
- Doctrine ORM
- Docker / Docker Compose (PHP-FPM + Nginx)
- PHPUnit

## Quick Start

```bash
git clone <repo-url>
cd kislist-library-api
docker compose up -d --build
```

The API will be available at: **http://localhost:8081/api/books**

Database migrations run automatically on container startup (no manual step required).

## Deployment

The application is also deployed on Railway: **https://zooming-spontaneity-production-940e.up.railway.app/api/books**

Locally, `docker compose up` runs 3 separate containers (PHP-FPM, Nginx, PostgreSQL) defined in `compose.yaml`. On Railway, the same `Dockerfile` builds a single service where Nginx and PHP-FPM run together via `supervisord`; PostgreSQL is provided as a managed Railway plugin instead of the local `database` container. The listening port is read from the `$PORT` environment variable at runtime, with a local fallback to `8080`.

## Project Structure

```
src/
├── Controller/       # HTTP layer only — request/response handling, no business logic
├── Service/          # Business logic, Value Object creation & validation
├── Repository/       # Doctrine persistence (save / remove / findAllBooks)
├── Entity/           # Doctrine entity (data only, no behavior)
├── Dto/
│   ├── Request/      # Input DTOs, manual validation via fromArray()
│   └── Response/     # Output DTOs via toArray()
├── ValueObject/       # SerialNumber, CardNumber — immutable, self-validating
├── EventSubscriber/   # ExceptionSubscriber — centralized error handling
└── Exception/         # Domain exceptions mapped to HTTP status codes
```

**Layering:** `Controller → Service → Repository → Entity`. The Controller stays thin; all business rules and Value Object conversion live in `BookService`.

## API Documentation

Base URL: `http://localhost:8081/api/books`

### Create a book

```bash
curl -X POST http://localhost:8081/api/books \
  -H "Content-Type: application/json" \
  -d '{"id": 123456, "title": "Pan Tadeusz", "author": "Adam Mickiewicz"}'
```

**201 Created**
```json
{"id":123456,"title":"Pan Tadeusz","author":"Adam Mickiewicz","isBorrowed":false,"borrowedAt":null,"borrowerCardNumber":null}
```

**400 Bad Request** — missing fields

```bash
curl -X POST http://localhost:8081/api/books \
  -H "Content-Type: application/json" \
  -d '{"title": "Pan Tadeusz"}'
```
```json
{"error":"Pola \"id\", \"title\" oraz \"author\" są wymagane.","code":400}
```

**409 Conflict** — duplicate serial number (id already exists)
```json
{"error":"Książka o podanym numerze seryjnym już istnieje","code":409}
```

### List all books

```bash
curl http://localhost:8081/api/books
```

**200 OK**
```json
[{"id":123456,"title":"Pan Tadeusz","author":"Adam Mickiewicz","isBorrowed":false,"borrowedAt":null,"borrowerCardNumber":null}]
```

### Update book status (borrow / return)

```bash
curl -X PATCH http://localhost:8081/api/books/123456/status \
  -H "Content-Type: application/json" \
  -d '{"status": "borrowed", "borrowerCardNumber": 654321}'
```

**200 OK**
```json
{"id":123456,"title":"Pan Tadeusz","author":"Adam Mickiewicz","isBorrowed":true,"borrowedAt":"2026-06-24T12:00:00+00:00","borrowerCardNumber":654321}
```

Returning a book:

```bash
curl -X PATCH http://localhost:8081/api/books/123456/status \
  -H "Content-Type: application/json" \
  -d '{"status": "available"}'
```

**400 Bad Request** — invalid status value

```bash
curl -X PATCH http://localhost:8081/api/books/123456/status \
  -H "Content-Type: application/json" \
  -d '{"status": "foo"}'
```
```json
{"error":"Pole \"status\" musi mieć wartość \"borrowed\" lub \"available\".","code":400}
```

**404 Not Found**

```bash
curl -X PATCH http://localhost:8081/api/books/999999/status \
  -H "Content-Type: application/json" \
  -d '{"status": "available"}'
```
```json
{"error":"Książka o numerze seryjnym 999999 nie została znaleziona","code":404}
```

### Delete a book

```bash
curl -X DELETE http://localhost:8081/api/books/123456
```

**204 No Content**

**404 Not Found**

```bash
curl -X DELETE http://localhost:8081/api/books/999999
```
```json
{"error":"Książka o numerze seryjnym 999999 nie została znaleziona","code":404}
```

## Running Tests

```bash
docker compose exec php php bin/phpunit
```

32 tests (unit + functional) covering Value Objects, Service layer, and Controller endpoints.

## Design Decisions

- **Value Objects without Doctrine embeddables** — `SerialNumber`/`CardNumber` are plain `final readonly` classes, not mapped via Doctrine `Embeddable`. The Entity stores raw `int` columns; conversion to/from VO happens at the Service boundary (`try/catch` mapping `InvalidArgumentException` to domain exceptions). This keeps the Entity Doctrine-simple and avoids embeddable mapping overhead for a single-field VO, at the cost of validation not being enforced at the ORM layer.
- **No Symfony Serializer** — DTOs use manual `fromArray()` / `toArray()` factory methods, giving full control over validation messages and output shape without extra bundle configuration.
- **Centralized exception handling** — `ExceptionSubscriber` unifies all error responses (`{"error": ..., "code": ...}`) across domain exceptions, native Symfony HTTP exceptions, and DB constraint violations (e.g. duplicate serial number → 409).