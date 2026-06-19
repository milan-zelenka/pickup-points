.PHONY: up down clean shell install migrate import phpunit phpstan cs cs-fix check

PHP = docker compose exec php

up:
	docker compose up -d

down:
	docker compose down

clean:
	docker compose down -v --rmi local

shell:
	docker compose exec php bash

install:
	[ -f .env ] || cp .env.dist .env
	$(PHP) composer install
	$(MAKE) migrate

migrate:
	$(PHP) composer migrate

import:
	$(PHP) bin/console pickup-points:import balikovna

check:
	@echo "==> PHPStan"
	$(MAKE) phpstan
	@echo "==> php-cs-fixer (dry-run)"
	$(MAKE) cs
	@echo "==> PHPUnit"
	$(MAKE) phpunit
	@echo "All checks passed."

phpunit:
	$(PHP) composer phpunit

phpstan:
	$(PHP) composer phpstan

cs:
	$(PHP) composer cs

cs-fix:
	$(PHP) composer cs-fix
