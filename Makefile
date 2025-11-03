.PHONY: setup php cs-fixer phpstan tests

setup:
	docker-compose up --build -d
	docker-compose exec php composer install

php:
	docker-compose exec php sh

cs-fixer:
	docker-compose exec php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --allow-risky=yes

phpstan:
	docker-compose exec php vendor/bin/phpstan analyse src tests --configuration=phpstan.neon --memory-limit=1G

tests:
	rm -rf var/cache/test
	mkdir -p var/cache/test
	docker-compose exec php vendor/bin/phpunit
