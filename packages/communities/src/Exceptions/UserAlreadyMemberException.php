<?php

namespace Loja\Communities\Exceptions;

use RuntimeException;

class UserAlreadyMemberException extends RuntimeException
{
    public static function of(string $userId, string $communityId): self
    {
        return new self("User {$userId} is already a member of community {$communityId}.");
    }
}
