import mixpanel from "mixpanel-browser";
import { router } from "@inertiajs/vue3";

const mixpanelToken = import.meta.env.VITE_MIX_PANEL_TOKEN;
let isMixpanelEnabled = false;

function withMixpanel(fn: () => void) {
    if (!isMixpanelEnabled) {
        return;
    }

    try {
        fn();
    } catch (error) {
        if (import.meta.env.DEV) {
            console.warn("Mixpanel call failed", error);
        }
    }
}

function setup(props: Record<string, any>) {
    const user = props.auth?.user;
    const tenantId: string | undefined = props.tenant_settings?.tenant_id != null
        ? String(props.tenant_settings.tenant_id)
        : undefined;
    const tenantDomain: string | undefined = props.tenant_settings?.tenant_domain ?? undefined;

    if (!user) return;

    const userId = user.ulid ?? String(user.id);

    withMixpanel(() => mixpanel.identify(userId));

    // Super properties: anexadas automaticamente em TODOS os eventos futuros
    // É assim que o painel consegue filtrar por tenant
    withMixpanel(() => {
        mixpanel.register({
            tenant_id: tenantId ?? null,
            tenant_domain: tenantDomain ?? null,
            user_role: user.role,
        });
    });

    withMixpanel(() => {
        mixpanel.people.set({
            $name: user.name,
            $email: user.email,
            role: user.role,
            tenant_id: tenantId ?? null,
            tenant_domain: tenantDomain ?? null,
        });
    });

    if (tenantId) {
        withMixpanel(() => mixpanel.set_group('tenant_id', tenantId));
    }
}

export default {
    install(_app: any) {
        if (!mixpanelToken) {
            if (import.meta.env.DEV) {
                console.warn("Mixpanel disabled: missing VITE_MIX_PANEL_TOKEN");
            }
            return;
        }

        try {
            mixpanel.init(mixpanelToken, {
                debug: import.meta.env.DEV,
            });
            isMixpanelEnabled = true;
        } catch (error) {
            if (import.meta.env.DEV) {
                console.warn("Mixpanel init failed", error);
            }
            return;
        }

        // Lê os props iniciais direto do atributo data-page que o Inertia injeta no HTML.
        // Necessário porque o evento 'navigate' dispara antes deste listener ser registrado
        // (acontece durante app.use(plugin), que vem antes de app.use(MixPanel)).
        const el = document.getElementById('app');
        if (el?.dataset.page) {
            try {
                setup(JSON.parse(el.dataset.page).props);
            } catch {
                // silencia erros de parse no boot
            }
        }

        // Mantém identify/super-properties atualizados em cada navegação SPA
        router.on('navigate', (event: any) => {
            setup(event.detail.page.props);
        });
    },
};
