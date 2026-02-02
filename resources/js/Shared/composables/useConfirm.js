export function useConfirm() {
    async function confirm(options = {}) {
        if (typeof window === 'undefined') return Promise.reject(new Error('No window'));
        if (!window.__globalConfirm || typeof window.__globalConfirm.open !== 'function') {
            return Promise.reject(new Error('Global confirm not initialized'));
        }
        return window.__globalConfirm.open(options);
    }

    return { confirm };
}

export default useConfirm;
