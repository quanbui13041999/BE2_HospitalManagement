bước 1:composer install
bước 2:copy .env.example .env
bước 3:php artisan key:generate
       php artisan migrate:fresh
       (hoặc php artisan migrate nếu database đang trống)
nếu lỗi thiếu file database.sqlite thì chạy lệnh: New-Item -Path "database\database.sqlite" -ItemType File và chạy php artisan migrate
bước 4:php artisan optimize:clear
bước 5:php artisan serve
chạy lệnh này mới thấy được ảnh trong profile 
php artisan storage:link
