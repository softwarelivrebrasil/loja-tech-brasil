<?php

namespace Loja\Communities\Models;

use Loja\Communities\Enums\CommunityStatus;
use Loja\Communities\Enums\InstallmentHandling;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * README.md §11 (COMUNIDADE). A community doesn't hold stock or ship
 * anything — it picks a supplier, lists products and keeps the margin
 * (dropshipping). Only sells once approved (RN01) and once it has an
 * active payment-gateway sub-account (RN02, payment_gateway_account_id).
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $region
 * @property int $total_followers
 * @property InstallmentHandling $installment_handling
 * @property CommunityStatus $status
 * @property string|null $payment_gateway_account_id
 */
class Community extends Model
{
    use HasUuids;

    protected $table = 'communities';

    protected $fillable = [
        'name',
        'slug',
        'region',
        'total_followers',
        'installment_handling',
        'status',
        'payment_gateway_account_id',
    ];

    protected $casts = [
        'total_followers' => 'integer',
        'installment_handling' => InstallmentHandling::class,
        'status' => CommunityStatus::class,
    ];

    /**
     * @return HasMany<CommunityMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }
}
