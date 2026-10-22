<?php

use Illuminate\Support\Str;
use Loja\Communities\Actions\AddCommunityMemberAction;
use Loja\Communities\DTOs\AddCommunityMemberDTO;
use Loja\Communities\Enums\CommunityStatus;
use Loja\Communities\Enums\InstallmentHandling;
use Loja\Communities\Enums\MemberRole;
use Loja\Communities\Exceptions\UserAlreadyMemberException;
use Loja\Communities\Models\Community;

beforeEach(function () {
    $this->community = Community::create([
        'name' => 'PHP Brasil',
        'slug' => 'php-brasil',
        'region' => 'Nacional',
        'total_followers' => 0,
        'installment_handling' => InstallmentHandling::PassedToCustomer,
        'status' => CommunityStatus::Approved,
    ]);
});

it('adds a Collaborator to the Community', function () {
    $userId = (string) Str::uuid();

    $member = (new AddCommunityMemberAction)->execute($this->community, new AddCommunityMemberDTO(
        userId: $userId,
        role: MemberRole::Collaborator,
    ));

    expect($member->user_id)->toBe($userId)
        ->and($member->role)->toBe(MemberRole::Collaborator)
        ->and($this->community->members()->count())->toBe(1);
});

it('rejects adding the same user twice to the same Community', function () {
    $userId = (string) Str::uuid();

    (new AddCommunityMemberAction)->execute($this->community, new AddCommunityMemberDTO(
        userId: $userId,
        role: MemberRole::Collaborator,
    ));

    (new AddCommunityMemberAction)->execute($this->community, new AddCommunityMemberDTO(
        userId: $userId,
        role: MemberRole::Owner,
    ));
})->throws(UserAlreadyMemberException::class);
