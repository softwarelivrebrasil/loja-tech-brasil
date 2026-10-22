<?php

use Loja\Communities\Actions\ApproveCommunityAction;
use Loja\Communities\Enums\CommunityStatus;
use Loja\Communities\Enums\InstallmentHandling;
use Loja\Communities\Events\CommunityApprovedEvent;
use Loja\Communities\Exceptions\CommunityCannotBeApprovedException;
use Loja\Communities\Models\Community;

it('approves a pending Community and fires the event', function () {
    Event::fake();

    $community = Community::create([
        'name' => 'PHP Brasil',
        'slug' => 'php-brasil',
        'region' => 'Nacional',
        'total_followers' => 0,
        'installment_handling' => InstallmentHandling::PassedToCustomer,
        'status' => CommunityStatus::Pending,
    ]);

    (new ApproveCommunityAction)->execute($community);

    expect($community->fresh()->status)->toBe(CommunityStatus::Approved);
    Event::assertDispatched(CommunityApprovedEvent::class, fn ($event) => $event->community->is($community));
});

it('rejects approving a Community that is not pending', function () {
    $community = Community::create([
        'name' => 'PHP Brasil',
        'slug' => 'php-brasil',
        'region' => 'Nacional',
        'total_followers' => 0,
        'installment_handling' => InstallmentHandling::PassedToCustomer,
        'status' => CommunityStatus::Suspended,
    ]);

    (new ApproveCommunityAction)->execute($community);
})->throws(CommunityCannotBeApprovedException::class);
