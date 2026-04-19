# Roadmap de Competitividade

Lista de funcionalidades e melhorias necessárias para o produto ser competitivo
frente a soluções como Oficina Digital, Mecânico Digital e AutoGestor.

---

## Prioridade Alta — Eliminatórios de venda

Funcionalidades que o cliente pergunta antes de fechar. Sem elas, a maioria das
oficinas não assina.

### NF-e / NFS-e
- Emissão de Nota Fiscal de produto (NF-e) e serviço (NFS-e) direto do sistema
- Integração com SEFAZ (via biblioteca como NFePHP ou serviço de terceiro como Focus NFe)
- Cancelamento e carta de correção
- Armazenamento e reemissão de XMLs

### Agendamento
- Calendário de serviços por mecânico ou baia
- Visualização diária/semanal
- Associar agendamento a um cliente/veículo existente
- Notificação de lembrete para o cliente

### Integração de Pagamento (PIX)
- Geração de cobrança PIX via Asaas, PagarMe ou Efí Bank
- Registro automático do pagamento na OS ao confirmar
- Histórico financeiro por OS

---

## Prioridade Média — Diferencial competitivo

Funcionalidades que os concorrentes já têm e que aumentam retenção.

### WhatsApp
- Notificação automática quando a OS muda de status (aprovada, pronta para retirada)
- Envio de orçamento para aprovação pelo cliente via link
- Integração via Evolution API ou Z-API (self-hosted ou cloud)

### Relatórios Financeiros
- Fluxo de caixa por período
- Receita por tipo de serviço
- Lucro por OS (receita - custo de peças)
- Exportação em CSV/PDF

### Dashboard com métricas reais
- OS abertas, em andamento, finalizadas no mês
- Receita do mês vs. mês anterior
- Peças com estoque crítico
- Mecânicos com mais OS finalizadas

---

## Prioridade Média — Completude do produto atual

Partes do sistema que existem mas precisam ser finalizadas ou polidas.

### UX / Frontend
- Validar todos os fluxos no mobile (navegador responsivo)
- Adicionar testes frontend com Vitest para os fluxos críticos (criação de OS, cadastro de cliente)
- Revisar feedback de erro nos formulários (mensagens claras para o usuário final)

### Admin / SaaS Layer
- Dashboard do admin com métricas de tenants (ativos, OS criadas, uso)
- Gestão de planos com limites (usuários por tenant, OS por mês)
- Onboarding automatizado ao criar tenant (migrations + dados iniciais + e-mail de boas-vindas)
- Integração de cobrança recorrente (Asaas ou Stripe) para cobrar assinatura dos tenants

### Notificações internas
- Central de notificações no sistema (OS aprovada, estoque crítico, pagamento recebido)

---

## Prioridade Baixa — Evolução de longo prazo

Funcionalidades que aumentam o ticket médio e abrem novos segmentos.

### App Mobile nativo
- PWA como primeiro passo (instala pelo navegador, funciona offline básico)
- App nativo (React Native / Capacitor) se houver demanda validada

### Integração com fornecedores
- Consulta de preço de peças via API (Pecas.com ou similares)
- Pedido de compra direto para fornecedor

### Multi-usuário com permissões granulares
- Perfis: dono, mecânico, atendente, financeiro
- Cada perfil com acesso restrito às suas funcionalidades

### Histórico e fidelização
- Alerta automático de revisão com base no histórico do veículo (ex: troca de óleo a cada 5.000km)
- Relatório de veículos sem retorno há X meses

### Integração fiscal avançada
- SPED Fiscal
- Relatórios para contabilidade

---

## Referências — Concorrentes diretos no Brasil

| Produto | Preço estimado | Pontos fortes |
|---|---|---|
| Oficina Digital | R$ 99–299/mês | NF-e, agenda, WhatsApp, app mobile |
| Mecânico Digital | R$ 79–199/mês | Interface simples, suporte ativo |
| AutoGestor | R$ 89–249/mês | Relatórios financeiros, multi-unidade |
| Caris | R$ 120–350/mês | Integração com fornecedores, orçamento online |
