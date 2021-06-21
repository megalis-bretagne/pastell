DOCKER=docker
PASTELL_PATH=/var/www/pastell
EXEC_NODE=$(DOCKER) run --rm --volume ${PWD}:$(PASTELL_PATH) -it node:14-slim
EXEC_COMPOSER=$(DOCKER) run --rm --volume ${PWD}:/app --volume ${HOME}/.composer:/tmp -it composer:2
DOCKER_COMPOSE=docker-compose

.DEFAULT_GOAL := help
.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

composer-install: ## Run composer install
	$(EXEC_COMPOSER) composer install --ignore-platform-reqs

npm-install: ## Run npm install
	$(EXEC_NODE) npm --prefix $(PASTELL_PATH) install

install: npm-install composer-install ## Install the project NPM and PHP dependencies

clean: ## Clear and remove dependencies
	rm -f web/node_modules web-mailsec/node_modules
	rm -rf node_modules vendor

test: phpcs phpunit codeception ## Run all tests (code style, unit test, ...)

docker-compose-up: ## Up all container
	$(DOCKER_COMPOSE) up -d

phpcs: docker-compose-up ## Check code style through docker-compose
	$(DOCKER_COMPOSE) exec phpunit composer phpcs

phpcbf: docker-compose-up ## Fix all code style errors
	$(DOCKER_COMPOSE) exec phpunit composer phpcbf

phpunit: docker-compose-up ## Run unit test through docker-compose
	$(DOCKER_COMPOSE) exec phpunit composer test

coverage: docker-compose-up ## Run unit test through docker-compsose with coverage
	$(DOCKER_COMPOSE) exec phpunit composer test-cover

codeception: docker-compose-up ## Run acceptance tests
	$(DOCKER_COMPOSE) exec web_test composer codecept


