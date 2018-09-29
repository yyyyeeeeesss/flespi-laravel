<?php

namespace App\Bus;

use App\Bus\Contracts\MethodInterface;
use App\User;

class UpdateUser extends UpdateMethod implements MethodInterface
{
    public function run(array $options)
    {
        if (empty($options['title']) === false) {
            $user = User::find($options['id'] ?? null) ?? new User();
            $user->title = $options['title'];

            $rolesIds = array_column($options['roles'] ?? [], 'value') ?: [];
            $response = $user->save();

            $user->roles()->sync($rolesIds);

            $this->service
                ->publish('v1/events/update/user', $user->getAttributes());

            return $response;
        }
    }
}