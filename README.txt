Versions

PHP 8.2.12
Composer 2.8.5
Laravel 12.33.0

Steps to run the project
Clone repository in htdocs folder.
Create file in root directory ".env"
Copy data from ".env.example" file into ".env" file
Add APP_KEY=base64:4AWjbYdjMHVITOHGIK9jAykIPPA9XTdAhSuUDAEirRA=
Run command in terminal php artisan migrate
Run php artisan serve
Hit the url: http://127.0.0.1:8000
Run php artisan queue:work
Now choose the dummy file and upload it.