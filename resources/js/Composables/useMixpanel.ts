import mixpanel from "mixpanel-browser";

export function useMixpanel() {
    function track(event: string, props: Record<string, any> = {}) {
        mixpanel.track(event, props);
    }

    function identify(userId: string) {
        mixpanel.identify(userId);
    }

    function setTenant(tenantId: string) {
        mixpanel.set_group('tenant_id', tenantId);
    }

    return { track, identify, setTenant };
}
