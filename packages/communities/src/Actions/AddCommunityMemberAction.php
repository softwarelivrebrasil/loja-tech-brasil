<?php

namespace Loja\Communities\Actions;

use Loja\Communities\DTOs\AddCommunityMemberDTO;
use Loja\Communities\Exceptions\UserAlreadyMemberException;
use Loja\Communities\Models\Community;
use Loja\Communities\Models\CommunityMember;

/**
 * README.md §8.1: an approved community can invite other members as Owner
 * or Collaborator. RN05: a user may belong to more than one community, so
 * this only guards against joining the *same* community twice.
 */
final class AddCommunityMemberAction
{
    public function execute(Community $community, AddCommunityMemberDTO $dto): CommunityMember
    {
        $alreadyMember = $community->members()
            ->where('user_id', $dto->userId)
            ->exists();

        if ($alreadyMember) {
            throw UserAlreadyMemberException::of($dto->userId, $community->id);
        }

        return $community->members()->create([
            'user_id' => $dto->userId,
            'role' => $dto->role,
        ]);
    }
}
