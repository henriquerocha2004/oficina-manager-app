import { computed } from 'vue';

/**
 * Composable para estatísticas das telas de gerenciamento.
 * Agora suporta tanto dados da API quanto cálculo local (para backward compatibility).
 *
 * @param {Array} data - Array de dados da tela (para cálculo local)
 * @param {String} type - Tipo: 'products', 'clients', 'suppliers', 'services', 'vehicles'
 * @param {Object} statsFromApi - Stats vindas da API (opcional)
 * @returns {Object} { stats }
 */
export function useStats(data, type, statsFromApi = null) {
    const stats = computed(() => {
        if (statsFromApi?.value) {
            switch (type) {
                case 'clients':
                    return formatClientStats(statsFromApi.value);
                case 'suppliers':
                    return formatSupplierStats(statsFromApi.value);
                case 'products':
                    return formatProductStats(statsFromApi.value);
                case 'services':
                    return formatServiceStats(statsFromApi.value);
                case 'vehicles':
                    return formatVehicleStats(statsFromApi.value);
                default:
                    break;
            }
        }

        // Fallback para cálculo local (outras features ainda não migradas)
        if (!data.value || data.value.length === 0) {
            return getEmptyStats(type);
        }

        switch (type) {
            case 'products':
                return calculateProductStats(data.value);
            case 'clients':
                return calculateClientStats(data.value);
            case 'suppliers':
                return calculateSupplierStats(data.value);
            case 'services':
                return calculateServiceStats(data.value);
            case 'vehicles':
                return calculateVehicleStats(data.value);
            default:
                return [];
        }
    });

    return { stats };
}

/**
 * Formata estatísticas de clientes recebidas da API para o formato dos StatsCards.
 * @param {Object} stats - Dados de estatísticas vindos da API
 * @returns {Array} Array de objetos formatados para StatsCard
 */
export function formatClientStats(stats) {
    return [
        {
            icon: 'user',
            title: 'Total de Clientes',
            value: stats.total || 0,
            subtitle: stats.last_90_days > 0
                ? `${stats.last_90_days} novos nos últimos 90 dias`
                : 'Nenhum cliente novo nos últimos 90 dias',
            trend: stats.last_90_days > 0 ? `+${stats.last_90_days}` : '',
            color: 'orange',
        },
        {
            icon: 'calendar',
            title: 'Novos nos Últimos 90 Dias',
            value: stats.last_90_days || 0,
            subtitle: stats.growth > 0
                ? `+${stats.growth} comparado aos 90 dias anteriores`
                : `${stats.growth} comparado aos 90 dias anteriores`,
            trend: stats.growth_percentage > 0 ? `+${stats.growth_percentage}%` : '',
            color: 'green',
        },
        {
            icon: 'geolocation',
            title: 'Top Cidade',
            value: stats.top_city || 'N/A',
            subtitle: `${stats.top_city_percentage}% dos clientes`,
            trend: '',
            color: 'blue',
        },
        {
            icon: 'chart-simple',
            title: 'Crescimento',
            value: `${stats.growth || 0}`,
            subtitle: 'Últimos 90 dias vs anteriores',
            trend: stats.growth_percentage > 0 ? `+${stats.growth_percentage}%` : '',
            color: 'green',
        },
    ];
}

/**
 * Formata estatísticas de fornecedores recebidas da API para o formato dos StatsCards.
 * @param {Object} stats - Dados de estatísticas vindos da API
 * @returns {Array} Array de objetos formatados para StatsCard
 */
export function formatSupplierStats(stats) {
    return [
        {
            icon: 'shop',
            title: 'Total de Fornecedores',
            value: stats.total || 0,
            subtitle: stats.last_90_days > 0
                ? `${stats.last_90_days} novos nos últimos 90 dias`
                : 'Nenhum fornecedor novo nos últimos 90 dias',
            trend: stats.last_90_days > 0 ? `+${stats.last_90_days}` : '',
            color: 'orange',
        },
        {
            icon: 'check-circle',
            title: 'Fornecedores Ativos',
            value: stats.active_suppliers || 0,
            subtitle: `${stats.active_percentage}% do total`,
            trend: '',
            color: 'green',
        },
        {
            icon: 'geolocation',
            title: 'Top Estado',
            value: stats.top_state || 'N/A',
            subtitle: stats.top_state
                ? `${stats.top_state_percentage}% dos fornecedores`
                : 'Nenhum estado cadastrado',
            trend: '',
            color: 'blue',
        },
        {
            icon: 'calendar',
            title: 'Novos nos Últimos 90 Dias',
            value: stats.last_90_days || 0,
            subtitle: stats.growth > 0
                ? `+${stats.growth} comparado aos 90 dias anteriores`
                : `${stats.growth} comparado aos 90 dias anteriores`,
            trend: stats.growth_percentage > 0 ? `+${stats.growth_percentage}%` : '',
            color: 'green',
        },
    ];
}

