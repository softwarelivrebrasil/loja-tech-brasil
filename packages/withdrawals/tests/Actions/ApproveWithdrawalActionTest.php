<?php

use Loja\Withdrawals\Actions\ApproveWithdrawalAction;
use Loja\Withdrawals\Contracts\WithdrawalGatewayContract;
use Loja\Withdrawals\Enums\WithdrawalStatus;
use Loja\Withdrawals\Events\WithdrawalApprovedEvent;
use Loja\Withdrawals\Models\Withdrawal;
use Illuminate\Support\Str;

it('transfers through the bound gateway, marks the Withdrawal as paid and fires the event', function () {
    Event::fake();

    $gateway = Mockery::mock(WithdrawalGatewayContract::class);
    $gateway->shouldReceive('transfer')->once();
    app()->instance(WithdrawalGatewayContract::class, $gateway);

    $withdrawal = Withdrawal::create([
        'recipient_type' => 'community',
        'recipient_id' => (string) Str::uuid(),
        'amount' => 100.00,
        'fee' => 2.49,
        'net_amount' => 97.51,
        'status' => WithdrawalStatus::Requested,
        'requested_by' => (string) Str::uuid(),
    ]);

    app(ApproveWithdrawalAction::class)->execute($withdrawal);

    expect($withdrawal->fresh()->status)->toBe(WithdrawalStatus::Paid);
    Event::assertDispatched(WithdrawalApprovedEvent::class, fn ($event) => $event->withdrawal->is($withdrawal));
});
