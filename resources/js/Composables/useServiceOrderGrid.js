import { ref } from 'vue';
import { fetchServiceOrders } from '@/services/serviceOrderService.js';

export function useServiceOrderGrid() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const page = ref(1);
    const perPage = ref(10);
    const total = ref(0);
    const search = ref('');
    const sortKey = ref(null);
    const sortDir = ref('asc');

    async function load(filters = {}) {
        loading.value = true;
        error.value = null;

        try {
            const result = await fetchServiceOrders({
                page: page.value,
                perPage: perPage.value,
                search: search.value,
                sortKey: sortKey.value,
                sortDir: sortDir.value,
                filters,
            });

            if (result) {
                items.value = result.items;
                total.value = result.total;
            }
        } catch (e) {
            error.value = e;
        } finally {
            loading.value = false;
        }
    }

    function setPage(p) {
        page.value = p;
    }

    function setPerPage(pp) {
        perPage.value = pp;
    }

    function setSearch(s) {
        search.value = s;
        page.value = 1;
    }

    function setSort(key, dir) {
        sortKey.value = key;
        sortDir.value = dir;
        page.value = 1;
    }

    function resetPagination() {
        page.value = 1;
    }

    return {
        items,
        loading,
        error,
        page,
        perPage,
        total,
        search,
        sortKey,
        sortDir,
        load,
        setPage,
        setPerPage,
        setSearch,
        setSort,
        resetPagination,
    };
}
