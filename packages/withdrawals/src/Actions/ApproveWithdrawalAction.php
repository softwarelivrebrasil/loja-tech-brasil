<?php

namespace Loja\Withdrawals\Actions;

use Loja\Withdrawals\Contracts\WithdrawalGatewayContract;
use Loja\Withdrawals\Enums\WithdrawalStatus;
use Loja\Withdrawals\Events\WithdrawalApprovedEvent;
use Loja\Withdrawals\Models\Withdrawal;

final class ApproveWithdrawalAction
{
    public function __construct(
        private readonly WithdrawalGatewayContract $gateway,
    ) {}

    public function execute(Withdrawal $withdrawal): Withdrawal
    {
        $this->gateway->transfer($withdrawal);

        $withdrawal->update(['status' => WithdrawalStatus::Paid]);

        event(new WithdrawalApprovedEvent($withdrawal));

        return $withdrawal;
    }
}
