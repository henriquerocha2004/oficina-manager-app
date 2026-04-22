<template>
  <TenantLayout title="Ajuda / FAQ" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8 text-center">
        <i class="ki-outline ki-information-2 text-5xl text-primary mb-3 block"></i>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-foreground mb-2">Central de Ajuda</h1>
        <p class="text-gray-500 dark:text-muted-foreground">Encontre respostas sobre como usar as funcionalidades do sistema.</p>
      </div>

      <!-- Busca -->
      <div class="max-w-xl mx-auto mb-8">
        <div class="relative">
          <i class="ki-outline ki-magnifier absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar perguntas..."
            class="kt-input pl-10 w-full"
          />
        </div>
      </div>

      <!-- Sem resultados -->
      <div v-if="filteredSections.length === 0" class="text-center py-16 text-gray-400 dark:text-muted-foreground">
        <i class="ki-outline ki-search-list text-4xl mb-3 block"></i>
        <p>Nenhuma pergunta encontrada para "<strong>{{ search }}</strong>".</p>
      </div>

      <!-- Seções -->
      <div class="max-w-3xl mx-auto space-y-6">
        <div
          v-for="section in filteredSections"
          :key="section.title"
          class="card bg-white dark:bg-card"
        >
          <div class="card-header border-b border-border pb-4 flex items-center gap-3 px-5 sm:px-6 pt-5">
            <i :class="section.icon + ' text-2xl text-primary'"></i>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-foreground">{{ section.title }}</h2>
          </div>
          <div class="card-body px-5 sm:px-6 pt-4 pb-5 space-y-2">
            <details
              v-for="item in section.items"
              :key="item.question"
              class="faq-item group"
            >
              <summary class="faq-summary">
                <span class="flex-1 text-sm font-medium text-gray-800 dark:text-foreground">{{ item.question }}</span>
                <i class="ki-outline ki-plus faq-icon-open text-gray-400 text-sm shrink-0"></i>
                <i class="ki-outline ki-minus faq-icon-close text-primary text-sm shrink-0"></i>
              </summary>
              <div class="faq-answer text-sm text-gray-600 dark:text-muted-foreground leading-relaxed" v-html="item.answer"></div>
            </details>
          </div>
        </div>
      </div>
    </div>
  </TenantLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { usePermissions } from '@/Composables/usePermissions.js';

const { isAdministrator, isReception, isMechanic } = usePermissions();

const search = ref('');
const breadcrumbs = [{ label: 'Ajuda / FAQ' }];

