# Code Style Backend – Oficina Manager

## Objetivo
Gerar código PHP para Laravel seguindo padrões profissionais, legibilidade e manutenibilidade.

## Regras Gerais
- Siga o padrão PSR-12.
- Nomes de classes, métodos e variáveis em inglês, com significado claro.
- Use tipagem explícita em métodos e propriedades.

### Docblocks
- Só adicione docblocks para documentar uma exception. Já estamos tipando as coisas, não precisa documentar os tipos.

**Exemplo:**
```php
/**
 * Throws CustomDomainException if business rule fails.
 */
public function handle(): void
{
    // ...
}
```

### Indentação e Chaves
- Indentação de 4 espaços, sem tabs.
- Chaves de classes e métodos sempre na linha de baixo.

**Exemplo:**
```php
class ExampleService
{
    public function doSomething(): void
    {
        // ...
    }
}
```

### Separação de Responsabilidades
- Use Actions, Services, Dtos, Traits conforme já praticado.

**Exemplo de Action (CRUD simples):**
```php
class CreateUserAction
{
    public function __invoke(UserDto $dto): User
    {
        // ...
    }
}
```

**Exemplo de Domain Model (tarefa complexa):**
```php
class SubscriptionDomain
{
    public function activate(Subscription $subscription): void
    {
        // ...
    }
}
```

### Injeção de Dependência
- Use injeção de dependência via construtor apenas se não for uma classe Action.
- No caso das Classes Action, injete elas no método que vai chamá-las.

**Exemplo Service:**
```php
class UserService
{
    public function __construct(private UserRepository $repo) {}
}
```

**Exemplo Action:**
```php
public function store(CreateUserAction $action)
{
    $action($dto);
}
```

### Funções Globais
- Se achar que deve usar funções globais, me pergunte antes de criá-las.

### Tratamento de Exceções e Logs
- Sempre trate exceções de forma clara e registre logs relevantes.

**Exemplo:**
```php
try {
    // ...
} catch (DomainException $e) {
    Log::error('Domain error', ['error' => $e->getMessage()]);
    throw;
}
```

### Consultas
- Para consultas envolvendo apenas uma tabela e se for uma classe Action, use a trait QueryBuilderTrait.
- Para consultas mais complexas (join, etc.), use Models + QueryBuilder.

**Exemplo simples (Action + Trait):**
```php
class ListUsersAction
{
    use QueryBuilderTrait;

    public function __invoke(): Collection
    {
        return $this->query(User::query())->get();
    }
}
```

**Exemplo complexo:**
```php
$users = User::query()
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->where('profiles.active', true)
    ->get();
```

### Organização de Pastas
- Siga o padrão de organização de pastas já existente (Actions, Dto, Services, etc).

### Controllers e Validação
- Ao criar uma nova função em um controller, use FormRequest. Nunca faça validação no controller.

**Exemplo:**
```php
public function store(StoreUserRequest $request)
{
    // $request já validado
}
```

## Boas Práticas Específicas
- Use exceptions customizadas para regras de negócio.
- Sempre use migrations para alterações de banco.
- Prefira Requests para validação de dados em controllers.
- Use Dtos para transferir dados entre camadas.
- Adote testes unitários e de feature para novas funcionalidades.
- Nome dos testes devem ser em camelCase.
- Quando for criar os testes, siga o padrão AAA (Arrange, Act, Assert).
- Siga os principios SOLID no design do código.
- Siga os principios FIRST nos testes automatizados.
- Use namespaces conforme a estrutura de pastas do projeto. Nos testes também.
- Dentro dos métodos dos controllers, use try/catch para capturar exceptions e retornar respostas HTTP adequadas.
- Siga os principios do object calisthenics.

**Exemplo de teste:**
```php
public function testUserCreation(): void
{
    // ...
}
```

## Observações
- Sempre que possível, siga exemplos já presentes no projeto.
- Documente métodos e classes relevantes na pasta docs.

### Comentários
- Não precisa comentar em cada linha do código gerado.
- Escreva código autoexplicativo, com nomes claros e boas práticas.
- Só use comentários se for algo realmente complexo que precise de explicação extra.