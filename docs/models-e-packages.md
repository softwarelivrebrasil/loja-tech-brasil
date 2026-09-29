# Onde uma Model mora, e como os apps a usam

Toda Model de negócio vive dentro de `packages/*` — nunca dentro de `apps/backend/app/Models/` ou `apps/admin/app/Models/` como a Model "de verdade" (colunas, casts, factory, migration). Mas **como** cada app consome essa Model depende do que o app precisa fazer com ela. Tem dois padrões diferentes no código hoje — vale saber diferenciar antes de criar uma Model nova.

## Padrão 1 — o app só consome a Model do pacote direto (o caso comum)

Se o app não precisa colar nada específico dele na Model (nenhum contrato do Filament, nenhuma trait que só faz sentido num dos apps), ele importa a classe do pacote e usa direto. É o caso de `Withdrawal`:

```php
// packages/withdrawals/src/Models/Withdrawal.php
namespace Loja\Withdrawals\Models;

class Withdrawal extends Model
{
    use HasUuids;
    protected $table = 'withdrawals';
    // ...
}
```

```php
// apps/admin/app/Filament/Admin/Resources/Withdrawals/WithdrawalResource.php
use Loja\Withdrawals\Models\Withdrawal;

class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;
    // ...
}
```

Não existe `apps/admin/app/Models/Withdrawal.php`. Não precisa — o Filament Resource referencia a classe do pacote diretamente, e se `apps/backend` um dia precisar ler/escrever `Withdrawal`, faz exatamente a mesma coisa: `use Loja\Withdrawals\Models\Withdrawal;`.

**Esse é o padrão padrão.** Ao criar um módulo novo (`comunidades`, `produtos`, etc.), a Model normalmente só existe dentro do pacote, sem subclasse em nenhum app.

## Padrão 2 — o app precisa de uma subclasse local (User e Admin)

`User` e `Admin` são diferentes: `apps/admin` precisa que a Model implemente contratos do Filament (`FilamentUser`, `HasAvatar`) pra o painel funcionar (login, avatar, controle de acesso por painel). Mas `packages/identity` **não pode depender do Filament** — se dependesse, `apps/backend` quebraria ao rodar `composer require loja/identity`, porque ele nem tem o Filament instalado.

A solução: a Model "de verdade" (colunas, casts, `Notifiable`, factory, hash de senha) fica no pacote, sem saber que Filament existe. Cada app tem sua própria subclasse fina que adiciona só o que é específico dele:

```php
// packages/identity/src/Models/User.php — dona da tabela, sem Filament
namespace Loja\Identity\Models;

class User extends Model implements AuthenticatableContract, AuthorizableContract, ...
{
    use Authenticatable, Authorizable, CanResetPassword, HasFactory, HasUuids, MustVerifyEmail, Notifiable;

    protected $table = 'users';
    protected $fillable = ['status', 'name', 'email', 'password', ...];
    // casts, factory, etc.
}
```

```php
// apps/backend/app/Models/User.php — quase vazio, é só o ponto de extensão
namespace App\Models;

use Loja\Identity\Models\User as BaseUser;

class User extends BaseUser
{
    //
}
```

```php
// apps/admin/app/Models/User.php — aqui sim entra o que é específico do Filament
namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Loja\Identity\Models\User as BaseUser;

class User extends BaseUser implements FilamentUser, HasAvatar
{
    public function canAccessPanel(Panel $panel): bool
    {
        return PanelAccess::check($this, $panel);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // ...
    }
}
```

`Admin` segue exatamente a mesma forma (`packages/identity/src/Models/Admin.php` + `apps/admin/app/Models/Admin.php`).

### Por que isso funciona pra apontar pra mesma linha da tabela

As duas subclasses (`apps/backend`'s `User` e `apps/admin`'s `User`) herdam `protected $table = 'users'` do pacote — nenhuma delas redeclara isso. Então não importa qual classe você usa pra ler/escrever: é sempre a mesma tabela `users`, no mesmo Postgres (ver [`banco-de-dados.md`](banco-de-dados.md)). A subclasse muda o **comportamento em PHP** (quais interfaces a classe implementa, quais métodos ela tem), nunca o **schema**.

### As duas coisas que amarram a subclasse certa no lugar certo

1. **`config/auth.php`** de cada app aponta o guard pra a classe local, não pra do pacote:

   ```php
   // apps/admin/config/auth.php
   'providers' => [
       'users' => ['driver' => 'eloquent', 'model' => App\Models\User::class],
       'admins' => ['driver' => 'eloquent', 'model' => App\Models\Admin::class],
   ],
   ```

   É isso que faz `Auth::user()` no `apps/admin` devolver uma instância que já implementa `FilamentUser` — se o guard apontasse pra `Loja\Identity\Models\User` direto, o Filament não conseguiria usar ela pra controlar acesso ao painel.

2. **A factory resolve a classe certa em tempo de execução**, não sempre a classe base do pacote:

   ```php
   // packages/identity/database/factories/UserFactory.php
   public function modelName(): string
   {
       return config('auth.providers.users.model') ?: User::class;
   }
   ```

   Então `User::factory()->create()` num teste do `apps/admin` cria um `App\Models\User` (com os contratos do Filament); o mesmo `User::factory()->create()` num teste do `apps/backend` cria a versão vazia dele. Mesma factory, mesma tabela, classe diferente — sem duplicar nada.

## Regra prática pra decidir

Ao criar ou usar uma Model de um `packages/*`:

- **App só lê/escreve normalmente, sem precisar de nada específico dele?** → importa a Model do pacote direto (Padrão 1, como `Withdrawal`).
- **App precisa colar um contrato/trait que só faz sentido nele** (Filament, Sanctum, alguma integração que só um dos apps usa) **e o pacote não pode depender disso?** → cria a subclasse fina só nesse app (Padrão 2, como `User`/`Admin`), e configura `config/auth.php`/o que for relevante pra apontar pra ela.

Na dúvida: comece sem subclasse. Só crie uma quando surgir a necessidade concreta de colar algo específico do app — não antecipe.
