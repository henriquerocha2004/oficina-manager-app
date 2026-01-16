USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)

start:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose -f infra/dev/docker-compose.yml -p oficina-manager up -d
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app composer install
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app cp .env.example .env
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan key:generate
	sleep 10 # Wait for the database to be ready
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app chmod -Rf 777 storage
