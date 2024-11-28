include .env

# Colors
COLOR_RESET   = \033[0m
COLOR_ERROR   = \033[31m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

## Help - display content of "make" command
.PHONY: help
help:
	@printf "\n${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	@printf "make [target]\n"
	@printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	@awk '/^[^.#][a-zA-Z0-9_\/\-.@]*:/ { \
		if (substr(lastLine, 1, 1) == ".") { \
			lastLine = prevToLastLine; \
		} \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 1, index($$1, ":") - 1); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "  ${COLOR_INFO}%-20s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ prevToLastLine = lastLine } \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

## Docker build
.PHONY: build
build:
	docker compose build

## Docker down
.PHONY: down
down:
	docker compose down -v

## Docker up
.PHONY: run
run:
	@if [[ -z $$(docker ps -a -q -f name=${APP_CONTAINER_NAME}) ]]; then \
		docker compose up -d; \
	else \
		docker compose run --rm -it app bash; \
	fi

## Docker container console
.PHONY: console
console:
	@if [[ -z $$(docker ps -a -q -f name=${APP_CONTAINER_NAME}) ]]; then \
		echo "$(COLOR_ERROR)ERROR: Docker container '${APP_CONTAINER_NAME}' is not found.$(COLOR_RESET)"; \
		exit 0; \
	else \
		docker compose run --rm -it app bash; \
	fi

