<template>
  <TenantLayout title="Movimentações de Estoque" :breadcrumbs="breadcrumbs">
    <div class="kt-container-fixed w-full py-4 px-2">
      <StatsContainer>
        <StatsCard
          v-for="(stat, index) in stats"
          :key="index"
          :icon="stat.icon"
          :title="stat.title"
          :value="stat.value"
          :subtitle="stat.subtitle"
          :trend="stat.trend"
          :color="stat.color"
        />
      </StatsContainer>

      <DataGrid
        :columns="columns"
        :items="items"
        :total="total"
        :page="page"
        :perPage="perPage"
        @update:page="onPage"
        @sort="onSort"
        @search="onSearch"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <ExportButton @export="handleExport" />
          </div>
        </template>

        <template #filters>
          <FilterDropdown
            :activeCount="activeFiltersCount"
            @clear="onClearFilters"
          >
            <div class="filter-item relative">
              <label class="filter-label">
                Produto
              </label>
              <input
                v-model="productSearch"
                type="text"
                class="kt-input"
                placeholder="Buscar produto..."
                @input="onProductSearchInput"
                @focus="showProductDropdown = true"
                @blur="onProductBlur"
              />
              <div
                v-if="showProductDropdown && (loadingProducts || filteredProducts.length > 0)"
                class="absolute top-full z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto"
              >
                <template v-if="loadingProducts">
                  <div
                    v-for="i in 3"
                    :key="'skeleton-' + i"
                    class="px-4 py-3 animate-pulse"
                  >
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                  </div>
                </template>
                <template v-else>
                  <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                    @mousedown.prevent="selectProduct(product)"
                  >
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ product.name }}</div>
                  </div>
                </template>
              </div>
            </div>

            <div class="filter-item">
              <label class="filter-label">
                Tipo de Movimento
              </label>
              <select
                v-model="filters.movement_type"
                class="kt-select"
              >
                <option value="">Todos</option>
                <option value="IN">Entrada</option>
                <option value="OUT">Saída</option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">
                Motivo
              </label>
              <select
                v-model="filters.reason"
                class="kt-select"
              >
                <option value="">Todos</option>
                <option value="purchase">Compra</option>
                <option value="sale">Venda</option>
                <option value="adjustment">Ajuste</option>
                <option value="loss">Perda</option>
                <option value="return">Devolução</option>
                <option value="transfer">Transferência</option>
                <option value="initial">Estoque Inicial</option>
                <option value="other">Outro</option>
              </select>
            </div>

            <div class="filter-item">
              <label class="filter-label">
                Período
              </label>
              <DateRangePicker
                v-model="dateRange"
              />
            </div>
          </FilterDropdown>
        </template>

        <template #cell-product="{ row }">
          <span>{{ row.product?.name || '-' }}</span>
        </template>

        <template #cell-movement_type="{ row }">
          <span
            :class="{
              'px-2 py-1 rounded text-xs font-medium': true,
              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': row.movement_type === 'IN',
              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': row.movement_type === 'OUT',
            }"
          >
            {{ row.movement_type === 'IN' ? 'Entrada' : 'Saída' }}
          </span>
        </template>

        <template #cell-type="{ row }">
          <span>{{ row.type || '-' }}</span>
        </template>

        <template #cell-quantity="{ row }">
          <span>{{ row.quantity }}</span>
        </template>

        <template #cell-balance_after="{ row }">
          <span>{{ row.balance_after }}</span>
        </template>

        <template #cell-reason="{ row }">
          <span>{{ translateReason(row.reason) }}</span>
        </template>

        <template #cell-user="{ row }">
          <span>{{ row.user?.name || '-' }}</span>
        </template>

        <template #cell-created_at="{ row }">
          <span>{{ formatDate(row.created_at) }}</span>
        </template>
      </DataGrid>
    </div>
  </TenantLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import DataGrid from '../../../Shared/Components/DataGrid.vue';
