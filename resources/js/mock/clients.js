// Mock clients data and simple query API for front-end prototyping
const NAMES = [
    'Ana Silva', 'Bruno Costa', 'Carlos Pereira', 'Daniela Souza', 'Eduardo Lima', 'Fernanda Rocha', 'Gustavo Alves', 'Helena Martins', 'Igor Oliveira', 'Joana Melo', 'Karla Pinto', 'Lucas Dias', 'Marina Ramos', 'Neto Santos', 'Olivia Ferreira', 'Paulo Ribeiro', 'Quênia Gomes', 'Rafaela Teixeira', 'Samuel Nunes', 'Tatiana Cardoso', 'Ulisses Barros', 'Vanessa Castro', 'Wagner Freitas', 'Xavier Moura', 'Yasmin Nery', 'Zeca Alves'
];
const CITIES = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre', 'Salvador', 'Fortaleza', 'Recife'];
const STATES = ['SP', 'RJ', 'MG', 'PR', 'RS', 'BA', 'CE', 'PE'];

const DATA = NAMES.map((n, i) => ({
    id: (i + 1).toString(),
    name: n,
    email: n.toLowerCase().replace(/\s+/g, '.') + '@exemplo.com',
    city: CITIES[i % CITIES.length],
    state: STATES[i % STATES.length]
}));

export function fetchClientsMock({ page = 1, perPage = 10, search = '', sortKey = null, sortDir = 'asc' } = {}) {
    return new Promise((resolve) => {
        setTimeout(() => {
            let items = DATA.slice();

            if (search && search.trim()) {
                const q = search.trim().toLowerCase();
                items = items.filter(i => i.name.toLowerCase().includes(q) || i.email.toLowerCase().includes(q) || (i.city || '').toLowerCase().includes(q));
            }

            if (sortKey) {
                items.sort((a, b) => {
                    const A = (a[sortKey] || '').toString().toLowerCase();
                    const B = (b[sortKey] || '').toString().toLowerCase();
                    if (A < B) return sortDir === 'asc' ? -1 : 1;
                    if (A > B) return sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            const total = items.length;
            const start = (page - 1) * perPage;
            const end = start + perPage;
            const pageItems = items.slice(start, end);

            resolve({ items: pageItems, total, page, perPage });
        }, 200);
    });
}

export function getAllMockClients() { return DATA.slice(); }
