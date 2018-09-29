<?php

namespace App\Bus;

use App\Role;
use App\Bus\Contracts\MethodInterface;

class UpdateRole extends UpdateMethod implements MethodInterface
{
    public function run(array $options)
    {
        if (empty($options['title']) === false) {
            $role = Role::find($options['id'] ?? null) ?? new Role();
            $role->title = $options['title'];

            $this->service
                ->publish('v1/events/update/role', $role->getAttributes());

            return $role->save();
        }
    }
}