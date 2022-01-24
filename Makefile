DOCKER=docker
PASTELL_PATH=/var/www/pastell
EXEC_NODE=$(DOCKER) run --rm --volume ${PWD}:$(PASTELL_PATH) -it node:14-slim
EXEC_COMPOSER=$(DOCKER) run --rm --volume ${PWD}:/app --volume ${HOME}/.composer:/tmp -it composer:2
DOCKER_COMPOSE=docker-compose
DOCKER_COMPOSE_FOR_ADDITIONAL_SERVICES=ci-resources/production/docker-compose.yml
MAKE_MODULE=$(DOCKER_COMPOSE_EXEC) php ./bin/console app:studio:make-module


.DEFAULT_GOAL := help
.PHONY: help

ifneq ($(SKIP_DOCKER),true)
    DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE) exec web
    DOCKER_COMPOSE_UP=$(DOCKER_COMPOSE) up -d
else
	DOCKER_COMPOSE_EXEC=
	DOCKER_COMPOSE_UP=
endif

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
	$(DOCKER_COMPOSE_UP)

phpcs: docker-compose-up ## Check code style through docker-compose
	$(DOCKER_COMPOSE_EXEC) composer phpcs

phpcbf: docker-compose-up ## Fix all code style errors
	$(DOCKER_COMPOSE_EXEC) composer phpcbf

phpunit: docker-compose-up ## Run unit test through docker-compose
	$(DOCKER_COMPOSE_EXEC) composer test

coverage: docker-compose-up ## Run unit test through docker-compsose with coverage
	$(DOCKER_COMPOSE_EXEC) composer test-cover

codeception: docker-compose-up ## Run acceptance tests
	$(DOCKER_COMPOSE_EXEC) composer codecept

start:  ## Start all services (seda-generator, cloudooo, ..., and pastell stuff)
	$(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FOR_ADDITIONAL_SERVICES) up -d && $(DOCKER_COMPOSE) up -d

stop: ## Stop all services (pastell stuff and ... seda-generator, cloudooo, ...)
	$(DOCKER_COMPOSE) down && $(DOCKER_COMPOSE) -f $(DOCKER_COMPOSE_FOR_ADDITIONAL_SERVICES) down

module-pack-urbanisme: docker-compose-up ## Run make-module pack_urbanisme
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/dossier-autorisation-urba-draft.json ./module/ --id dossier-autorisation-urbanisme --name "Archivage des dossiers d'autorisation d'urbanisme" --restriction_pack 'pack_urbanisme'
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/document-autorisation-urba-destinataire-draft.json ./module/ --id document-autorisation-urbanisme-destinataire --name document-autorisation-urbanisme-destinataire --restriction_pack 'pack_urbanisme'
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/document-autorisation-urba-draft.json ./module/ --id document-autorisation-urbanisme --name "Document d'autorisation d'urbanisme" --restriction_pack 'pack_urbanisme'

module-pack-gfc: docker-compose-up ## Run make-module pack_gfc
	$(MAKE_MODULE) ./pack-json/pack-gfc/dossier-wgfc.json ./module/ --id gfc-dossier
	$(MAKE_MODULE) ./pack-json/pack-gfc/dossier-wgfc-destinataire.json ./module/ --id gfc-dossier-destinataire

all-module: module-pack-gfc module-pack-urbanisme

