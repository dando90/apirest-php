# REST API and Interactive Playground

A native PHP and MySQL REST API for managing courses, subjects, and their many-to-many relationship. The repository includes a browser-based playground so visitors can send real requests without installing an API client.

## Requirements

- PHP 8.4 with PDO MySQL enabled (the target hosting runtime)
- MySQL or MariaDB with InnoDB support
- Composer for local dependency installation
- A web server configured to route API paths through the root `index.php`

The application uses `vlucas/phpdotenv` and PDO. It does not require a PHP framework or a JavaScript build step.

## Run locally

### With Docker Compose

With Docker Desktop running, start the complete PHP 8.4 and MySQL environment from the repository root:

```sh
docker compose up --build
```

When both services are ready, open `http://localhost:8080/demo/`. Compose imports `migration.sql` into a dedicated MySQL container on its first startup, then the demo page resets the sample records before showing the playground. No local PHP, Composer, or MySQL installation is needed. To choose another HTTP port, set `PLAYGROUND_PORT` before starting Compose.

Stop the services with `Ctrl+C`, then run `docker compose down`. The named MySQL volume keeps local data between runs. To remove that local database and start fresh, run `docker compose down -v`; this deletes the Compose database volume.

The Compose file uses disposable local-only credentials. Do not reuse them on a public server. For hosting, configure the real credentials in the server's ignored `.env` file.

### With a local PHP and MySQL installation

1. Install PHP, Composer, and MySQL/MariaDB. Start the database service.
2. From the repository root, install dependencies:

   ```sh
   composer install
   ```

3. Copy `.env.example` to `.env` and set the local database values:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

4. Create the database, then import `migration.sql` using your MySQL client or phpMyAdmin. This creates the three prefixed tables and sample data.
5. Start a local PHP server from the repository root:

   ```sh
   php -S 127.0.0.1:8000 router.php
   ```

   The included `router.php` forwards non-file paths to the API front controller so the built-in server can serve the API and the demo from the repository root.

6. Open `/demo/`. Its initial page load restores the sample dataset. Use the endpoint and method selectors to try requests. For example, select **Courses → GET**, then use the filters; select **Courses → PUT**, enter an ID, and edit the JSON body. Set `subjects` to `[]` to verify updating a course without subjects.

To try the API independently, use a request client or `curl`:

```sh
curl http://127.0.0.1:8000/api/subjects
curl -X POST http://127.0.0.1:8000/api/subjects \
  -H 'Content-Type: application/json' \
  -d '{"name":"Geography"}'
```

The PHP built-in server does not read `.htaccess`; do not use it to evaluate `.env` access protection. Configure Apache for local testing of that protection.

## Database and configuration

The app reads MySQL settings from `.env` in the repository root. `.env` is ignored by Git. Keep a separate `.env` for each project; this application loads the file from its own root. The root `.htaccess` denies direct web access to `.env` on Apache. For hosting, upload `.env` as a hidden file and confirm the FTP client displays hidden files.

`migration.sql` creates:

| Table | Purpose |
| --- | --- |
| `apirest_courses` | Courses (`id`, `name`, `places`) |
| `apirest_subjects` | Subjects (`id`, `name`) |
| `apirest_course_subject` | Course-subject links (`course_id`, `subject_id`) |

The link table has a composite primary key and foreign keys to both resource tables with `ON DELETE CASCADE` and `ON UPDATE CASCADE`. All names use the `apirest_` prefix so the API does not use generic table names that may belong to another application. Existing unprefixed tables are left untouched. Import the updated migration into the target database before using this version of the API.

## API reference

All endpoints are relative to the application root. Item IDs are path segments. JSON responses contain a `message` and, when data is returned, a `data` field. The HTTP status is also set to the response code.

