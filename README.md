## Project Setup

#### Run Docker

`docker compose up -d`

#### Install packages
`docker compose exec app composer install`

#### Copy .env.example
`docker compose exec app cp .env.example .env`

#### Generate app key
`docker compose exec app php artisan key:generate`

#### Migrate DB tables
`docker compose exec app php artisan migrate`

#### Seed initial data
`docker compose exec app php artisan db:seed`

#### List available routes
`docker compose exec app php artisan route:list --path=api`