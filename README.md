## AL-Laravel 

### Summary
Laravel interface for AL database

### Setup
- create mysql user and database
- copy .env.example to .env
   - edit `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - add field `GITHUB_TOKEN` with token as value
- run cli cmd `php artisan migrate` to setup database tables
- run cli cmd `php artisan serve &` to run web dev server
- run cli cmd `npm run dev` to run npm dev server

### Requirements
- tested on: 
   - php -v 8.2.1
   - node -v 18.14.0
