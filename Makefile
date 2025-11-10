.PHONY: setup setup-php81 setup-php85 php php81 php85 cs-fixer cs-fixer-php81 cs-fixer-php85 phpstan phpstan-php81 phpstan-php85 tests tests-php81 tests-php85

setup: setup-php81 setup-php85

setup-php81:
	rm -f composer.lock
	docker-compose up --build -d php81
	docker-compose exec php81 composer install

setup-php85:
	rm -f composer.lock
	docker-compose up --build -d php85
	docker-compose exec php85 sh -c "COMPOSER_VENDOR_DIR=vendor85 composer install"

php: php81

php81:
	docker-compose exec php81 sh

php85:
	docker-compose exec php85 sh

cs-fixer: cs-fixer-php81 cs-fixer-php85

cs-fixer-php81:
	docker-compose exec php81 vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --allow-risky=yes

cs-fixer-php85:
	docker-compose exec php85 vendor85/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --allow-risky=yes

phpstan: phpstan-php81 phpstan-php85

phpstan-php81:
	docker-compose exec php81 vendor/bin/phpstan analyse src tests --configuration=phpstan-lt-8.5.neon --memory-limit=1G

phpstan-php85:
	docker-compose exec php85 sh -c "mv vendor vendor_tmp && COMPOSER_VENDOR_DIR=vendor85 vendor85/bin/phpstan analyse src tests --configuration=phpstan-8.5-plus.neon --memory-limit=1G; mv vendor_tmp vendor"

tests: tests-php81 tests-php85

tests-php81:
	rm -rf var/cache/test
	mkdir -p var/cache/test
	docker-compose exec php81 vendor/bin/phpunit

tests-php85:
	rm -rf var/cache/test
	mkdir -p var/cache/test
	docker-compose exec php85 sh -c "COMPOSER_VENDOR_DIR=vendor85 vendor85/bin/phpunit"

ci-local:
	act -j build
