<?php

namespace Loja\Communities\DTOs;

final class RequestCommunityDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $region,
        public readonly string $requestedByUserId,
    ) {}
}
