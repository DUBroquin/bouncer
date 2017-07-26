<?php

namespace Dubroquin\Bouncer\Database;

use Dubroquin\Bouncer\Database\Concerns\HasRoles;
use Dubroquin\Bouncer\Database\Concerns\HasAbilities;

trait HasRolesAndAbilities
{
    use HasRoles, HasAbilities {
        HasRoles::getClipboardInstance insteadof HasAbilities;
    }
}
