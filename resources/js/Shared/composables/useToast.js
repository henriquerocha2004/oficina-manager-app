export function useToast() {
    function show(opts = {}) {
        if (typeof window === 'undefined') return;
        if (window.KTToast && typeof window.KTToast.show === 'function') {
            window.KTToast.show(opts);
            return;
        }
        // Fallback: console
        console.warn('KTToast not available, fallback to console:', opts.message || opts);
    }

    function success(message, opts = {}) { show(Object.assign({ message, variant: 'success' }, opts)); }
    function error(message, opts = {}) { show(Object.assign({ message, variant: 'error', icon: '<i class="ki-filled ki-information-4 text-red-500 text-xl"></i>' }, opts)); }
    function info(message, opts = {}) { show(Object.assign({ message, variant: 'info' }, opts)); }

    return { show, success, error, info };
}

export default useToast;
