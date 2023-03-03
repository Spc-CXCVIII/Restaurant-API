## Restaurant list from Google map API

- Using Laravel 10
- PHP 8.1.0 Required
- Composer 2.2.0 or greater

Feature

- Get restaurant list by enter a keyword

To run this project
1. Download this project
2. Change ```.env.example``` to ```.env``` and put your Google Map API key to .env file
3. Install package
```
composer install
```
4. Generate app key
```
php artisan key:generate
```
5. Add your Google Map API key to ```.env``` file

6. start server
```
php artisan serve
```

#### The API is <b>```/api/restaurant/search```</b>
