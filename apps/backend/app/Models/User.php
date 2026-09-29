<?php

namespace App\Models;

use Loja\Identity\Models\User as BaseUser;

/**
 * Columns, casts, factory, and the users/admins tables themselves come from
 * loja/identity — the same package apps/admin requires, so both apps
 * authenticate against the same users table instead of two independent ones.
 * Add backend-specific traits/relations/overrides here.
 */
class User extends BaseUser
{
    //
}
