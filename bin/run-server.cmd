set APP_DIR=%cd%
php -d display_errors -S 0.0.0.0:8000 -t public/ -d auto_prepend_file=%cd%\vendor\autoload.php