up:
	@cd ./docker && docker-compose -f docker-compose.yaml up -d

build:
	@cd ./docker && docker-compose -f docker-compose.yaml build

down:
	@cd ./docker && docker-compose -f docker-compose.yaml down

ps:
	@docker ps

bash:
	@docker exec -it symapi_app_1 bash

pull:
	@git clean -f -d && git reset --hard && git pull

clean:
	@git clean -f -d && git reset --hard

fix:
	@phpcbf --standard=PSR12 ./src --encoding=utf-8 --extensions=php