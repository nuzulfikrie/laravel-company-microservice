# Company Microservice

A Laravel-based microservice for managing company-related operations in a distributed system.

## Overview

This microservice handles company management operations including:
- Company CRUD operations
- Company member management
- Role-based access control
- Integration with user management service

## Requirements

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Laravel 10.x

## Installation

1. Clone the repository:

```
git clone [repository-url]
cd microservice-company
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Configure your environment variables in `.env`:
```env
APP_NAME=CompanyService
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=microservice
DB_USERNAME=root
DB_PASSWORD=

SERVICES_USER_MANAGEMENT_BASE_URL=http://user-management
SERVICES_USER_MANAGEMENT_KEY=your-key
SERVICES_USER_MANAGEMENT_ID=your-id
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run migrations:
```bash
php artisan migrate
```

## Available Commands

The service includes several utility commands:

- `php artisan app:clear-all` - Clear all cached data
- `php artisan db:create {name}` - Create a new database
- `php artisan db:drop-tables` - Drop all tables in the database
- `php artisan db:truncate-all` - Truncate all tables
- `php artisan db:reset-development-project` - Reset development environment
- `php artisan test:db-setup` - Set up test database

## API Endpoints

### Companies

- `GET /api/companies` - List all companies
- `POST /api/companies` - Create a new company
- `GET /api/companies/{id}` - Get company details
- `PUT /api/companies/{id}` - Update company
- `DELETE /api/companies/{id}` - Delete company

### Company Members

- `GET /api/company-members` - List all company members
- `POST /api/company-members` - Add new company member
- `GET /api/company-members/{id}` - Get member details
- `PUT /api/company-members/{id}` - Update member
- `DELETE /api/company-members/{id}` - Remove member

## Authentication

The service uses token-based authentication integrated with a User Management service. All requests must include:

```http
Authorization: Bearer <token>
X-Service-Key: <service-key>
X-Service-ID: <service-id>
```

## Testing

1. Configure test environment:
```bash
cp .env.testing.example .env.testing
```

2. Run tests:
```bash
php artisan test
```

## Company Status Enums

Available company statuses:
- `active`
- `inactive`
- `dormant`
- `suspended`
- `bankrupt`

## Company Member Roles

Available member roles:
- `admin`
- `member`
- `guest`
- `owner`
- `vendor`

## Error Handling

The service returns standard HTTP status codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Internal Server Error

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.