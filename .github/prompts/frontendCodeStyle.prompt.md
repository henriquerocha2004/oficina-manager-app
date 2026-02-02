# Code Style Frontend – Oficina Manager

## Objetivo
Gerar código JavaScript/Vue.js para o frontend seguindo padrões profissionais, legibilidade e manutenibilidade.

## Regras Gerais
- Siga o padrão Vue.js 3 Composition API com `<script setup>`.
- Nomes de componentes, variáveis e funções em inglês, com significado claro.
- Use tipagem TypeScript-like em comentários JSDoc quando necessário.

### Docblocks
- Adicione docblocks para funções exportadas e métodos complexos.

**Exemplo:**
```javascript
/**
 * Fetches clients from the API.
 * @param {Object} params - Query parameters.
 * @returns {Promise<Object>} Paginated clients data.
 */
export async function fetchClients(params) {
    // ...
}
```

### Indentação e Sintaxe
- Indentação de 4 espaços.
- Use arrow functions, destructuring e template literals.
- Chaves sempre na linha de baixo para funções e blocos.

**Exemplo:**
```javascript
const fetchData = async () => {
    // ...
};
```

### Separação de Responsabilidades
- Use Composables, Services, Components conforme já praticado.

**Exemplo de Composable:**
```javascript
export function useToast() {
    // ...
}
```

**Exemplo de Service:**
```javascript
export async function createClient(data) {
    // ...
}
```

### Estado e Reatividade
- Use `ref()` para estado reativo.
- Evite manipulação direta de DOM; use Vue reactivity.

**Exemplo:**
```javascript
const items = ref([]);
```

### Tratamento de Erros e API
- Sempre use try-catch em services, retornando `{ success, data/error }`.
- Mostre toasts para feedback (sucesso/erro).

**Exemplo:**
```javascript
try {
    const { data } = await axios.post('/api/clients', payload);
    return { success: true, data };
} catch (error) {
    return { success: false, error };
}
```

### Componentes
- Template limpo, script com lógica, style scoped.
- Use props para entrada, emits para saída.

**Exemplo:**
```vue
<template>
    <div>{{ message }}</div>
</template>

<script setup>
const props = defineProps({ message: String });
</script>
```

### Testes
- Use Vitest + Vue Test Utils.
- Foque em lógica, mock services/composables.
- Padrão AAA (Arrange, Act, Assert).

**Exemplo:**
```javascript
it('should create client', async () => {
    // Arrange
    mockCreateClient.mockResolvedValue({ success: true });
    // Act
    await component.vm.onSubmit(data);
    // Assert
    expect(mockToast.success).toHaveBeenCalled();
});
```

### Funções Globais e Composables
- Evite globals; encapsule em composables.

### Consultas e Estado
- Para dados, use services para API.
- Cache local com composables se necessário.

### Organização de Pastas
- Siga a estrutura: Pages/, Shared/Components/, Composables/, services/, tests/.

### Validação
- Use Vee-Validate + Yup em formulários.

### Boas Práticas Específicas
- Sem if-else desnecessários: use early returns.
- Centralize API em services.
- Testes para lógica crítica.
- Use Tailwind + KTUI para estilos.
- Acessibilidade: ARIA attributes.

## Observações
- Sempre que possível, siga exemplos já presentes no projeto.
- Documente em docs/ se necessário.</content>
<parameter name="filePath">/home/hsouza/Projetos/oficina-manager/.github/prompts/frontendCodeStyle.prompt.md