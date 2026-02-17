/**
 * Composable para exportar dados em formato CSV.
 * Implementação manual sem bibliotecas externas.
 */
export function useExportCSV() {
    /**
     * Exporta dados para CSV.
     *
     * @param {Array} data - Array de objetos com os dados
     * @param {Array} columns - Array de objetos { key: 'campo', label: 'Rótulo' }
     * @param {String} filename - Nome do arquivo (sem extensão)
     */
    const exportToCSV = (data, columns, filename = 'export') => {
        if (!data || data.length === 0) {
            console.warn('Nenhum dado para exportar');
            return;
        }

        // Gera cabeçalhos
        const headers = columns.map(col => col.label).join(';');

        // Gera linhas de dados
        const rows = data.map(item => {
            return columns.map(col => {
                let value = item[col.key];

                // Trata valores nulos/undefined
                if (value === null || value === undefined) {
                    return '';
                }

                // Trata booleanos
                if (typeof value === 'boolean') {
                    return value ? 'Sim' : 'Não';
                }

                // Trata strings com caracteres especiais
                if (typeof value === 'string') {
                    // Remove quebras de linha
                    value = value.replace(/\r?\n|\r/g, ' ');
                    // Escapa aspas duplas
                    value = value.replace(/"/g, '""');
                    // Envolve em aspas se contém ponto e vírgula ou aspas
                    if (value.includes(';') || value.includes('"')) {
                        value = `"${value}"`;
                    }
                }

                return value;
            }).join(';');
        });

        // Combina cabeçalhos e linhas
        const csv = [headers, ...rows].join('\n');

        // Adiciona BOM para garantir UTF-8 no Excel
        const bom = '\uFEFF';
        const csvWithBom = bom + csv;

        // Cria blob e faz download
        const blob = new Blob([csvWithBom], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        // Gera nome do arquivo com data
        const date = new Date().toISOString().split('T')[0];
        const fullFilename = `${filename}-${date}.csv`;

        link.setAttribute('href', url);
        link.setAttribute('download', fullFilename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Libera memória
        URL.revokeObjectURL(url);
    };

    return { exportToCSV };
}
