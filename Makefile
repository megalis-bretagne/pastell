DOCKER=docker
PASTELL_PATH=/var/www/pastell
EXEC_NODE=$(DOCKER) run --rm --volume ${PWD}:$(PASTELL_PATH) -it node:14-slim
EXEC_COMPOSER=$(DOCKER) run --rm --volume ${PWD}:/app --volume ${HOME}/.composer:/tmp -it composer:2
MAKE_MODULE=$(DOCKER_COMPOSE_EXEC) php ./bin/console app:studio:make-module
DOCKER_COMPOSE=docker-compose -f docker-compose.yml -f docker-compose.dev.yml

.DEFAULT_GOAL := help
.PHONY: help

ifneq ($(SKIP_DOCKER),true)
    DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE) exec web
    DOCKER_COMPOSE_UP=$(DOCKER_COMPOSE)  up -d
else
	DOCKER_COMPOSE_EXEC=
	DOCKER_COMPOSE_UP=
endif

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# Retrieve and build external dependencies

composer-install: ## Run composer install
	$(EXEC_COMPOSER) composer install --ignore-platform-reqs

npm-install: ## Run npm install
	$(EXEC_NODE) npm --prefix $(PASTELL_PATH) install

build-extensions: # Build internal extensions
	$(EXEC_COMPOSER) composer install --ignore-platform-reqs --working-dir=./extensions/pastell-depot-cmis/
	docker-compose -f ./extensions/pastell-depot-cmis/docker-compose.yml run app bash -c "php-scoper add-prefix --force && composer dump-autoload --working-dir=build"

install: npm-install composer-install build-extensions ## Install the project NPM and PHP dependencies

clean: ## Clear and remove dependencies
	rm -f web/node_modules web-mailsec/node_modules
	rm -rf node_modules vendor

# Build, launch, stop, run pastell container and services

build: # Build pastell container
	$(DOCKER_COMPOSE) build web

start:  ## Start all services
	$(DOCKER_COMPOSE) up -d --remove-orphans

stop: ## Stop all services
	$(DOCKER_COMPOSE) down

run: # Lauch a pastell container and connect to it in a bash
	$(DOCKER_COMPOSE) run web bash

# Testing

phpcs: start ## Check code style through docker-compose
	$(DOCKER_COMPOSE_EXEC) composer phpcs

phpcbf: start ## Fix all code style errors
	$(DOCKER_COMPOSE_EXEC) composer phpcbf

phpunit: start ## Run unit test through docker-compose
	$(DOCKER_COMPOSE_EXEC) composer test

coverage: start ## Run unit test through docker-compsose with coverage
	$(DOCKER_COMPOSE_EXEC) composer test-cover

codeception:  ## Run acceptance tests
	$(DOCKER_COMPOSE) -f docker-compose.codeception.yml up -d
	$(DOCKER_COMPOSE) -f docker-compose.codeception.yml exec web composer codecept
	$(DOCKER_COMPOSE_UP)

phpstan: start ## Run phpstan
	$(DOCKER_COMPOSE_EXEC) vendor/bin/phpstan

test: phpcs phpunit codeception phpstan ## Run all tests (code style, unit test, phpstan, ...)

# Module construction

module-pack-urbanisme: start ## Run make-module pack_urbanisme
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/dossier-autorisation-urba-draft.json ./module/ --id dossier-autorisation-urbanisme --name "Archivage des dossiers d'autorisation d'urbanisme" --restriction_pack 'pack_urbanisme'
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/document-autorisation-urba-destinataire-draft.json ./module/ --id document-autorisation-urbanisme-destinataire --name document-autorisation-urbanisme-destinataire --restriction_pack 'pack_urbanisme'
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/document-autorisation-urba-draft.json ./module/ --id document-autorisation-urbanisme --name "Document d'autorisation d'urbanisme" --restriction_pack 'pack_urbanisme'

module-pack-gfc: start ## Run make-module pack_gfc
	$(MAKE_MODULE) ./pack-json/pack-gfc/dossier-wgfc.json ./module/ --id gfc-dossier
	$(MAKE_MODULE) ./pack-json/pack-gfc/dossier-wgfc-destinataire.json ./module/ --id gfc-dossier-destinataire

all-module: module-pack-gfc module-pack-urbanisme ## Build all modules
