# Ambiente de desenvolvimento com Docker

## Subindo tudo

```bash
cp .env.example .env      # variáveis do docker-compose (usuário/senha do Postgres, portas)
docker compose up
```

Não precisa ter PHP, Composer ou Node instalados na máquina — os containers cuidam disso sozinhos (na primeira subida, cada app roda `composer install`/`npm ci`/`npm run build` dentro do próprio container, antes de aceitar requisições).

- Backend: http://localhost:8000
- Admin: http://localhost:8001 (`/` é o painel público, `/admin` é o painel interno, `/app` é o painel de usuário comum)

## O que sobe

Quatro serviços: `postgres`, `redis`, `backend` (`apps/backend`), `admin` (`apps/admin`). Um Postgres só, compartilhado pelos dois apps — ver [`banco-de-dados.md`](banco-de-dados.md) pra entender por quê e o que isso implica pras migrations.

`backend` e `admin` não sobem juntos por acaso: `admin` só começa a aceitar requisição depois que `backend` está com o healthcheck verde (que só fica verde depois de rodar as migrations) — então nunca existe uma janela onde `apps/admin` está de pé mas o banco ainda não tem as tabelas.

## Por que as duas imagens são diferentes

- **`backend`** roda [FrankenPHP](https://frankenphp.dev) com Laravel Octane (`php artisan octane:start --watch`) — é o runtime que o `README.md` já definiu pro backend (processo long-running, não reinicia a cada request).
- **`admin`** roda o servidor embutido do PHP direto (`php -S`), **não** `php artisan serve`. Motivo não óbvio: o `artisan serve` do Laravel inicia o servidor como um subprocesso com um ambiente "filtrado" que descarta as variáveis de ambiente que o Docker injeta no container — na prática, isso fazia o `apps/admin` tentar conectar num SQLite que não existe em vez do Postgres do `docker-compose.yml`, mesmo com `DB_CONNECTION=pgsql` configurado certinho no container. Rodar o servidor embutido do PHP direto, usando o mesmo script de rotas que o `artisan serve` usa por baixo dos panos, resolve isso sem perder nenhum comportamento. Detalhe técnico: [`.claude/rules/docker.md`](../.claude/rules/docker.md).

Nenhuma das duas imagens já vem com Laravel Octane em `apps/admin` — só `apps/backend` roda Octane (ver `README.md`: o painel administrativo tem um perfil de tráfego bem menor, não precisa de processo long-running).

## Comandos úteis

```bash
docker compose logs -f backend        # acompanhar log de um serviço
docker compose exec backend sh        # abrir um shell dentro do container
docker compose exec backend php artisan tinker
docker compose down                   # para os containers, mantém os dados
docker compose down -v                # para os containers E apaga banco/vendor/node_modules (começa do zero)
```

## Onde ficam os dados entre reinícios

`vendor/`, `node_modules/`, os assets compilados (`public/build`) e os dados do Postgres/Redis ficam em *named volumes* do Docker — sobrevivem a um `docker compose down` normal (sem `-v`), então reiniciar não obriga reinstalar nada. O código-fonte em si (`apps/`, `packages/`) é montado direto da sua máquina — qualquer edição aparece no container na hora, sem rebuild.