const allSections = [
  {
    title: 'Primeiros Passos',
    icon: 'ki-outline ki-rocket',
    roles: ['administrator', 'reception', 'mechanic'],
    items: [
      {
        question: 'Como navegar pelo sistema?',
        answer: `Use o menu lateral à esquerda para acessar as diferentes áreas do sistema. No desktop, o menu pode ser expandido ou recolhido clicando no botão de menu. No celular, toque no ícone <strong>☰</strong> no topo da tela para abrir o menu.`,
      },
      {
        question: 'Como alterar o tema (claro/escuro)?',
        answer: `Clique no ícone de perfil na parte inferior do menu lateral e acesse <strong>Minha Conta</strong>. Lá você encontrará a opção de alternar entre o tema claro e escuro.`,
      },
      {
        question: 'O que cada perfil de usuário pode fazer?',
        answer: `
          <ul class="list-disc pl-5 space-y-1 mt-1">
            <li><strong>Administrador:</strong> acesso completo — clientes, veículos, ordens de serviço, usuários e configurações.</li>
            <li><strong>Recepção:</strong> acessa clientes, veículos e ordens de serviço.</li>
            <li><strong>Mecânico:</strong> acessa apenas as ordens de serviço para registrar diagnóstico, itens e acompanhar o progresso.</li>
          </ul>
        `,
      },
    ],
  },
  {
    title: 'Clientes',
    icon: 'ki-outline ki-profile-circle',
    roles: ['administrator', 'reception'],
    items: [
      {
        question: 'Como cadastrar um novo cliente?',
        answer: `Na página <strong>Clientes</strong>, clique em <strong>"Novo Cliente"</strong> no canto superior direito. Um painel lateral será aberto para preencher os dados: nome, CPF/CNPJ, telefone, e-mail e endereço. Clique em <strong>Salvar</strong> para concluir. Você também pode criar um cliente diretamente ao abrir uma nova Ordem de Serviço.`,
      },
      {
        question: 'Como editar ou excluir um cliente?',
        answer: `Na lista de clientes, clique no ícone de <strong>lápis</strong> para editar ou no ícone de <strong>lixeira</strong> para excluir. O sistema pedirá confirmação antes de excluir.`,
      },
      {
        question: 'Como filtrar a lista de clientes?',
        answer: `Use o campo de busca para localizar pelo nome. Clique em <strong>Filtros</strong> para filtrar por <strong>Estado</strong>, <strong>Cidade</strong> ou <strong>Tipo</strong> (Pessoa Física ou Jurídica). Os filtros são salvos automaticamente para a próxima visita.`,
      },
      {
        question: 'Como exportar a lista de clientes?',
        answer: `Clique no botão <strong>Exportar</strong> (ícone de download). O sistema gera um arquivo <strong>CSV</strong> com os dados visíveis na lista, respeitando os filtros ativos.`,
      },
    ],
  },
  {
    title: 'Veículos',
    icon: 'ki-outline ki-car',
    roles: ['administrator', 'reception'],
    items: [
      {
        question: 'Como cadastrar um veículo?',
        answer: `Na página <strong>Veículos</strong>, clique em <strong>"Novo Veículo"</strong>. Informe placa, tipo (carro, moto, caminhão ou van), marca, modelo, ano e cor, e vincule a um cliente existente. Você também pode cadastrar um veículo diretamente ao criar uma nova Ordem de Serviço.`,
      },
      {
        question: 'Como ver o histórico de um veículo?',
        answer: `Na lista de veículos, clique no ícone de histórico do veículo. Você verá todas as Ordens de Serviço já registradas para aquele veículo. Dentro de uma OS aberta, o link <strong>"Ver histórico do veículo →"</strong> na barra lateral leva diretamente a esse histórico.`,
      },
    ],
  },
  {
    title: 'Ordens de Serviço',
    icon: 'ki-outline ki-note-2',
    roles: ['administrator', 'reception', 'mechanic'],
    items: [
      {
        question: 'Como criar uma nova Ordem de Serviço?',
        answer: `Na página <strong>Ordens de Serviço</strong>, clique em <strong>"Nova OS"</strong>. Um modal será aberto com três etapas:
          <ol class="list-decimal pl-5 space-y-1 mt-2">
            <li>Busque e selecione o <strong>cliente</strong> (ou crie um novo diretamente no modal).</li>
            <li>Selecione o <strong>veículo</strong> do cliente (ou cadastre um novo).</li>
            <li>Descreva o <strong>problema relatado</strong> pelo cliente (opcional).</li>
          </ol>
          Clique em <strong>"Criar Rascunho"</strong>. A OS será criada no status <strong>Rascunho</strong> e você será direcionado para ela.`,
      },
      {
        question: 'O que é o Kanban e como funciona?',
        answer: `No desktop, as ordens são exibidas em colunas por status: <strong>Iniciado · Aguardando Aprovação · Aprovado · Serviço em Andamento · Aguardando Pagamento</strong>. Cada coluna mostra as OS naquele estágio. No celular a visualização é em lista. Você pode alternar entre Kanban e lista usando os botões no canto superior direito da página.`,
      },
      {
        question: 'Qual é o fluxo de status de uma OS?',
        answer: `
          <ol class="list-decimal pl-5 space-y-1 mt-1">
            <li><strong>Rascunho</strong> — OS recém-criada. O técnico preenche o diagnóstico e adiciona itens.</li>
            <li><strong>Aguardando Aprovação</strong> — enviada para o cliente aprovar. Exige diagnóstico preenchido e pelo menos um item.</li>
            <li><strong>Aprovado</strong> — cliente aprovou. Clique em "Iniciar Trabalho" para começar.</li>
            <li><strong>Serviço em Andamento</strong> — execução do serviço. Ao finalizar clique em "Finalizar Trabalho".</li>
            <li><strong>Aguardando Pagamento</strong> — serviço concluído. Registre o pagamento na aba Financeiro.</li>
            <li><strong>Concluído</strong> — OS encerrada após pagamento integral.</li>
          </ol>
          Em qualquer etapa (exceto Concluído e Cancelado) é possível <strong>Cancelar a OS</strong> pelo menu de ações (⋮).`,
      },
      {
        question: 'Como preencher o diagnóstico técnico?',
        answer: `Dentro da OS, acesse a aba <strong>Diagnóstico</strong>. O campo <strong>"Problema Relatado"</strong> exibe o que o cliente descreveu ao abrir a OS (somente leitura). O campo <strong>"Diagnóstico do Técnico"</strong> é editável: escreva os achados e o texto é salvo automaticamente ao sair do campo. Você também pode <strong>anexar fotos</strong> do veículo nesta mesma aba.`,
      },
      {
        question: 'Como adicionar serviços e peças a uma OS?',
        answer: `Acesse a aba <strong>Itens</strong> dentro da OS e clique em <strong>"Adicionar Item"</strong>. Selecione o tipo:
          <ul class="list-disc pl-5 space-y-1 mt-1">
            <li><strong>Serviço:</strong> ao digitar, o sistema busca automaticamente os serviços cadastrados e preenche o preço. Você pode ajustar o valor.</li>
            <li><strong>Peça:</strong> campo livre; informe o nome, a quantidade e o preço unitário.</li>
          </ul>
          Clique em <strong>"Salvar Item"</strong>. Para remover, clique no ícone de lixeira ao lado do item.`,
      },
      {
        question: 'Como registrar o pagamento de uma OS?',
        answer: `Na aba <strong>Financeiro</strong> da OS você encontra o resumo de valores (serviços + peças − desconto = total). Clique em <strong>"+ Registrar Pagamento"</strong>, escolha a forma (Dinheiro, Crédito, Débito ou PIX) e informe o valor. É possível registrar <strong>pagamentos parciais</strong> em múltiplas etapas. Quando o saldo for totalmente quitado, a OS é automaticamente marcada como <strong>Concluída</strong>.`,
      },
      {
        question: 'Como aplicar desconto em uma OS?',
        answer: `Na aba <strong>Financeiro</strong>, edite o campo <strong>Desconto</strong> diretamente no resumo de valores. O total é recalculado na hora.`,
      },
      {
        question: 'Como imprimir ou gerar PDF de uma OS?',
        answer: `No cabeçalho da OS, clique no ícone de <strong>impressora</strong>. O sistema abrirá o PDF da OS em uma nova aba, pronto para imprimir ou salvar. Para imprimir o recibo de pagamento, acesse a aba <strong>Financeiro</strong> e clique em <strong>"Imprimir Recibo"</strong>.`,
      },
      {
        question: 'Como cancelar uma OS?',
        answer: `Clique no botão de menu <strong>⋮</strong> no cabeçalho da OS e selecione <strong>"Cancelar OS"</strong>. Informe o motivo do cancelamento (obrigatório). Se houver pagamentos registrados, o sistema realizará o <strong>estorno automático</strong> do valor pago. A ação não pode ser desfeita.`,
      },
      {
        question: 'Como ver a linha do tempo de uma OS?',
        answer: `Acesse a aba <strong>Linha do Tempo</strong> dentro da OS. Ela registra automaticamente todos os eventos: criação, mudanças de status, adição de itens e pagamentos.`,
      },
    ],
  },
  {
    title: 'Usuários',
    icon: 'ki-outline ki-user',
    roles: ['administrator'],
    items: [
      {
        question: 'Como criar um novo usuário?',
        answer: `Na página <strong>Usuários</strong>, clique em <strong>"Novo Usuário"</strong>. Preencha nome, e-mail, senha e selecione o <strong>perfil de acesso</strong> (Administrador, Recepção ou Mecânico). O usuário poderá acessar o sistema imediatamente com as permissões do perfil escolhido.`,
      },
      {
        question: 'Como ativar ou desativar um usuário?',
        answer: `Edite o usuário clicando no ícone de <strong>lápis</strong>. Altere o campo de status para <strong>Ativo</strong> ou <strong>Inativo</strong>. Usuários inativos não conseguem acessar o sistema.`,
      },
      {
        question: 'Como alterar a senha de um usuário?',
        answer: `Edite o usuário e preencha o campo de nova senha. O usuário poderá usar a nova senha no próximo acesso.`,
      },
    ],
  },
  {
    title: 'Configurações',
    icon: 'ki-outline ki-setting-2',
    roles: ['administrator'],
    items: [
      {
        question: 'O que posso configurar na tela de Configurações?',
        answer: `Em <strong>Configurações</strong> você pode personalizar as informações da sua oficina: <strong>nome fantasia, logotipo, e-mail, telefone e documento (CNPJ/CPF)</strong>. O logotipo aparece no menu lateral e nos documentos gerados pelo sistema. O painel lateral exibe as informações atuais da oficina (somente leitura) para conferência.`,
      },
      {
        question: 'Como atualizar o logotipo da oficina?',
        answer: `Em <strong>Configurações</strong>, clique na área de upload do logotipo e selecione o arquivo de imagem desejado. O logo ficará visível no menu lateral e nos PDFs gerados pelo sistema.`,
      },
    ],
  },
];

