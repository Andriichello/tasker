## `tasker`

A minimal task management system that allows users to create and track tasks.

## Local Setup Options

You can run this application locally either with Docker or directly on your machine.

## Option 1: Docker Setup

This project includes a Docker Compose configuration for easy development and deployment. The setup includes:

- PHP-FPM (8.2) for the backend
- MySQL (8.0) for the database
- Nginx for the web server
- Node.js for frontend builds

### Requirements

- Docker
- Docker Compose

### Getting Started

1. Clone the repository:
   ```
   git clone <repository-url>
   cd tasker
   ```

2. Start the Docker containers:
   ```
   docker-compose up -d --build
   ```

   This single command will:
   - Build the PHP container with all required extensions
   - Start the MySQL database
   - Start the Nginx web server
   - Build the frontend assets with Node.js
   - Run database migrations
   - Set up the application

3. Access the application:
   ```
   http://localhost:8080/api/docs
   ```

### Container Details

- **app**: PHP-FPM container running the Laravel application
- **web**: Nginx web server serving the application
- **db**: MySQL database for storing application data
- **node**: Node.js container for building frontend assets

### Useful Commands

- Start containers: `docker-compose up -d`
- Stop containers: `docker-compose down`
- View logs: `docker-compose logs -f`
- Access PHP container: `docker-compose exec app bash`
- Run artisan commands: `docker-compose exec app php artisan <command>`
- Run composer commands: `docker-compose exec app composer <command>`
- Run npm commands: `docker-compose exec node npm <command>`

### Database

The MySQL database is configured with the following default settings:
- Database: tasker
- Username: tasker
- Password: password

These settings can be modified in the `.env` file and `docker-compose.yml` file.

## Option 2: Local Machine Setup

If you prefer to run the application directly on your local machine without Docker, follow these steps:

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0
- Node.js 20 or higher
- npm

Required PHP extensions:
- PDO PHP Extension
- HTTP PHP Extension
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

### Installation Steps

1. Clone the repository:
   ```
   git clone <repository-url>
   cd tasker
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Install JavaScript dependencies:
   ```
   npm install
   ```

4. Create a copy of the environment file:
   ```
   cp .env.example .env
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Configure the database:
   - Create a MySQL database named `tasker`
   - Create a MySQL user `tasker` with password `password` (or update the .env file with your preferred credentials)
   - Grant all privileges on the `tasker` database to the `tasker` user

7. Run database migrations:
   ```
   php artisan migrate
   ```

8. Build frontend assets:
   ```
   npm run build
   ```

### Running the Application

You can run the application using the built-in Laravel development server:

```
php artisan serve
```

This will start the server at http://localhost:8000

For a more complete development environment with queue processing and frontend asset compilation, you can use the provided composer script:

```
composer dev
```

This will concurrently run:
- Laravel development server
- Queue worker
- Log viewer
- Vite development server for frontend assets

### Useful Commands

- Run tests: `composer test`
- Clear cache: `php artisan cache:clear`
- Clear config: `php artisan config:clear`
- Clear route cache: `php artisan route:clear`
- Clear view cache: `php artisan view:clear`
