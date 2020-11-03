artisan:
	docker-compose exec php-cli php artisan

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-build:
	docker-compose up --build -d

test:
	docker-compose exec php-cli vendor/bin/phpunit
