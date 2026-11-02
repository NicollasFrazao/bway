# Day-to-day commands. Everything PHP runs inside the app container, so the host
# needs only Docker (and Node.js for the git hooks). Run `make up` first.
.DEFAULT_GOAL := help

EXEC := docker compose exec app

# The image runs as the host user, so files it writes in the bind mount stay the user's.
UID := $(shell id -u)
GID := $(shell id -g)
export UID GID

.PHONY: help up down sh install test coverage lint fix analyse architecture unused audit context gate

help: ## List the targets
	@grep -E '^[a-z-]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "} {printf "  %-13s %s\n", $$1, $$2}'

up: ## Build and start the app and MySQL; waits until http://localhost:8000 answers
	docker compose up -d --build --wait

down: ## Stop the containers (the database volume is kept)
	docker compose down

sh: ## Open a shell in the app container
	$(EXEC) bash

install: ## Install Composer and npm dependencies and the git hooks
	$(EXEC) composer install
	npm ci
	npm run prepare

test: ## Run the test suite
	$(EXEC) composer test

coverage: ## Run the tests with the 80% coverage gate
	$(EXEC) composer test:coverage

lint: ## Pint (check mode), PHP_CodeSniffer and ESLint
	$(EXEC) composer lint
	npm run lint

fix: ## Apply Pint, PHP_CodeSniffer and Rector fixes
	$(EXEC) composer fix

analyse: ## PHPStan (Larastan max), PHPMD, magic numbers and Rector dry run
	$(EXEC) composer analyse

architecture: ## Deptrac layer rules
	$(EXEC) composer check:architecture

unused: ## Unused Composer packages (composer-unused) and unused JS files and exports (knip)
	$(EXEC) composer check:unused
	npm run check:unused

audit: ## Known vulnerabilities in Composer and npm dependencies
	$(EXEC) composer audit
	npm run audit

context: ## Agent surfaces are copies of .context/docs/README.md
	$(EXEC) php artisan context:check

gate: ## The whole quality gate, one check at a time, as CI runs it
	$(EXEC) composer gate
	npm run lint
	npm run check:unused
	npm run audit
