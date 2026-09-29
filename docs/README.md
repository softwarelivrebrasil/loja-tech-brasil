# Documentação técnica

Enquanto o [`README.md`](../README.md) na raiz é o documento de **regras de negócio** (o que é a Loja das Comunidades Tech BR, como funciona o modelo de dropshipping, split de pagamento, moderação, etc.), esta pasta documenta **como o código está organizado** — pra quem está chegando no projeto e precisa entender a arquitetura antes de mexer em algo.

- [`arquitetura.md`](arquitetura.md) — visão geral do monorepo: por que `apps/backend`, `apps/admin` e `packages/*` existem como coisas separadas, e como eles se conectam.
- [`models-e-packages.md`](models-e-packages.md) — onde uma Model mora, e como `apps/backend`/`apps/admin` usam uma Model que vive em `packages/*` — inclusive quando (e por que) cada app precisa da sua própria subclasse.
- [`banco-de-dados.md`](banco-de-dados.md) — um Postgres só, compartilhado pelos dois apps; por que só o `apps/backend` roda migration; por que toda tabela usa UUID em vez de id incremental.
- [`ambiente-docker.md`](ambiente-docker.md) — como subir tudo localmente com `docker compose up`.
- [`seguranca.md`](seguranca.md) — resumo em português das regras de segurança obrigatórias (versão canônica, mais detalhada, em [`.claude/rules/security.md`](../.claude/rules/security.md)).

## Sobre as duas camadas de documentação

Esse projeto tem documentação em dois lugares com públicos diferentes:

- **`docs/`** (aqui) — pra pessoas. Explicação, contexto, "por que foi feito assim".
- **[`.claude/rules/`](../.claude/rules/)** — pra o Claude Code (ou qualquer assistente que leia `CLAUDE.md`) seguir como convenção obrigatória ao gerar código. Mais denso, assume que quem lê já conhece o básico do projeto.

Os dois não competem — `docs/` é a porta de entrada, `.claude/rules/` é a referência que o código de verdade tem que obedecer. Quando um `docs/*.md` menciona uma regra específica (ex.: "toda tabela usa UUID"), ele aponta pro arquivo correspondente em `.claude/rules/` em vez de duplicar o texto inteiro.
