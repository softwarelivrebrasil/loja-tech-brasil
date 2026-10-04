<?php

namespace Loja\Withdrawals\Events;

use Loja\Withdrawals\Models\Withdrawal;

final class WithdrawalApprovedEvent
{
    public function __construct(
        public readonly Withdrawal $withdrawal,
    ) {}
}
