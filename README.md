## AL-Laravel 

### Summary
Laravel interface for AL database. Aim is to create a web-based interface to view/manage AL database entries. 

First milestone will be to read all .itm files from Amirani-AL/Accursedlands-obj/ - https://github.com/users/kugra-al/projects/1:
- .itm files must be read from Amirani-AL/Accursedlands-obj/
- migration files must be made for all new keys so we can keep track of them
- all .itm file data must be saved to database, and updated whenever any of them change 
- output should be in a [DataTable](https://datatables.net/examples/basic_init/multi_col_sort.html) format, with sortable headers, search, optional headers (don't need to show all 100+ headers), .csv output

### Status
- https://github.com/kugra-al/al-laravel/projects?query=is%3Aopen
- Not usable yet. Still setting up base app to read/process files from git

### Setup

- perms 
   - cd to dir
   - sudo find . -type f -exec chmod 664 {} \;
   - sudo find . -type d -exec chmod 775 {} \;
   - sudo chgrp -R www-data storage bootstrap/cache
   - sudo chmod -R ug+rwx storage bootstrap/cache
- create mysql user and database
- copy .env.example to .env
   - edit `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - add field `GITHUB_TOKEN` with token as value
- run cli cmd `php artisan migrate` to setup database tables
- run cli cmd `php artisan serve` to run web dev server (`php artisan serve &` to run in background) - (for testing only, should be installed on apache/nginx eventually)
- run cli cmd `npm run dev` to run npm dev server (`npm run dev &` to run in background)
- navigate to web frontend (address should be shown after `php artisan serve`) and register a new account
- run cli cmd `php artisan db:seed --class=PermissionSeeder` to create AL permissions and roles and apply admin and super-admin roles to user #1 (not working)

### Queues
- run cli cmd `php artisan queue:work` to run the queue worker

If something doesn't work:
- composer update
- npm install

### Requirements
- tested on: 
   - php -v 8.2.1
   - node -v 18.14.0
   - apache -v 2.4.41 (Ubuntu)
   - mysql -v 8.0.32-0ubuntu0.20.04.2
