# Clients API (contrato para integração futura)

Endpoint esperado (exemplo): `GET /api/tenants/:tenant/clients`

Query params:
- `page` (int) — página atual (padrão: 1)
- `per_page` (int) — itens por página (padrão: 10)
- `search` (string) — termo de busca (nome, email, cidade)
- `sort_key` (string) — campo para ordenar (`name`, `email`, `city`, `state`)
- `sort_dir` (string) — `asc` ou `desc`

Resposta JSON esperada:
```
{
  "items": [ { /* cliente */ } ],
  "total": 123,
  "page": 1,
  "per_page": 10
}
```

Observações:
- O front atual usa `resources/js/mock/clients.js` para prototipagem client-side.
- Ao integrar o backend, altere a chamada em `resources/js/Pages/Tenant/Clients/Index.vue` para buscar do endpoint e manter o mesmo formato de resposta.