import TenantLayout from '@/Layouts/TenantLayout.vue';
import StatsContainer from '@/Shared/Components/StatsContainer.vue';
import StatsCard from '@/Shared/Components/StatsCard.vue';
import ExportButton from '@/Shared/Components/ExportButton.vue';
import FilterDropdown from '@/Shared/Components/FilterDropdown.vue';
import DateRangePicker from '@/Shared/Components/DateRangePicker.vue';
import { fetchStockMovements, searchProducts, fetchStockMovementStats } from '@/services/stockMovementService.js';
import { useToast } from '@/Shared/composables/useToast.js';
import { useStockMovementFilters } from '@/Composables/useStockMovementFilters.js';
import { useExportCSV } from '@/Composables/useExportCSV.js';

const toast = useToast();
const { exportToCSV } = useExportCSV();

const page = ref(1);
const perPage = ref(10);
const total = ref(0);
const items = ref([]);
const search = ref('');
const sortKey = ref('created_at');
const sortDir = ref('desc');
const loading = ref(false);
const statsFromApi = ref(null);

// Filtros
const { filters, saveToStorage, loadFromStorage, clearFilters, activeFiltersCount } = useStockMovementFilters();
const productSearch = ref('');
const dateRange = ref({ from: '', to: '' });

const showProductDropdown = ref(false);
const filteredProducts = ref([]);
const loadingProducts = ref(false);
const debounceTimer = ref(null);

const breadcrumbs = [{ label: 'Movimentações de Estoque' }];

const reasonTranslations = {
    purchase: 'Compra',
    sale: 'Venda',
    adjustment: 'Ajuste',
    loss: 'Perda',
    return: 'Devolução',
    transfer: 'Transferência',
    initial: 'Estoque Inicial',
    other: 'Outro',
};

const stats = computed(() => {
    if (!statsFromApi.value) {
        return [
            { icon: 'chart-line', title: 'Total de Movimentações', value: '0', subtitle: 'Este mês', trend: '', color: 'orange' },
            { icon: 'arrow-down', title: 'Total de Entradas', value: '0', subtitle: 'Este mês', trend: '', color: 'green' },
            { icon: 'arrow-up', title: 'Total de Saídas', value: '0', subtitle: 'Este mês', trend: '', color: 'red' },
            { icon: 'category', title: 'Motivo Mais Comum', value: '-', subtitle: 'Este mês', trend: '', color: 'blue' },
        ];
    }

    const formatTrend = (growth) => {
        if (!growth || growth === 0) return '';
        return growth > 0 ? `+${growth}%` : `${growth}%`;
    };

    return [
        {
            icon: 'chart-line',
            title: 'Total de Movimentações',
            value: statsFromApi.value.total_movements.current.toString(),
            subtitle: `${statsFromApi.value.total_movements.previous} no mês anterior`,
            trend: formatTrend(statsFromApi.value.total_movements.growth),
            color: 'orange'
        },
        {
            icon: 'arrow-down',
            title: 'Total de Entradas',
            value: statsFromApi.value.total_in.current.toString(),
            subtitle: `${statsFromApi.value.total_in.previous} no mês anterior`,
            trend: formatTrend(statsFromApi.value.total_in.growth),
            color: 'green'
        },
        {
            icon: 'arrow-up',
            title: 'Total de Saídas',
            value: statsFromApi.value.total_out.current.toString(),
            subtitle: `${statsFromApi.value.total_out.previous} no mês anterior`,
            trend: formatTrend(statsFromApi.value.total_out.growth),
            color: 'red'
        },
        {
            icon: 'category',
            title: 'Motivo Mais Comum',
            value: translateReason(statsFromApi.value.most_common_reason),
            subtitle: 'Este mês',
            trend: '',
            color: 'blue'
        },
    ];
});

