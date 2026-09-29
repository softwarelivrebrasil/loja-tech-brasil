# UUIDs: every table, every Model

Every table's primary key is a UUID, never an auto-incrementing integer. This applies to `packages/*` migrations and Models, and to `apps/backend`/`apps/admin`'s own tables — no exceptions carved out for "internal" or "low-traffic" tables.

**Why**: sequential integer IDs leak business information through the URL/API alone — `/withdrawals/1847` tells a competitor (or a curious user incrementing the number) roughly how many withdrawals exist and how fast that number is growing, the same way `/orders/{id}` would for order volume. UUIDs remove that signal, and they don't need to be guarded case-by-case later — the convention is enforced at the schema level, once, for every table.

## Migration

```php
// GOOD
Schema::create('withdrawals', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('recipient_id');       // any column referencing another UUID-keyed table's id
    $table->string('recipient_type');   // ...
});

// BAD — the generator's default, never leave this in
Schema::create('withdrawals', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('recipient_id');
});
```

- Foreign-key-shaped columns (whether or not they carry an actual `->constrained()`/`->foreign()` reference) use `$table->uuid('x_id')` or `$table->foreignUuid('x_id')`, never `unsignedBigInteger`/`foreignId`.
- Polymorphic columns use `$table->uuidMorphs('notifiable')`, never `$table->morphs(...)` — the plain version creates an `unsignedBigInteger` foreign column, which would silently mismatch a UUID-keyed target table.
- A column that is a real primary key of its own but isn't a Model id in this sense — Laravel's `sessions.id` (the session token string) is the one example already in this codebase — stays what it naturally is. The rule is about entity ids, not every `->primary()` column that happens to exist.

## Model

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Withdrawal extends Model
{
    use HasUuids;
    // ...
}
```

`HasUuids` (Laravel's own trait, no third-party package) sets `$incrementing = false`, `$keyType = 'string'`, and generates an ordered UUID on create — ordered so it still sorts and indexes reasonably instead of fragmenting a b-tree the way a random UUID v4 would. Every Model backed by a `packages/*` table uses it; `packages/identity`'s `User`/`Admin` and `packages/withdrawals`' `Withdrawal` already do — imitate them.

Docblocks follow: `@property string $id`, not `@property int $id`. DTOs and Action method signatures that carry another Model's id (`RequestWithdrawalDTO::$recipientId`, `$requestedBy`) are typed `string`, not `int`, for the same reason.

## What this doesn't cover yet

`packages/notifications`' table already shipped with a UUID `id` (Laravel's own default database-notifications migration always uses one) — only its `notifiable_id`/`notifiable_type` morph columns needed the `uuidMorphs` fix to match `identity`'s now-UUID `users`/`admins`. New `packages/*` modules built from `.claude/skills/new-module/` should apply this from the first migration, not retrofit it later the way `identity`/`withdrawals`/`notifications` just did.
