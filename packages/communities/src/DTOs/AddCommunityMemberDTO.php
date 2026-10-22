<?php

namespace Loja\Communities\DTOs;

use Loja\Communities\Enums\MemberRole;

final class AddCommunityMemberDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly MemberRole $role,
    ) {}
}
