# Arquitetura: monorepo, apps e packages

## A ideia em uma frase

Um repositório Git só, com **dois apps Laravel independentes** (`apps/backend` e `apps/admin`) que compartilham as mesmas regras de negócio através de **pacotes Composer** (`packages/*`) — nenhum dos dois apps duplica Model, migration ou lógica que o outro também precisa.

```
loja-tech-brasil/
  apps/
    backend/     # Laravel Octane — a API, dona de todo caminho de escrita
    admin/       # Laravel + Filament — painel interno (3 telas: admin/app/guest)
    web/         # ainda não existe — futuro frontend, só HTTP, nunca toca em packages/*
  packages/
    identity/    # User, Admin — as mesmas linhas nas mesmas tabelas pros dois apps
    withdrawals/ # módulo de referência: Model + Action + DTO + Contract/Adapter
    notifications/ # tabela de notificações do Laravel (sino do Filament)
```

## Por que dois apps Laravel em vez de um

`apps/backend` e `apps/admin` são dois projetos Laravel **completamente separados** — cada um com seu próprio `composer.json`, `vendor/`, `.env`, ciclo de deploy. Isso é proposital:

- **`apps/backend`** é a API (Laravel Octane — processo long-running, ver `README.md`). É o único lugar que qualquer cliente externo (o futuro `apps/web`, o app mobile) fala com o sistema.
- **`apps/admin`** é o painel interno (Filament). Tem um perfil de operação bem diferente do backend: baixo tráfego, PHP "normal" (nem Octane), interface administrativa em vez de API.

Colocar os dois no mesmo app Laravel misturaria dois ritmos de deploy e duas superfícies de autenticação diferentes numa coisa só. Separar significa que um bug ou deploy do painel administrativo não arrisca a API que o cliente final usa.

## O problema que isso cria — e como `packages/*` resolve

Se são dois apps Laravel separados, cada um começaria com sua própria tabela `users`, sua própria Model `User`, suas próprias regras de negócio duplicadas. Isso realmente aconteceu nas primeiras versões deste template: `apps/backend` e `apps/admin` tinham cada um sua própria migration de `users` — duas tabelas independentes, divergindo silenciosamente (uma tinha colunas que a outra não tinha). Um usuário criado pela API era invisível no painel.

A correção foi: qualquer coisa que **os dois apps precisam ver exatamente igual** — a Model, a migration, a factory — vai pra um pacote Composer em `packages/*`, e os dois apps passam a *requerer* esse pacote (`composer.json`: `"loja/identity": "^1.0.0"`), em vez de reimplementar.

Isso vale tanto pra:
- **Módulos de negócio** de verdade (`packages/withdrawals` é o exemplo de referência) — viram pacote pra ter um limite reforçado (nada fora do pacote acessa a tabela direto) e pra já nascerem no formato que teriam se um dia virassem um serviço separado.
- **Dados de identidade compartilhados** (`packages/identity`) — vira pacote por um motivo diferente: não é candidato a virar microsserviço, é que os dois apps **precisam literalmente enxergar as mesmas linhas** da tabela `users`/`admins`.

Ver [`.claude/rules/architecture.md`](../.claude/rules/architecture.md) pra a explicação completa dessa distinção, e [`models-e-packages.md`](models-e-packages.md) pra como isso aparece no código de verdade (Models, extends, etc.).

## Como um pacote chega nos dois apps

Cada `composer.json` (de `apps/backend` e `apps/admin`) declara `packages/*` como um "path repository":

```json
{
  "repositories": [
    { "type": "path", "url": "../../packages/*" }
  ],
  "require": {
    "loja/identity": "^1.0.0",
    "loja/notifications": "^1.0.0",
    "loja/withdrawals": "^1.0.0"
  }
}
```

O Composer resolve isso como um symlink (`vendor/loja/identity -> ../../../../packages/identity`), então qualquer mudança em `packages/identity/src/...` aparece imediatamente nos dois apps, sem precisar publicar nada. O `ServiceProvider` de cada pacote (`IdentityServiceProvider`, `WithdrawalsServiceProvider`, `NotificationsServiceProvider`) é descoberto automaticamente pelo Laravel (via `extra.laravel.providers` no `composer.json` do pacote) — não precisa registrar nada manualmente em `config/app.php`.

## A regra que mantém tudo isso coerente: Actions

Qualquer escrita numa Model que tenha regra de negócio por trás (não é só "salvar um campo", tem efeito colateral, validação, notificação) passa pela `Actions/` do próprio pacote — nunca um `->update()` cru chamado de fora. É isso que garante que `apps/backend` e `apps/admin`, quando os dois chamam a mesma Action, tenham exatamente o mesmo comportamento — a regra existe em um lugar só. Detalhe completo em [`.claude/rules/architecture.md`](../.claude/rules/architecture.md), seção "The Action rule".

## O que ainda não existe

- `apps/web`: vai ser um frontend (provavelmente Nuxt, ver `README.md`) que conversa só com a API HTTP do `apps/backend` — nunca importa `packages/*` nem toca em banco direto.
- Os módulos de negócio reais do domínio (`comunidades`, `fornecedores`, `produtos`, `pedidos`, `pagamentos`, `moderacao`) — hoje só existem `identity`, `withdrawals` e `notifications` como referência/infraestrutura, imitando exatamente a forma que esses módulos reais vão seguir.
