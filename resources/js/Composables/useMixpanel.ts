import mixpanel from "mixpanel-browser";

function safeMixpanelCall(fn: () => void) {
    try {
        fn();
    } catch (error) {
        console.warn("Mixpanel call skipped", error);
    }
}

export function useMixpanel() {
    function track(event: string, props: Record<string, any> = {}) {
        safeMixpanelCall(() => mixpanel.track(event, props));
    }

    function identify(userId: string) {
        safeMixpanelCall(() => mixpanel.identify(userId));
    }

    function setTenant(tenantId: string) {
        safeMixpanelCall(() => mixpanel.set_group('tenant_id', tenantId));
    }

    return { track, identify, setTenant };
}
