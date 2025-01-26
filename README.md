## Project Setup

#### Run Docker

`docker compose up -d`

#### Migrate DB tables
`docker compose exec app php artisan migrate`

#### Seed initial data
`docker compose exec app php artisan db:seed`