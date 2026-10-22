<?php

namespace Loja\Communities\Enums;

enum CommunityStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Suspended = 'suspended';
}
