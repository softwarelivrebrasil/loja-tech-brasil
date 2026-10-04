<?php

use Loja\Withdrawals\Actions\RequestWithdrawalAction;
use Loja\Withdrawals\DTOs\RequestWithdrawalDTO;
use Loja\Withdrawals\Enums\WithdrawalStatus;
use Loja\Withdrawals\Exceptions\InvalidWithdrawalAmountException;
use Illuminate\Support\Str;

it('creates a Withdrawal with the flat fee already discounted', function () {
    $withdrawal = (new RequestWithdrawalAction)->execute(new RequestWithdrawalDTO(
        recipientType: 'community',
        recipientId: (string) Str::uuid(),
        amount: 100.00,
        requestedBy: (string) Str::uuid(),
    ));

    expect($withdrawal->fee)->toEqual('2.49')
        ->and($withdrawal->net_amount)->toEqual('97.51')
        ->and($withdrawal->status)->toBe(WithdrawalStatus::Requested);
});

it('rejects a withdrawal that would not cover the flat fee', function () {
    (new RequestWithdrawalAction)->execute(new RequestWithdrawalDTO(
        recipientType: 'community',
        recipientId: (string) Str::uuid(),
        amount: 2.00,
        requestedBy: (string) Str::uuid(),
    ));
})->throws(InvalidWithdrawalAmountException::class);