function translateReason(reason) {
    return reasonTranslations[reason] || reason;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

const load = async () => {
    loading.value = true;
    try {
        const res = await fetchStockMovements({
            page: page.value,
            perPage: perPage.value,
            search: search.value,
            sortKey: sortKey.value,
            sortDir: sortDir.value,
            filters: {
                product_id: filters.product_id || undefined,
                movement_type: filters.movement_type || undefined,
                reason: filters.reason || undefined,
                date_from: filters.date_from || undefined,
                date_to: filters.date_to || undefined,
            }
        });
        items.value = res.items;
        total.value = res.total;
    } catch (error) {
        toast.error('Erro ao carregar movimentações: ' + (error.response?.data?.message || error.message));
    } finally {
        loading.value = false;
    }
};

const loadStats = async () => {
    const result = await fetchStockMovementStats();
    if (result.success) {
        statsFromApi.value = result.data;
    }
};

async function onProductSearchInput() {
    if (debounceTimer.value) {
        clearTimeout(debounceTimer.value);
    }

    debounceTimer.value = setTimeout(async () => {
        if (productSearch.value.length < 2) {
            filteredProducts.value = [];
            return;
        }

        loadingProducts.value = true;
        try {
            const result = await searchProducts(productSearch.value);
            if (result.success) {
                filteredProducts.value = result.data;
                showProductDropdown.value = true;
            }
        } catch (error) {
            console.error('Erro ao buscar produtos:', error);
            filteredProducts.value = [];
        } finally {
            loadingProducts.value = false;
        }
    }, 300);
}

function selectProduct(product) {
    filters.product_id = product.id;
    filters.productName = product.name;
    productSearch.value = product.name;
    showProductDropdown.value = false;
}

function onProductBlur() {
    setTimeout(() => {
        showProductDropdown.value = false;
    }, 200);
}

function onClearFilters() {
    clearFilters();
    productSearch.value = '';
    dateRange.value = { from: '', to: '' };
    page.value = 1;
    load();
}

function handleExport() {
    const columns = [
        { key: 'product_name', label: 'Produto' },
        { key: 'movement_type', label: 'Tipo de Movimento' },
        { key: 'type', label: 'Tipo' },
        { key: 'quantity', label: 'Quantidade' },
        { key: 'balance_after', label: 'Saldo Após' },
        { key: 'reason', label: 'Motivo' },
        { key: 'user_name', label: 'Usuário' },
        { key: 'created_at', label: 'Data' },
    ];

    const dataToExport = items.value.map(item => ({
        ...item,
        product_name: item.product?.name || '-',
        movement_type: item.movement_type === 'IN' ? 'Entrada' : 'Saída',
        type: item.type || '-',
        reason: translateReason(item.reason),
        user_name: item.user?.name || '-',
        created_at: formatDate(item.created_at),
    }));

    exportToCSV(dataToExport, columns, 'movimentacoes-estoque');
}

watch(
    [() => filters.product_id, () => filters.movement_type, () => filters.reason, () => filters.date_from, () => filters.date_to],
    () => {
        saveToStorage();
        page.value = 1;
        load();
    }
);

watch(
    dateRange,
    (newRange) => {
        filters.date_from = newRange.from;
        filters.date_to = newRange.to;
    },
    { deep: true }
);

onMounted(() => {
    Object.assign(filters, loadFromStorage());
    if (filters.productName) {
        productSearch.value = filters.productName;
    }
    if (filters.date_from || filters.date_to) {
        dateRange.value = {
            from: filters.date_from || '',
            to: filters.date_to || '',
        };
    }
    load();
    loadStats();
});

function onPage(p) {
    page.value = p;
    load();
}

function onSearch(q) {
    search.value = q;
    page.value = 1;
    load();
}

function onSort({ key, dir }) {
    sortKey.value = key;
    sortDir.value = dir;
    page.value = 1;
    load();
}

const columns = [
    { key: 'product', label: 'Produto', sortable: false },
    { key: 'movement_type', label: 'Tipo de Movimento', sortable: true },
    { key: 'type', label: 'Tipo', sortable: false },
    { key: 'quantity', label: 'Quantidade', sortable: true },
    { key: 'balance_after', label: 'Saldo Após', sortable: false },
    { key: 'reason', label: 'Motivo', sortable: true },
    { key: 'user', label: 'Usuário', sortable: false },
    { key: 'created_at', label: 'Data', sortable: true },
];
</script>

<style scoped>
.filter-item {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    position: relative;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.dark .filter-label {
    color: #cbd5e1;
}
</style>
