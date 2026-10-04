<?php

namespace Loja\Communities\Actions;

use Loja\Communities\Enums\CommunityStatus;
use Loja\Communities\Events\CommunityApprovedEvent;
use Loja\Communities\Exceptions\CommunityCannotBeApprovedException;
use Loja\Communities\Models\Community;

/**
 * README.md RN01: a community only sells after platform-admin approval.
 */
final class ApproveCommunityAction
{
    public function execute(Community $community): Community
    {
        if ($community->status !== CommunityStatus::Pending) {
            throw CommunityCannotBeApprovedException::notPending($community->status);
        }

        $community->update(['status' => CommunityStatus::Approved]);

        event(new CommunityApprovedEvent($community));

        return $community;
    }
}
