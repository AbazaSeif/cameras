## Camera CRUD and streaming test

This application allows CRUD of CCTV cameras and show the live streaming of each one.

## Instalation

First clone the project
```
    $ git clone https://github.com/esalazarv/cameras.git
```

After configure the database access in the `.env` file and install composer dependencies.
```
    $ cd cameras
    $ composer install    
```

Run all migrations and seeder, all routes require Authentication, but this will create a user record for access to the application
username: `test@email.com` password: `secret`, but you can create a yourself profile if you prefer.
```
    $ php artisan migrate --seed
```

