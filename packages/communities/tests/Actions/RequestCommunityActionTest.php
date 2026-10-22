<?php

use Illuminate\Support\Str;
use Loja\Communities\Actions\RequestCommunityAction;
use Loja\Communities\DTOs\RequestCommunityDTO;
use Loja\Communities\Enums\CommunityStatus;
use Loja\Communities\Enums\InstallmentHandling;
use Loja\Communities\Enums\MemberRole;

it('creates a pending Community with the requester as its first Owner', function () {
    $userId = (string) Str::uuid();

    $community = (new RequestCommunityAction)->execute(new RequestCommunityDTO(
        name: 'PHP Brasil',
        slug: 'php-brasil',
        region: 'Nacional',
        requestedByUserId: $userId,
    ));

    expect($community->status)->toBe(CommunityStatus::Pending)
        ->and($community->installment_handling)->toBe(InstallmentHandling::PassedToCustomer)
        ->and($community->total_followers)->toBe(0);

    $member = $community->members()->sole();

    expect($member->user_id)->toBe($userId)
        ->and($member->role)->toBe(MemberRole::Owner);
});
