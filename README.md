## AL-Laravel 

### Summary
Laravel interface for AL database

### Setup
- create mysql user and database
- copy .env.example to .env
   - edit `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - add field `GITHUB_TOKEN` with token as value
- run cli cmd `php artisan migrate` to setup database tables
- run cli cmd `php artisan serve` to run web dev server (`php artisan serve &` to run in background)
- run cli cmd `npm run dev` to run npm dev server (`npm run dev &` to run in background)
- navigate to web frontend (address should be shown after `php artisan serve`) and register a new account
- run cli cmd `php artisan db:seed --class=PermissionSeeder` to create AL permissions and roles and apply admin and super-admin roles to user #1

If something doesn't work:
- composer update
- npm install

### Requirements
- tested on: 
   - php -v 8.2.1
   - node -v 18.14.0
