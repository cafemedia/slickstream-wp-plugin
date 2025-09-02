#:/bin/sh
php -l *.php
vendor/bin/phpcs --standard=PSR12 ./*.php
vendor/bin/phpstan analyse ./*.php --level=max --memory-limit=4G
