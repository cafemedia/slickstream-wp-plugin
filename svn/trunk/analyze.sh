#:/bin/sh
vendor/bin/phpcs --standard=PSR12 ./*.php
vendor/bin/phpstan analyse ./*.php --level=max

