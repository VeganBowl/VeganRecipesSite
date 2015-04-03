install:
	@rm -rf recipes
	@git clone https://github.com/VeganBowl/VeganRecipes.git recipes/

preview:
	@bin/console preview

build:
	@cd recipes/ && git pull