const visibleSections = computed(() => {
  return allSections.filter(section => {
    if (isAdministrator.value) return true;
    if (isReception.value) return section.roles.includes('reception');
    if (isMechanic.value) return section.roles.includes('mechanic');
    return false;
  });
});

const filteredSections = computed(() => {
  const q = search.value.toLowerCase().trim();
  if (!q) return visibleSections.value;

  return visibleSections.value
    .map(section => ({
      ...section,
      items: section.items.filter(
        item =>
          item.question.toLowerCase().includes(q) ||
          item.answer.toLowerCase().includes(q)
      ),
    }))
    .filter(section => section.items.length > 0);
});
</script>

<style scoped>
.faq-item {
  border: 1px solid var(--border, #e5e7eb);
  border-radius: 0.5rem;
  overflow: hidden;
  transition: border-color 0.15s;
}

.faq-item[open] {
  border-color: #f97316;
}

.faq-summary {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  cursor: pointer;
  list-style: none;
  user-select: none;
}

.faq-summary::-webkit-details-marker {
  display: none;
}

.faq-summary:hover {
  background-color: var(--muted, #f9fafb);
}

.dark .faq-summary:hover {
  background-color: rgba(255, 255, 255, 0.04);
}

.faq-icon-open { display: inline; }
.faq-icon-close { display: none; }

details[open] .faq-icon-open { display: none; }
details[open] .faq-icon-close { display: inline; }

.faq-answer {
  padding: 0 1rem 1rem 1rem;
  border-top: 1px solid var(--border, #e5e7eb);
  padding-top: 0.875rem;
}
</style>
