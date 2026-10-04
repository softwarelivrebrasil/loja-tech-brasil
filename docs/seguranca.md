# Segurança: regras obrigatórias

Resumo em português. A versão canônica — a que o Claude Code segue ao gerar código — está em [`.claude/rules/security.md`](../.claude/rules/security.md); se as duas divergirem em algum detalhe, aquela é que vale.

Aplicar sempre em código novo, e ao tocar em código existente:

1. **Sanitizar e validar toda entrada (XSS)** — formulário, query string, header, upload, tudo passa por `FormRequest`/`$request->validate()`. Blade sempre com `{{ }}` (escapa automático); `{!! !!}` só pra HTML que o próprio sistema gerou. Upload valida o mime/extensão de verdade, nunca só o nome do arquivo.
2. **Content-Security-Policy** — `script-src`/`style-src` restritos a `'self'` mais só o que for realmente necessário. Testar em modo `Report-Only` antes de bloquear de verdade.
3. **CSRF** — `VerifyCsrfToken` ativo em toda rota `web` que muda estado. Única exceção: webhook de serviço externo (gateway de pagamento, etc.) — e aí a rota precisa validar a assinatura do provedor no lugar do CSRF, não só pular a verificação.
4. **Rate limiting** — toda rota de login (dos dois painéis do Filament, e futuramente da API) tem `throttle`. Endpoint público sem autenticação também precisa, principalmente por não ter outra barreira.
5. **Manter dependências atualizadas** — `composer outdated`/`composer audit`, `npm audit`, rodado periodicamente e antes de release. Atualizar Laravel/Filament numa janela dedicada, não misturado com feature.
6. **Auditar biblioteca nova antes de adicionar** — checar manutenção ativa, uso, issues de segurança abertas.
7. **Evitar iframe desnecessário** — nunca pra conteúdo do próprio sistema. Se for inevitável, `sandbox` + `X-Frame-Options`/`frame-ancestors`.
8. **Permissions-Policy** — desabilitar por padrão câmera/microfone/geolocalização/etc. que o painel não usa.
9. **SRI pra recursos de CDN externo** — `integrity` + `crossorigin` em qualquer `<script>`/`<link>` de CDN. Preferir sempre empacotar via Vite (já é o padrão dos dois apps) em vez de CDN.
