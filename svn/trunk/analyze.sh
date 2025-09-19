#:/bin/sh
php -l *.php
vendor/bin/phpcs --standard=PSR12 ./*.php
vendor/bin/phpstan analyse ./*.php --level=5 --memory-limit=4G
