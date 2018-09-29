<?php

namespace App\Bus;

use App\Role;
use App\Bus\Contracts\MethodInterface;

class GetRoles implements MethodInterface
{
    public function run(array $options)
    {
        $roles = Role::get()->toArray();

        return [
            'items' => $roles,
            'total' => count($roles)
        ];
    }
}