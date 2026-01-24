USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)

start:
	USER_ID=$(USER_ID) GROUP_ID=$(GROUP_ID) docker compose -f infra/dev/docker-compose.yml up -d
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app composer install
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app cp .env.example .env
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan key:generate
	sleep 20 # Wait for the database to be ready
	$(MAKE) migrate-all
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app chmod -Rf 777 storage

migrate-all:
	@echo "Running migrations on all databases (manager, tenant, manager_test, tenant_test)..."
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate --database=manager --force
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate --database=tenant --path=database/migrations/tenants --force
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate --database=manager_test --force
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate --database=tenant_test --path=database/migrations/tenants --force
	@echo "All migrations completed successfully!"
	@echo "Running seeders..."
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed --database=manager --class=Database\\Seeders\\Admin\\AdminDatabaseSeeder
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed --database=manager_test --class=Database\\Seeders\\Admin\\AdminDatabaseSeeder
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed --database=tenant --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed --database=tenant_test --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder
	@echo "All seeders completed successfully!"

test-db-refresh:
	@echo "Dropping and recreating test databases..."
	docker exec oficina-manager-db psql -U user -d postgres -c "DROP DATABASE IF EXISTS oficina_manager_test;"
	docker exec oficina-manager-db psql -U user -d postgres -c "DROP DATABASE IF EXISTS oficina_tenant_test;"
	docker exec oficina-manager-db psql -U user -d postgres -c "CREATE DATABASE oficina_manager_test;"
	docker exec oficina-manager-db psql -U user -d postgres -c "CREATE DATABASE oficina_tenant_test;"
	@echo "Running migrations on test databases..."
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate --database=manager_test --force
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan migrate --database=tenant_test --path=database/migrations/tenants --force
	@echo "Running seeders on test databases..."
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed --database=manager_test --class=Database\\Seeders\\Admin\\AdminDatabaseSeeder
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan db:seed --database=tenant_test --class=Database\\Seeders\\Tenant\\TenantDatabaseSeeder
	@echo "Test databases refreshed successfully!"

test:
	docker exec -u $(USER_ID):$(GROUP_ID) oficina-manager-app php artisan test
