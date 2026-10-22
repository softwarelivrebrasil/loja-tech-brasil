<?php

namespace Loja\Communities\Actions;

use Loja\Communities\DTOs\RequestCommunityDTO;
use Loja\Communities\Enums\CommunityStatus;
use Loja\Communities\Enums\InstallmentHandling;
use Loja\Communities\Enums\MemberRole;
use Loja\Communities\Models\Community;
use Illuminate\Support\Facades\DB;

/**
 * README.md §8.1 (onboarding) and RN03: a community always starts with at
 * least one member, the requester, as Owner — there's no way to end up
 * with an ownerless community through this Action.
 */
final class RequestCommunityAction
{
    public function execute(RequestCommunityDTO $dto): Community
    {
        return DB::transaction(function () use ($dto) {
            $community = Community::create([
                'name' => $dto->name,
                'slug' => $dto->slug,
                'region' => $dto->region,
                'total_followers' => 0,
                'installment_handling' => InstallmentHandling::PassedToCustomer,
                'status' => CommunityStatus::Pending,
            ]);

            $community->members()->create([
                'user_id' => $dto->requestedByUserId,
                'role' => MemberRole::Owner,
            ]);

            return $community;
        });
    }
}
