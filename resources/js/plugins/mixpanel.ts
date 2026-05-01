import mixpanel from "mixpanel-browser";
import { router } from "@inertiajs/vue3";

function setup(props: Record<string, any>) {
    const user = props.auth?.user;
    const tenantId: string | undefined = props.tenant_settings?.tenant_id != null
        ? String(props.tenant_settings.tenant_id)
        : undefined;
    const tenantDomain: string | undefined = props.tenant_settings?.tenant_domain ?? undefined;

    if (!user) return;

    const userId = user.ulid ?? String(user.id);

    mixpanel.identify(userId);

    // Super properties: anexadas automaticamente em TODOS os eventos futuros
    // É assim que o painel consegue filtrar por tenant
    mixpanel.register({
        tenant_id: tenantId ?? null,
        tenant_domain: tenantDomain ?? null,
        user_role: user.role,
    });

    mixpanel.people.set({
        $name: user.name,
        $email: user.email,
        role: user.role,
        tenant_id: tenantId ?? null,
        tenant_domain: tenantDomain ?? null,
    });

    if (tenantId) {
        mixpanel.set_group('tenant_id', tenantId);
    }
}

export default {
    install(_app: any) {
        mixpanel.init(import.meta.env.VITE_MIX_PANEL_TOKEN, {
            debug: import.meta.env.DEV,
        });

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
