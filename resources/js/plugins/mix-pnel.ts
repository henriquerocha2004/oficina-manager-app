import mixpanel from "mixpanel-browser";

export default {
    install(app) {
        mixpanel.init(import.meta.env.VITE_MIX_PANEL_TOKEN, {
            debug: true
        });

        app.config.globalProperties.$track = (event: string, props = {}) => {
              mixpanel.track(event, props);
        };

        app.config.globalProperties.$identify = (userId: string) => {
              mixpanel.identify(userId);
        };

        app.config.globalProperties.$setTenant = (tenantId: string) => {
              mixpanel.set_group('tenant_id', tenantId);
        };
    }
}
