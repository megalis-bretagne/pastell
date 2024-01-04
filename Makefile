DOCKER=docker
PASTELL_PATH=/var/www/pastell
EXEC_NODE=$(DOCKER) run --rm --volume ${PWD}:$(PASTELL_PATH) -it node:14-slim
EXEC_COMPOSER=$(DOCKER) run --rm --volume ${PWD}:/app --volume ${HOME}/.composer:/tmp -it composer:2
MAKE_MODULE=$(DOCKER_COMPOSE_EXEC) php ./bin/console app:studio:make-module
DOCKER_COMPOSE=docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml
EXEC_TRIVY=$(DOCKER) run -it --rm -v /var/run/docker.sock:/var/run/docker.sock -v ~/.cache:/root/.cache -v ${PWD}/.trivyignore:/.trivyignore aquasec/trivy image --severity HIGH,CRITICAL pastell-local-dev

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

composer-install: ## Run composer install
	$(EXEC_COMPOSER) composer install --ignore-platform-reqs

npm-install: ## Run npm install
	$(EXEC_NODE) npm --prefix $(PASTELL_PATH) install

install: npm-install composer-install build-extensions ## Install the project NPM and PHP dependencies

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

codeception:  ## Run acceptance tests
	$(DOCKER_COMPOSE) -f docker/docker-compose.codeception.yml up -d
	$(DOCKER_COMPOSE) -f docker/docker-compose.codeception.yml exec web composer codecept
	$(DOCKER_COMPOSE_UP)

phpstan: docker-compose-up ## Run phpstan
	$(DOCKER_COMPOSE_EXEC) vendor/bin/phpstan --xdebug

trivy: ## Run trivy
	$(EXEC_TRIVY)

start:  ## Start all services
	$(DOCKER_COMPOSE) up -d --remove-orphans

start-minio:  ## Start all services with minio
	$(DOCKER_COMPOSE) -f docker/docker-compose.minio.yml up -d --remove-orphans

stop: ## Stop all services
	$(DOCKER_COMPOSE) down

stop-minio:  ## Start all services with minio
	$(DOCKER_COMPOSE) -f docker/docker-compose.minio.yml down

module-pack-urbanisme: docker-compose-up ## Run make-module pack_urbanisme
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/dossier-autorisation-urba-draft.json ./module/ --id dossier-autorisation-urbanisme --name "Dossiers d'autorisation d'urbanisme (archivage)" --restriction_pack 'pack_urbanisme'
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/document-autorisation-urba-destinataire-draft.json ./module/ --id document-autorisation-urbanisme-destinataire --name "Document d'autorisation d'urbanisme (destinataire)" --restriction_pack 'pack_urbanisme'
	$(MAKE_MODULE) ./pack-json/pack-urbanisme/document-autorisation-urba-draft.json ./module/ --id document-autorisation-urbanisme --name "Document d'autorisation d'urbanisme" --restriction_pack 'pack_urbanisme'

module-pack-rh: docker-compose-up ## Run make-module pack-rh
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-document-individuel.json ./module/ --id rh-document-individuel --name "Document individuel" --restriction_pack 'pack_rh'
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-document-individuel-destinataire.json ./module/ --id rh-document-individuel-destinataire --name "Document individuel (destinataire)" --restriction_pack 'pack_rh'
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-bulletin-salaire.json ./module/ --id rh-bulletin-salaire --name "Bulletin de salaire" --restriction_pack 'pack_rh'
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-bulletin-salaire-destinataire.json ./module/ --id rh-bulletin-salaire-destinataire --name "Bulletin de salaire (destinataire)" --restriction_pack 'pack_rh'
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-archivage-dossier-agent.json ./module/ --id rh-archivage-dossier-agent --name "Eléments du dossier individuel de l'agent (archivage)" --restriction_pack 'pack_rh'
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-archivage-collectif.json ./module/ --id rh-archivage-collectif --name "Données de gestion collective (fichier unitaire) (archivage)" --restriction_pack 'pack_rh'
	$(MAKE_MODULE) ./pack-json/pack-rh/draft-rh-archivage-collectif-zip.json ./module/ --id rh-archivage-collectif-zip --name "Données de gestion collective (fichier compressé) (archivage)" --restriction_pack 'pack_rh'

module-pack-gfc: docker-compose-up ## Run make-module pack_gfc
	$(MAKE_MODULE) ./pack-json/pack-gfc/dossier-wgfc.json ./module/ --id gfc-dossier
	$(MAKE_MODULE) ./pack-json/pack-gfc/dossier-wgfc-destinataire.json ./module/ --id gfc-dossier-destinataire

module-pack-actes: docker-compose-up ## Run make-module pack_actes
	$(MAKE_MODULE) ./pack-json/pack-actes/ls-actes-publication-draft.json ./module/ --id ls-actes-publication --name "Actes publication"
	$(MAKE_MODULE) ./pack-json/pack-actes/ls-dossier-seance-draft.json ./module/ --id ls-dossier-seance --name "Dossier de séance (archivage)" --restriction_pack 'pack_dossier_seance'

module-pack-document: docker-compose-up ## Run make-module pack_document
	$(MAKE_MODULE) ./pack-json/pack-document/ls-document-pdf-draft.json ./module/ --id ls-document-pdf --name "Document PDF"
	$(MAKE_MODULE) ./pack-json/pack-document/ls-recup-parapheur-draft.json ./module/ --id ls-recup-parapheur --name "Récupération parapheur" --restriction_pack 'pack_recup_fin_parapheur'
	$(MAKE_MODULE) ./pack-json/pack-document/ls-document-pdf-draft-destinataire.json ./module/ --id ls-document-pdf-destinataire --name "Document PDF (destinataire)"


all-module: module-pack-gfc module-pack-urbanisme module-pack-rh module-pack-actes module-pack-document

build-extensions: ## Build extensions
	$(EXEC_COMPOSER) composer install --ignore-platform-reqs --working-dir=./extensions/pastell-depot-cmis/
	docker compose -f ./extensions/pastell-depot-cmis/docker-compose.yml run app bash -c "php-scoper add-prefix --force && composer dump-autoload --working-dir=build"

build: ## Build the container
	$(DOCKER_COMPOSE) build web

bash: docker-compose-up ## Get a bash console
	$(DOCKER_COMPOSE) exec web bash

logs: docker-compose-up ## Display last application logs (in follow mode)
	$(DOCKER_COMPOSE) logs -t 50 -f web
