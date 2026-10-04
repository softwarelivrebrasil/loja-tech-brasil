<?php

namespace Loja\Communities\Models;

use Loja\Communities\Enums\MemberRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * README.md §11 (MEMBRO_COMUNIDADE), RN03/RN04/RN05. user_id references
 * packages/identity's users table by id only — no Eloquent relationship
 * crosses the package boundary, see .claude/rules/architecture.md.
 *
 * @property string $id
 * @property string $community_id
 * @property string $user_id
 * @property MemberRole $role
 */
class CommunityMember extends Model
{
    use HasUuids;

    protected $table = 'community_members';

    protected $fillable = [
        'community_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => MemberRole::class,
    ];

    /**
     * @return BelongsTo<Community, $this>
     */
    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
