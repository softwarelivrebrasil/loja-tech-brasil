<?php

namespace Loja\Communities\Events;

use Loja\Communities\Models\Community;

final class CommunityApprovedEvent
{
    public function __construct(
        public readonly Community $community,
    ) {}
}