| Resource | Method | Endpoint | Parameters / JSON body | Success and common error statuses |
| --- | --- | --- | --- | --- |
| Subjects | GET | `/api/subjects` | Optional `/{id}` for one subject | 200, 404 |
| Subjects | POST | `/api/subjects` | `{"name":"New subject"}` | 201, 400, 503 |
| Subjects | PUT | `/api/subjects/{id}` | `{"name":"Updated subject"}` | 200, 400, 503 |
| Subjects | DELETE | `/api/subjects/{id}` | No body | 200, 400, 503 |
| Courses | GET | `/api/courses` | Optional `/{id}`; collection filters: `name`, `subjects` (comma-separated IDs), `places` (minimum) | 200, 404 |
| Courses | POST | `/api/courses` | `{"name":"New course","places":30,"subjects":[1,2]}` | 201, 400, 503 |
| Courses | PUT | `/api/courses/{id}` | `{"name":"Updated course","places":20,"subjects":[1]}` | 200, 400, 503 |
| Courses | DELETE | `/api/courses/{id}` | No body | 200, 400, 503 |

Course `subjects` values are subject IDs. In course responses, the API currently returns the linked IDs as a comma-separated value for reads and as the submitted array for writes. There is no standalone relationship endpoint.

## Playground behavior and limitations

The playground is under `demo/` and uses HTML, CSS, and vanilla JavaScript. It sends requests to this application's existing API on the same origin and displays the method, URL, HTTP status, elapsed time, selected response headers, and formatted response body.

On each page load, PHP restores the sample subjects, courses, and links in the three `apirest_` tables. The reset uses a transaction and a MySQL advisory lock so simultaneous page loads do not reset those tables at the same time. Subsequent API requests do not reset the data. Since visitors share the demo database, opening a new playground page can erase changes made by another visitor. The reset runs from the page entry point; no public reset API is provided.

The demo only isolates data by table name within the configured database; use credentials with access limited to this application's database where the hosting plan allows it. The existing API has no authentication or rate limiting and allows cross-origin requests, so anyone who can reach it can call its write endpoints. CORS is not an access control mechanism. Keep it pointed at disposable demo data and do not publish it against production records. Database connection failures are logged server-side and return a generic 503 JSON response.

## Shared-hosting deployment

1. Generate `vendor/` locally. On Windows PowerShell, open a terminal in the repository root and run:

   ```powershell
   docker run --rm -v "${PWD}:/app" -w /app composer:2 install --no-dev --prefer-dist --no-interaction --optimize-autoloader
   ```

   This creates the ignored `vendor/` directory on your computer; it does not change `composer.lock`. If Composer is installed locally, `composer install --no-dev --prefer-dist --optimize-autoloader` does the same. The hosting server does not need Composer, SSH, Node.js, or a running process.
2. In Tophost's MySQL panel, create/select the database for this API and import `migration.sql` into it using phpMyAdmin. This creates the three `apirest_` tables.
3. Create `.env` in the project root from `.env.example`, then fill in the database host, name, username, and password supplied by Tophost. Keep `.env` out of Git.
4. Upload the project contents, including `vendor/` and the hidden `.htaccess`, to `/htdocs/projects/apirest/` using FTP/FTPS or the file manager. Upload `.env` to that same directory; enable hidden-file display in the FTP client if necessary. The `.htaccess` in this project blocks direct HTTP access to `.env`.
5. Open `https://your-domain.example/projects/apirest/demo/`. The page should show the playground after the initial database reset succeeds. If the main site has its own rewrite rules, confirm they do not route `/projects/apirest/api/` requests to another application.

Tophost's file manager opens in `/htdocs`, which is the site's web folder. If the domain is configured with a different document root, upload the project under that root while preserving the `projects/apirest/` path.

## Preserving the original API

The endpoint paths, HTTP methods, request/response formats, and CRUD queries are retained. The models now target only `apirest_courses`, `apirest_subjects`, and `apirest_course_subject`. The course update path that removes all subject links now prepares its delete statement before binding the course ID.
