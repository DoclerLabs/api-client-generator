.PHONY: $(filter-out help, $(MAKECMDGOALS))
.DEFAULT_GOAL := help

DOCKER_RUN=docker run -w /app -v $(shell pwd):/app php:7.4-cli-alpine php

help:
	@echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-10s\033[0m %s\n", $$1, $$2}'

test: ## run test suites
	$(DOCKER_RUN) vendor/bin/phpunit -c phpunit.xml.dist --colors=always

cs: ## fix code style
	$(DOCKER_RUN) vendor/bin/php-cs-fixer fix .

stan: ## statically analyse code
	$(DOCKER_RUN) vendor/bin/phpstan analyse src
