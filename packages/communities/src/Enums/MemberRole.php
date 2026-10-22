<?php

namespace Loja\Communities\Enums;

enum MemberRole: string
{
    case Owner = 'owner';
    case Collaborator = 'collaborator';
}
