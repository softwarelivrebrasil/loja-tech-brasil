<?php

namespace Loja\Communities\Exceptions;

use Loja\Communities\Enums\CommunityStatus;
use RuntimeException;

class CommunityCannotBeApprovedException extends RuntimeException
{
    public static function notPending(CommunityStatus $status): self
    {
        return new self("Only a pending community can be approved, this one is {$status->value}.");
    }
}
