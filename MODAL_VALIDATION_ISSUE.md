# Problema de Validação no Modal de Transição de OS

## Contexto

Estamos desenvolvendo um sistema de Ordens de Serviço (OS) com Kanban drag-and-drop. O modal de transição (`ServiceOrderTransitionModal.vue`) coleta diagnóstico e itens (serviços/peças) antes de mudar o status da OS.

## Problema Atual

**O botão "Confirmar" do modal não está reagindo corretamente quando o usuário preenche os campos dos itens.**

### Regra de Negócio

Se o usuário adicionar um item ao modal, ele DEVE preencher TODOS os campos:
- ✅ **Tipo** (service ou part)
- ✅ **Descrição** (nome do serviço/peça)
- ✅ **Quantidade** (deve ser > 0)
- ✅ **Preço unitário** (deve ser >= 0)

**O botão "Confirmar" SÓ pode ficar habilitado quando TODOS os itens em tela estiverem com TODOS os campos preenchidos corretamente.**

## Arquivos Relevantes

```
resources/js/Shared/Components/ServiceOrder/ServiceOrderTransitionModal.vue
```

## Estrutura do Componente

### Dados Reativos

```javascript
const formDiagnosis = ref('');
const formItems = ref([]); // Array de itens: [{ type, service_id, description, quantity, unit_price }]
const itemErrors = ref({}); // Erros por item: { 0: { description: true }, 1: { quantity: true } }
```

### Computed `isValid`

O botão "Confirmar" usa `:disabled="!isValid"`:

```javascript
const isValid = computed(() => {
  // Se precisa de diagnóstico, validar
  if (needsDiagnosis.value) {
    if (!formDiagnosis.value.trim()) return false;
  }
  
  // Se precisa de itens, validar TODOS os campos de TODOS os itens
  if (needsItems.value) {
    if (formItems.value.length === 0) return false;
    
    // CADA item deve ter TODOS os campos preenchidos
    for (let i = 0; i < formItems.value.length; i++) {
      const item = formItems.value[i];
      
      if (!item.type || !item.type.trim()) return false;
      if (!item.description || !item.description.trim()) return false;
      if (!item.quantity || item.quantity <= 0) return false;
      if (item.unit_price === undefined || item.unit_price === null || item.unit_price < 0) return false;
    }
  }
  
  return true;
});
```

## Comportamento Esperado

1. Modal abre → usuário clica "Adicionar Item"
2. Um item vazio é adicionado com valores padrão:
   ```javascript
   {
     type: 'service',
     service_id: null,
     description: '',
     quantity: 1,
     unit_price: 0
   }
   ```
3. **Botão "Confirmar" deve estar DESABILITADO** (descrição vazia)
4. Usuário digita descrição → botão continua desabilitado (pode ter outros campos inválidos)
5. Usuário preenche quantidade → botão continua desabilitado
6. Usuário preenche preço → **AGORA o botão deve HABILITAR**
7. Se adicionar outro item → botão desabilita até preencher o novo item também

## Problema Identificado

O computed `isValid` não está reagindo às mudanças nos campos dos itens. Possíveis causas:

1. **Reatividade não está triggando** quando `formItems.value[index].description` muda
2. **Vue não detecta mudanças profundas** no array `formItems`
3. **Template está usando v-model incorretamente** nos inputs

## Tentativas Anteriores

### ❌ Tentativa 1: Usar `reactive({})`
```javascript
const itemErrors = reactive({});
```
**Problema:** Reatividade inconsistente com objetos dinâmicos

### ❌ Tentativa 2: Usar `watch` com `deep: true`
```javascript
watch(formItems, () => {
  console.log('formItems changed');
}, { deep: true });
```
**Problema:** Watch executa mas computed `isValid` não re-executa

### ✅ Tentativa 3 (atual): Usar `ref({})` e `.value`
```javascript
const itemErrors = ref({});
const formItems = ref([]);
```
**Status:** Aplicado mas ainda não testado completamente

## Template Relevante

```vue
<template>
  <div v-for="(item, index) in formItems" :key="index">
    <select v-model="item.type">
      <option value="service">Serviço</option>
      <option value="part">Peça</option>
    </select>
    
    <input
      v-model="item.description"
      type="text"
      placeholder="Descrição"
    />
    
    <input
      v-model.number="item.quantity"
      type="number"
      min="1"
    />
    
    <input
      v-model.number="item.unit_price"
      type="number"
      step="0.01"
      min="0"
    />
  </div>
  
  <button :disabled="!isValid" @click="onConfirmClick">
    Confirmar
  </button>
</template>
```

## O Que Precisa Ser Feito

1. **Diagnosticar por que o computed `isValid` não está reagindo**
   - Verificar se Vue está detectando mudanças em `formItems.value[index].description`
   - Verificar se o problema é com deep reactivity

2. **Garantir que o botão "Confirmar" reaja IMEDIATAMENTE** quando:
   - Usuário preenche qualquer campo de qualquer item
   - Usuário adiciona novo item
   - Usuário remove um item

3. **Testar fluxo completo:**
   - Adicionar item → botão desabilitado
   - Preencher tipo → continua desabilitado
   - Preencher descrição → continua desabilitado
   - Preencher quantidade → continua desabilitado
   - Preencher preço → **botão HABILITA**
   - Adicionar segundo item → botão DESABILITA
   - Preencher segundo item → botão HABILITA novamente

## Logs de Debug Disponíveis

O código atual tem logs detalhados no `isValid`:

```javascript
console.log('=== isValid computed ===');
console.log('formItems:', formItems.value);
console.log(`Item ${i}:`, item);
console.log(`❌ Item ${i}: descrição vazia`);
```

Abra o console do navegador para ver exatamente onde a validação está falhando.

## Stack Tecnológica

- **Vue 3** (Composition API com `<script setup>`)
- **Vite** (build tool)
- **Tailwind CSS 4**
- **Laravel 12** (backend)

## Notas Importantes

- No template Vue, **NÃO usar `.value`** (Vue resolve automaticamente)
- No script, **SEMPRE usar `.value`** com `ref()`
- O modal usa `v-model` diretamente nos itens: `v-model="item.description"`
- Vue deveria detectar essas mudanças automaticamente, mas não está detectando

## Pergunta Principal

**Por que o computed `isValid` não está re-executando quando `formItems.value[index].description` muda através do `v-model`?**

Como garantir que a validação reaja em tempo real aos inputs do usuário?
