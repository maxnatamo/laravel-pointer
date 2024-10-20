<?php

namespace Workbench\App\Models;

use Pointer\Traits\Tourable;

class User extends UserWithoutTourable
{
    use Tourable;
}
