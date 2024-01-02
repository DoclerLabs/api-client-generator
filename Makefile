.PHONY: $(filter-out help, $(MAKECMDGOALS))
.DEFAULT_GOAL := help
DOCKER_COMPOSE_BINARY=$(if $(shell command -v docker-compose 2> /dev/null),docker-compose,docker compose)

help:
	@echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-10s\033[0m %s\n", $$1, $$2}'

build: ## install dependencies
	$(DOCKER_COMPOSE_BINARY) run php composer install

test: ## run test suites
	$(DOCKER_COMPOSE_BINARY) up -d --build --remove-orphans
	$(DOCKER_COMPOSE_BINARY) run --rm wait -c wiremock:8080 -t 60
	$(DOCKER_COMPOSE_BINARY) exec -T php vendor/bin/phpunit -c phpunit.xml.dist --colors=always
	$(DOCKER_COMPOSE_BINARY) down

cs: ## fix code style
	$(DOCKER_COMPOSE_BINARY) run php vendor/bin/php-cs-fixer fix .

stan: ## statically analyse code
	$(DOCKER_COMPOSE_BINARY) run php vendor/bin/phpstan analyse --memory-limit 1G

coverage: ## coverage for pipeline
	$(DOCKER_COMPOSE_BINARY) run -e COVERALLS_REPO_TOKEN=${COVERALLS_REPO_TOKEN} -e GITHUB_REF=${GITHUB_REF} -e GITHUB_ACTIONS=${GITHUB_ACTIONS} -e GITHUB_RUN_ID=${GITHUB_RUN_ID} -e GITHUB_EVENT_NAME=${GITHUB_EVENT_NAME} php vendor/bin/php-coveralls -v
