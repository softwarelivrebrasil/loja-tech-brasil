<?php

namespace Loja\Withdrawals\DTOs;

final class RequestWithdrawalDTO
{
    public function __construct(
        public readonly string $recipientType,
        public readonly string $recipientId,
        public readonly float $amount,
        public readonly string $requestedBy,
    ) {}
}