/**
 * Formata estatísticas de produtos recebidas da API para o formato dos StatsCards.
 * @param {Object} stats - Dados de estatísticas vindos da API
 * @returns {Array} Array de objetos formatados para StatsCard
 */
export function formatProductStats(stats) {
    return [
        {
            icon: 'package',
            title: 'Total de Produtos',
            value: stats.total || 0,
            subtitle: stats.last_90_days > 0
                ? `${stats.last_90_days} novos nos últimos 90 dias`
                : 'Nenhum produto novo nos últimos 90 dias',
            trend: stats.last_90_days > 0 ? `+${stats.last_90_days}` : '',
            color: 'orange',
        },
        {
            icon: 'dollar',
            title: 'Valor Total em Estoque',
            value: `R$ ${parseFloat(stats.total_value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
            subtitle: 'Baseado no preço unitário',
            trend: '',
            color: 'green',
        },
        {
            icon: 'check-circle',
            title: 'Produtos Ativos',
            value: `${stats.active_percentage || 0}%`,
            subtitle: `${stats.active_products || 0} de ${stats.total || 0} produtos`,
            trend: '',
            color: 'green',
        },
        {
            icon: 'category',
            title: 'Top Categoria',
            value: stats.top_category || 'N/A',
            subtitle: stats.top_category
                ? `${stats.top_category_percentage}% dos produtos`
                : 'Nenhuma categoria cadastrada',
            trend: '',
            color: 'blue',
        },
    ];
}

/**
 * Formata estatísticas de serviços recebidas da API para o formato dos StatsCards.
 * @param {Object} stats - Dados de estatísticas vindos da API
 * @returns {Array} Array de objetos formatados para StatsCard
 */
export function formatServiceStats(stats) {
    return [
        {
            icon: 'wrench',
            title: 'Total de Serviços',
            value: stats.total || 0,
            subtitle: stats.last_90_days > 0
                ? `${stats.last_90_days} novos nos últimos 90 dias`
                : 'Nenhum serviço novo nos últimos 90 dias',
            trend: stats.last_90_days > 0 ? `+${stats.last_90_days}` : '',
            color: 'orange',
        },
        {
            icon: 'check-circle',
            title: 'Serviços Ativos',
            value: stats.active_services || 0,
            subtitle: `${Math.round((stats.active_services / (stats.total || 1)) * 100)}% do total`,
            trend: '',
            color: 'green',
        },
        {
            icon: 'dollar',
            title: 'Valor Médio',
            value: `R$ ${parseFloat(stats.average_price || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
            subtitle: 'Preço base médio dos serviços',
            trend: '',
            color: 'green',
        },
        {
            icon: 'calendar',
            title: 'Novos nos Últimos 90 Dias',
            value: stats.last_90_days || 0,
            subtitle: stats.growth > 0
                ? `+${stats.growth} comparado aos 90 dias anteriores`
                : `${stats.growth} comparado aos 90 dias anteriores`,
            trend: stats.growth_percentage > 0 ? `+${stats.growth_percentage}%` : '',
            color: 'blue',
        },
    ];
}

/**
 * Formata estatísticas de veículos recebidas da API para o formato dos StatsCards.
 * @param {Object} stats - Dados de estatísticas vindos da API
 * @returns {Array} Array de objetos formatados para StatsCard
 */
export function formatVehicleStats(stats) {
    return [
        {
            icon: 'car',
            title: 'Total de Veículos',
            value: stats.total || 0,
            subtitle: stats.last_90_days > 0
                ? `${stats.last_90_days} novos nos últimos 90 dias`
                : 'Nenhum veículo novo nos últimos 90 dias',
            trend: stats.last_90_days > 0 ? `+${stats.last_90_days}` : '',
            color: 'orange',
        },
        {
            icon: 'calendar',
            title: 'Novos nos Últimos 90 Dias',
            value: stats.last_90_days || 0,
            subtitle: stats.growth > 0
                ? `+${stats.growth} comparado aos 90 dias anteriores`
                : `${stats.growth} comparado aos 90 dias anteriores`,
            trend: stats.growth_percentage > 0 ? `+${stats.growth_percentage}%` : '',
            color: 'green',
        },
        {
            icon: 'award',
            title: 'Top Marca',
            value: stats.top_brand || 'N/A',
            subtitle: stats.top_brand
                ? `${stats.top_brand_percentage}% dos veículos`
                : 'Nenhuma marca cadastrada',
            trend: '',
            color: 'blue',
        },
        {
            icon: 'chart-simple',
            title: 'Crescimento',
            value: `${stats.growth || 0}`,
            subtitle: 'Últimos 90 dias vs anteriores',
            trend: stats.growth_percentage > 0 ? `+${stats.growth_percentage}%` : '',
            color: 'green',
        },
    ];
}


/**
 * Calcula estatísticas para Clientes.
 */
function calculateClientStats(clients) {
    const total = clients.length;

    // Mock: clientes criados este mês
    const thisMonth = clients.filter(c => {
        if (!c.created_at) return false;
        const created = new Date(c.created_at);
        const now = new Date();
        return created.getMonth() === now.getMonth() && created.getFullYear() === now.getFullYear();
    }).length;

    // Cidade mais comum
    const cities = clients.map(c => c.city).filter(Boolean);
    const cityCount = cities.reduce((acc, city) => {
        acc[city] = (acc[city] || 0) + 1;
        return acc;
    }, {});
    const topCity = Object.keys(cityCount).length > 0
        ? Object.keys(cityCount).reduce((a, b) => cityCount[a] > cityCount[b] ? a : b)
        : 'N/A';
    const topCityPercentage = Object.keys(cityCount).length > 0
        ? ((cityCount[topCity] / total) * 100).toFixed(0)
        : 0;

    // Mock: crescimento (comparação com mês anterior - simulado)
    const lastMonth = Math.floor(total * 0.85); // 85% do total como mock
    const growth = total - lastMonth;

    return [
        {
            icon: 'user',
            title: 'Total de Clientes',
            value: total,
            subtitle: thisMonth > 0 ? `${thisMonth} novos este mês` : 'Nenhum cliente novo este mês',
            trend: thisMonth > 0 ? `+${thisMonth}` : '',
            color: 'orange',
        },
        {
            icon: 'calendar',
            title: 'Novos no Mês',
            value: thisMonth,
            subtitle: growth > 0 ? `${growth} a mais que mês anterior` : 'Mesma quantidade do mês anterior',
            trend: growth > 0 ? `+${growth}` : '',
            color: 'green',
        },
        {
            icon: 'geolocation',
            title: 'Top Cidade',
            value: topCity,
            subtitle: `${topCityPercentage}% dos clientes`,
            trend: '',
            color: 'blue',
        },
        {
            icon: 'chart-simple',
            title: 'Crescimento',
            value: `${growth}`,
            subtitle: 'Comparado ao mês anterior',
            trend: growth > 0 ? `+${((growth / lastMonth) * 100).toFixed(1)}%` : '',
            color: 'green',
        },
    ];
}

/**
 * Calcula estatísticas para Fornecedores.
 */
function calculateSupplierStats(suppliers) {
    const total = suppliers.length;
    const active = suppliers.filter(s => s.is_active).length;

    // Mock: fornecedores criados este mês
    const thisMonth = suppliers.filter(s => {
        if (!s.created_at) return false;
        const created = new Date(s.created_at);
        const now = new Date();
        return created.getMonth() === now.getMonth() && created.getFullYear() === now.getFullYear();
    }).length;

    // Estado mais comum
    const states = suppliers.map(s => s.state).filter(Boolean);
    const stateCount = states.reduce((acc, state) => {
        acc[state] = (acc[state] || 0) + 1;
        return acc;
    }, {});
    const topState = Object.keys(stateCount).length > 0
        ? Object.keys(stateCount).reduce((a, b) => stateCount[a] > stateCount[b] ? a : b)
        : 'N/A';

    return [
        {
            icon: 'shop',
            title: 'Total de Fornecedores',
            value: total,
            subtitle: thisMonth > 0 ? `${thisMonth} novos este mês` : 'Nenhum fornecedor novo este mês',
            trend: thisMonth > 0 ? `+${thisMonth}` : '',
            color: 'orange',
        },
        {
            icon: 'check-circle',
            title: 'Fornecedores Ativos',
            value: active,
            subtitle: `${((active / total) * 100).toFixed(0)}% do total`,
            trend: '',
            color: 'green',
        },
        {
            icon: 'geolocation',
            title: 'Top Estado',
            value: topState,
            subtitle: 'Estado com mais fornecedores',
            trend: '',
            color: 'blue',
        },
        {
            icon: 'calendar',
            title: 'Novos no Mês',
            value: thisMonth,
            subtitle: 'Cadastrados este mês',
            trend: thisMonth > 0 ? `+${thisMonth}` : '',
            color: 'green',
        },
    ];
}

/**
 * Calcula estatísticas para Serviços.
 */
function calculateServiceStats(services) {
    const total = services.length;
    const active = services.filter(s => s.is_active).length;

    // Preço médio
    const avgPrice = services.reduce((sum, s) => sum + (parseFloat(s.base_price) || 0), 0) / total;

    // Categoria mais popular
    const categories = services.map(s => s.category).filter(Boolean);
    const categoryCount = categories.reduce((acc, cat) => {
        acc[cat] = (acc[cat] || 0) + 1;
        return acc;
    }, {});
    const topCategory = Object.keys(categoryCount).length > 0
        ? Object.keys(categoryCount).reduce((a, b) => categoryCount[a] > categoryCount[b] ? a : b)
        : 'N/A';

    return [
        {
            icon: 'wrench',
            title: 'Total de Serviços',
            value: total,
            subtitle: `${active} serviços ativos`,
            trend: '',
            color: 'orange',
        },
        {
            icon: 'dollar',
            title: 'Preço Médio',
            value: `R$ ${avgPrice.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`,
            subtitle: 'Por serviço',
            trend: '',
            color: 'green',
        },
        {
            icon: 'category',
            title: 'Categoria Popular',
            value: topCategory,
            subtitle: 'Mais oferecida',
            trend: '',
            color: 'blue',
        },
        {
            icon: 'check-circle',
            title: 'Taxa de Ativação',
            value: `${((active / total) * 100).toFixed(0)}%`,
            subtitle: `${active} de ${total} ativos`,
            trend: '',
            color: 'green',
        },
    ];
}

/**
 * Calcula estatísticas para Veículos.
 */
function calculateVehicleStats(vehicles) {
    const total = vehicles.length;
    const cars = vehicles.filter(v => v.type === 'car').length;
    const motos = vehicles.filter(v => v.type === 'moto').length;

    // Mock: veículos criados este mês
    const thisMonth = vehicles.filter(v => {
        if (!v.created_at) return false;
        const created = new Date(v.created_at);
        const now = new Date();
        return created.getMonth() === now.getMonth() && created.getFullYear() === now.getFullYear();
    }).length;

    // Marca mais popular
    const brands = vehicles.map(v => v.brand).filter(Boolean);
    const brandCount = brands.reduce((acc, brand) => {
        acc[brand] = (acc[brand] || 0) + 1;
        return acc;
    }, {});
    const topBrand = Object.keys(brandCount).length > 0
        ? Object.keys(brandCount).reduce((a, b) => brandCount[a] > brandCount[b] ? a : b)
        : 'N/A';

    return [
        {
            icon: 'car',
            title: 'Total de Veículos',
            value: total,
            subtitle: thisMonth > 0 ? `${thisMonth} novos este mês` : 'Nenhum veículo novo este mês',
            trend: thisMonth > 0 ? `+${thisMonth}` : '',
            color: 'orange',
        },
        {
            icon: 'chart-simple',
            title: 'Carros vs Motos',
            value: `${((cars / total) * 100).toFixed(0)}% / ${((motos / total) * 100).toFixed(0)}%`,
            subtitle: `${cars} carros, ${motos} motos`,
            trend: '',
            color: 'blue',
        },
        {
            icon: 'star',
            title: 'Marca Popular',
            value: topBrand,
            subtitle: 'Marca mais comum',
            trend: '',
            color: 'purple',
        },
        {
            icon: 'calendar',
            title: 'Novos no Mês',
            value: thisMonth,
            subtitle: 'Cadastrados este mês',
            trend: thisMonth > 0 ? `+${thisMonth}` : '',
            color: 'green',
        },
    ];
}

/**
 * Retorna stats vazias quando não há dados.
 */
function getEmptyStats(type) {
    const emptyCard = {
        icon: 'information',
        title: 'Sem dados',
        value: '0',
        subtitle: 'Nenhum registro encontrado',
        trend: '',
        color: 'blue',
    };

    return [emptyCard, emptyCard, emptyCard, emptyCard];
}
