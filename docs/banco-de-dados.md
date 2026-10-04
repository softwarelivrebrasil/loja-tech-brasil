# Banco de dados: um Postgres só, `apps/backend` cuida das migrations, tudo em UUID

## Um banco só, compartilhado

`apps/backend` e `apps/admin` apontam pro **mesmo banco Postgres físico** (ver `docker-compose.yml`: os dois containers recebem as mesmas variáveis `DB_*`). Isso não é acidente — é a consequência direta de `packages/identity` existir (ver [`models-e-packages.md`](models-e-packages.md)): os dois apps precisam autenticar e enxergar exatamente as mesmas linhas de `users`/`admins`, e isso só funciona se for o mesmo banco. Não tem sincronização, não tem join entre bancos diferentes.

## Só o `apps/backend` roda `migrate`

Como é o mesmo banco, também é a mesma tabela `migrations` (a tabela que o Laravel usa pra saber quais migrations já rodaram) — não importa qual app você rodou o `artisan migrate`, ela é global pro banco inteiro. Pra isso não virar bagunça, a regra é simples:

- **`apps/backend` migra tudo**: as próprias migrations dele (`cache`, `jobs`) mais toda migration de `packages/*`, carregadas automaticamente pelo `ServiceProvider` de cada pacote.
- **`apps/admin` nunca roda `migrate`**, e não tem nenhuma migration própria — a pasta `apps/admin/database/migrations/` está vazia de propósito. Ele só lê e escreve nas mesmas tabelas que `apps/backend` já criou, através das mesmas Models de `packages/*`.

Isso já pegou um bug real: originalmente `apps/admin` tinha sua própria cópia das migrations de `cache`/`jobs` (o padrão que o Laravel gera por padrão) — inofensivo enquanto cada app tinha seu próprio SQLite, mas quebraria com um erro de "tabela já existe" assim que os dois passassem a apontar pro mesmo Postgres. Foram removidas.

**Regra pra migration nova**: nunca cria uma migration dentro de `apps/admin`. Se é infraestrutura específica da API, vai em `apps/backend/database/migrations/`. Qualquer outra coisa — qualquer coisa que uma Model toque — vai no `packages/*` dono daquela Model, junto com o resto do módulo (Model, Action, DTO). Foi exatamente assim que a tabela `notifications` (o sino do Filament) saiu de `apps/admin` e virou `packages/notifications`.

Detalhe técnico completo (incluindo como isso é aplicado no Docker): [`.claude/rules/database.md`](../.claude/rules/database.md).

## Toda tabela usa UUID, nunca id incremental

Nenhuma tabela usa `$table->id()` (o incremental padrão do Laravel). Toda tabela usa UUID como chave primária:

```php
// certo
$table->uuid('id')->primary();
$table->uuid('recipient_id');           // referência a outra tabela com UUID
$table->uuidMorphs('notifiable');       // coluna polimórfica

// nunca isso
$table->id();
$table->unsignedBigInteger('recipient_id');
$table->morphs('notifiable');
```

E toda Model usa a trait `HasUuids` do próprio Laravel:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Withdrawal extends Model
{
    use HasUuids;
    // ...
}
```

**Por quê:** um id incremental (`/withdrawals/1847`) entrega informação de negócio de graça — dá pra estimar quantos saques existem e a velocidade que esse número cresce, só olhando a URL. UUID tira esse sinal, e tira de um jeito que não precisa ser lembrado caso a caso depois — é regra de schema, aplicada uma vez, em toda tabela.

Detalhe completo (inclusive por que é UUID "ordenado" e não aleatório puro): [`.claude/rules/uuids.md`](../.claude/rules/uuids.md).

## No dia a dia local (sem Docker)

Cada app ainda tem seu próprio `.env` com `DB_CONNECTION=sqlite` por padrão — bancos totalmente separados, sem nenhuma das regras acima entrando em jogo. A regra de "um banco só" só importa a partir do momento que os dois apps apontam pro mesmo Postgres, o que hoje só acontece rodando `docker compose up` (ver [`ambiente-docker.md`](ambiente-docker.md)).
