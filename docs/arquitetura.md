# Arquitetura do Projeto — SaaS de Gestão para Oficinas

## 1. Contexto e Objetivo

Este projeto nasce como um **SaaS de gestão para oficinas mecânicas de pequeno porte**, focado em resolver problemas reais do dia a dia (organização, estoque e ordens de serviço), evitando complexidade desnecessária.

O objetivo da **V1** é entregar um sistema **usável**, estável e evolutivo, não uma solução perfeita ou completa.

Este documento existe para **evitar decisões erradas no futuro**, especialmente:

* Overengineering
* Reescritas desnecessárias
* Mistura de regras de negócio com framework

---

## 2. Princípios Arquiteturais (decisões conscientes)

Estas decisões foram tomadas deliberadamente e **não devem ser revistas sem motivo forte**:

* **Laravel como monólito bem organizado**
* **Multi-tenant por banco de dados** (isolamento forte)
* **CRUD simples onde não há regra de negócio relevante**
* **Domínio rico apenas onde há regras reais**
* **Framework resolve problemas técnicos**
* **Domínio resolve regras de negócio**

Se um módulo não tem regras complexas, ele **não precisa** de Domain Model.

---

## 3. Estratégia de Multi-Tenancy

### 3.1 Modelo escolhido

Foi escolhido **Database per Tenant**, pois:

* Evita vazamento de dados
* Facilita backup/restauração por cliente
* Simplifica LGPD
* Evita `tenant_id` espalhado pelo código

### 3.2 Bancos

#### Banco Central (SaaS)

Responsável apenas por **controle do sistema**, nunca por dados da oficina.

Tabelas típicas:

* tenants
* users (login)
* planos (futuro)

#### Banco do Tenant (Oficina)

Responsável por **todas as regras de negócio**.

Tabelas:

* clientes
* veiculos
* servicos
* fornecedores
* produtos
* movimentos_estoque
* ordens_servico
* ordem_servico_itens

---

## 4. Organização do Código (Laravel)

A aplicação é um **monólito modular**, não microserviços.

```
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/               # Eloquent (persistência)
├── Domain/               # Regras de negócio reais
│   ├── Estoque/
│   │   ├── Entities/
│   │   ├── Services/
│   │   └── Exceptions/
│   └── OrdemServico/
│       ├── Entities/
│       ├── Services/
│       └── Enums/
├── Application/
│   └── Services/         # Casos de uso
```

---

## 5. Classificação dos Módulos

### 5.1 CRUD Simples (sem Domínio Rico)

Esses módulos **não justificam** Domain Model neste momento:

* Cliente
* Veículo
* Serviço
* Fornecedor
* Usuário

Fluxo:

FormRequest → Controller → Eloquent

Regras típicas:

* Campos obrigatórios
* Formato de dados
* Unicidade

Tudo isso **fica no framework**.

---

### 5.2 Domínio Rico (onde faz sentido)

Esses módulos possuem **regras que mudam comportamento**:

#### Estoque

Regras:

* Entrada e saída
* Não permitir saldo negativo
* Rastreabilidade de movimentos

#### Ordem de Serviço (OS)

Regras:

* Estados (aberta, em andamento, finalizada, cancelada)
* Vínculo com serviços e produtos
* Impacto direto no estoque

Fluxo:

Controller → Application Service → Domain → Persistência

---

## 6. Validações (decisão importante)

### Validação de Entrada

Responsabilidade do **FormRequest**:

* Campos obrigatórios
* Tipos
* Tamanho
* Formato

### Validação de Negócio

Responsabilidade do **Domínio**:

* Regras que refletem o mundo real
* Estados inválidos
* Operações proibidas

Nunca misturar as duas coisas.

---

## 7. Infraestrutura (V1)

A infra da V1 é **mínima e suficiente**:

* VPS única
* Docker + Docker Compose
* Nginx
* PHP (Laravel)
* MySQL (central + tenants)

Sem Kubernetes.
Sem cloud complexa.

---

## 8. Evolução Planejada

* **V1**: Cadastro + Estoque
* **V1.1**: Ordem de Serviço
* **V2**: Financeiro, Billing, Relatórios

Cada versão só começa quando a anterior estiver **estável**.

---

**Autor:** Henrique Rocha
**Última revisão:** Janeiro / 2026
