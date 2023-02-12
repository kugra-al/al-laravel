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
- Nearly ready for live testing 
![file view preview](https://media.discordapp.net/attachments/634069769267576832/1072121005922861136/Screenshot_20230206_134448.png)

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
   - add field `DEFAULT_ADMIN_USER_EMAIL` with email of default admin user
   - add field `DEFAULT_ADMIN_USER_NAME` with name of default admin user
   - add field `DEFAULT_ADMIN_USER_PASS` with password of default admin user
   - note: default admin user vars are used only for creating initial permissions
- run cli cmd `php artisan migrate` to setup database tables
- run cli cmd `php artisan serve` to run web dev server (`php artisan serve &` to run in background) - (for testing only, should be installed on apache/nginx eventually)
- run cli cmd `npm run dev` to run npm dev server (`npm run dev &` to run in background)
- run cli cmd `php artisan db:seed --class=PermissionSeeder` to create AL permissions and roles and apply admin and super-admin roles to DEFAULT_ADMIN_USER
- navigate to frontend, login and change password from default

### Queues
- run cli cmd `php artisan queue:work` to run the queue worker

If something doesn't work:
- composer update
- npm install

### Passwords/logins
- There's currently no mailer or password reset funcs (unless you're already logged in, or an admin)
- If you need to reset someone elses password, you can do it via Admin > Users menu
- You can also reset passwords using the cli cmd `php artisan password:reset`. You don't need to be logged into the frontend to do this

### Github OAuth
- Create OAuth app - https://docs.github.com/en/developers/apps/building-oauth-apps/creating-an-oauth-app
- Callback URL is https://websiteurl.tld/auth/github/callback (replace https://websiteurl.tld)
- In .env add:
   - `GITHUB_OAUTH_CLIENT_ID`
   - `GITHUB_OAUTH_CLIENT_SECRET`
   - `GITHUB_OAUTH_REDIRECT`         # comment or omit this to disable Login With Github button on /login

### Requirements
- tested on: 
   - php -v 8.2.1
   - node -v 18.14.0
   - apache -v 2.4.41 (Ubuntu)
   - mysql -v 8.0.32-0ubuntu0.20.04.2
   - also needs php8.2-mbstring and php8.2-dom if not installed
