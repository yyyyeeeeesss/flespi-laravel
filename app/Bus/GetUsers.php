<?php

namespace App\Bus;

use App\Bus\Contracts\MethodInterface;
use App\User;

class GetUsers implements MethodInterface
{
    public function run(array $options)
    {
        $limit = $options['rowsPerPage'] ?? 10;
        $offset = 0;

        if (empty($options['page']) === false) {
            $offset = $options['page'] * $limit - $limit;
        }

        $builder = User::with('roles');

        if (empty($options['roles']) === false) {
            $builder->whereHas('roles', function ($query) use ($options) {
                $query->whereIn('roles.id', array_filter($options['roles']));
            });
        }

        $count = $builder->count();

        $users = $builder
            ->offset($offset)
            ->limit($limit)
            ->orderBy('id')
            ->get();

        return [
            'items' => $users,
            'total' => $count
        ];
    }
}