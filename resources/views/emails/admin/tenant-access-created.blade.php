<x-mail::message>
# Acesso liberado

Olá, **{{ $tenantName }}**.

Seu ambiente no **{{ $appName }}** foi criado com sucesso. Abaixo estão os dados para o primeiro acesso:

<x-mail::panel>
**URL de acesso**  
{{ $loginUrl }}

**Subdomínio**  
{{ $tenantDomain }}

**Email**  
{{ $tenantEmail }}

**Senha provisória**  
{{ $password }}
</x-mail::panel>

@if ($isTrial && $trialUntil)
<x-mail::panel>
**Trial ativo até:** {{ $trialUntil }}
</x-mail::panel>
@endif

<x-mail::button :url="$loginUrl" color="primary">
Acessar sistema
</x-mail::button>

Recomendamos alterar a senha no primeiro login para manter a conta segura.

Se você não esperava este cadastro, responda este email para que a equipe possa verificar.

Atenciosamente,  
**Equipe {{ $appName }}**
</x-mail::message>
