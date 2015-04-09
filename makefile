compose = sudo docker-compose

install:
	@rm -rf recipes
	@git clone https://github.com/VeganBowl/VeganRecipes.git recipes/

# Dependencies management
npm-install:
	@$(compose) run --rm node npm install

composer-install:
	@$(compose) run --rm app composer install

# Build
deleteBuild:
	@sudo rm -rf build

update-recipes:
	@cd recipes/ && git pull

build-pages:
	@$(compose) run --rm app bin/console build

assets-install:
	@$(compose) run --rm node gulp install

chmod-build:
	@sudo chmod -R 755 build

# Meta commands
build: deleteBuild update-recipes build-pages assets-install chmod-build

preview:
	@bin/console preview

# Open cli
cli-app:
	@$(compose) run --rm app bash

cli-node:
	@$(compose) run --rm node bash

# Build docker image
build-docker:
	@$(compose) stop
	@$(compose) build

.PHONY: build
