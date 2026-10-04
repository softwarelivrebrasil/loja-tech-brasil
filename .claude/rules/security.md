# Security: mandatory rules

Apply these to new code always, and to existing code whenever you touch it — this file exists to make them checkable in review, not just a one-time pass. They apply to `apps/backend`, `apps/admin`, and `packages/*` alike.

1. **Sanitize and validate every input (XSS)**
   - Every piece of user input (form, query string, header, upload) goes through a `FormRequest` or `$request->validate()` — never trust a raw request value, in a Filament form/table action or an `apps/backend` API endpoint alike.
   - Blade: always `{{ $var }}` (auto-escaped). `{!! $var !!}` only for HTML the system itself generated, never for user-supplied data — if you use it, comment why at that call site.
   - Filament/Livewire: don't use `HtmlString`/`->html()` on a field displaying user input without sanitizing it first (`strip_tags`, or a proper purifier when limited HTML is genuinely needed).
   - Uploads: validate the real MIME type/extension, not just the filename. Never serve a user-uploaded file as executable.

2. **Set a Content-Security-Policy**
   - `script-src`/`style-src` scoped to `'self'` plus only the domains actually needed, avoiding `'unsafe-inline'`/`'unsafe-eval'` where avoidable.
   - Roll it out `Content-Security-Policy-Report-Only` first, confirm Livewire/Filament aren't broken by it, then switch to blocking.

3. **CSRF protection**
   - `VerifyCsrfToken` stays active on every state-changing `web` route.
   - The only legitimate exception is an external webhook (a payment gateway — see README's open gateway decision — WhatsApp/Evolution, etc.). Those routes **must** validate the provider's own request signature/secret in place of CSRF, not skip verification entirely.

4. **Rate limiting**
   - Every authentication route (both Filament panels' login, and `apps/backend`'s eventual API auth) has `throttle`, tuned tighter for login than for plain reads.
   - Public unauthenticated endpoints (webhooks, any future public tracking/status page) need rate limiting too, precisely because they have no auth to fall back on.

5. **Keep the framework and dependencies current**
   - Run `composer outdated` / `composer audit` in `apps/backend` and `apps/admin` (covers `packages/*` too, since they're required in via path repos), and `npm audit` wherever there's a `package.json`, periodically and before any release.
   - Upgrade Laravel/Filament in a dedicated pass, not bundled into a feature PR — test for regressions before it reaches production.

6. **Audit third-party packages before adding them**
   - Before `composer require`/`npm install`-ing something new: check it's actively maintained, has a reasonable install base, and has no open unpatched security issues.
   - Prefer official or community-maintained Laravel/Filament packages over an abandoned or single-maintainer alternative when both exist.

7. **Avoid unnecessary iframes**
   - Don't embed the system's own content in an `<iframe>`. When an iframe is unavoidable (a PDF preview, a trusted third-party embed), use `sandbox` and set `X-Frame-Options`/`frame-ancestors` to keep the rest of the site out of other pages' frames.

8. **Set a Permissions-Policy**
   - Disable sensitive browser APIs the panels don't use (camera, microphone, geolocation, USB, etc.) by default, opting in only to what's genuinely used.

9. **Subresource Integrity for anything loaded from a CDN**
   - Any `<script>`/`<link>` pulled from an external CDN needs `integrity` + `crossorigin`.
   - Prefer bundling through Vite (already the default for both `apps/backend` and `apps/admin` — see `laravel-vite-plugin` in each app's `vite.config.js`) over a CDN wherever possible — it removes the need for SRI and for trusting a third party's uptime/integrity at all.
