# Producao com GHCR

Este diretorio contem a estrutura de container para producao com imagens publicadas no GHCR.

## Arquivos

- `Dockerfile`: build multi-stage com dois alvos
  - `app-runtime` (php-fpm)
  - `web-runtime` (nginx)
- `docker-compose.prod.yml`: sobe app, web, worker e scheduler usando imagens prontas
- `.env.prod.example`: modelo de variaveis para servidor
- `entrypoint.sh`: warmup opcional e migracoes opcionais
- `healthcheck.sh`: healthcheck simples do container PHP

## Segredos no GitHub

Crie os secrets de repositorio:

- `PROD_SERVER_HOST`
- `PROD_SERVER_USER`
- `PROD_SERVER_KEY`
- `GHCR_USERNAME`
- `GHCR_TOKEN` (scope `read:packages`)

## Fluxo

1. Push na branch `main`.
2. Workflow `Build and Publish Production Images` publica as imagens:
   - `ghcr.io/<owner>/oficina-manager-app-app:sha-<commit>`
   - `ghcr.io/<owner>/oficina-manager-app-web:sha-<commit>`
   - `latest` na branch default
3. Workflow `Deploy Production` conecta no servidor via SSH.
4. No servidor, o workflow faz `docker compose pull` e `up -d` com as tags SHA.
5. Depois executa migracoes:
   - `php artisan migrate --force`
   - `php artisan app:migrate-tenants`

## Preparo do servidor

1. Checkout do repositorio em `/var/www/oficina-manager-app`.
2. Copie `infra/prod/.env.prod.example` para `infra/prod/.env.prod` e ajuste os valores.
3. Garanta Docker e Docker Compose instalados.
4. Garanta que a porta `80` esteja disponivel para o container web.

## Rollback rapido

Use uma tag SHA anterior:

```bash
cd /var/www/oficina-manager-app
export APP_IMAGE=ghcr.io/<owner>/oficina-manager-app-app:sha-<sha_anterior>
export WEB_IMAGE=ghcr.io/<owner>/oficina-manager-app-web:sha-<sha_anterior>
docker compose -f infra/prod/docker-compose.prod.yml up -d
```
