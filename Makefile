.PHONY: $(filter-out help, $(MAKECMDGOALS))
.DEFAULT_GOAL := help

help:
	@echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-10s\033[0m %s\n", $$1, $$2}'

test: ## run test suites
	docker-compose up -d --build --remove-orphans
	docker-compose run --rm wait -c wiremock:8080 -t 60
	docker-compose exec -T php vendor/bin/phpunit -c phpunit.xml.dist --colors=always
	docker-compose down

cs: ## fix code style
	docker-compose run php vendor/bin/php-cs-fixer fix .

stan: ## statically analyse code
	docker-compose run php vendor/bin/phpstan analyse src

coverage: ## coverage for pipeline
	docker-compose run -e COVERALLS_REPO_TOKEN=${COVERALLS_REPO_TOKEN} -e GITHUB_REF=${GITHUB_REF} -e GITHUB_ACTIONS=${GITHUB_ACTIONS} -e GITHUB_RUN_ID=${GITHUB_RUN_ID} -e GITHUB_EVENT_NAME=${GITHUB_EVENT_NAME} php vendor/bin/php-coveralls -v
