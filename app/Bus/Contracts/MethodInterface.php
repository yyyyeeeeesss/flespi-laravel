<?php
namespace App\Bus\Contracts;

interface MethodInterface
{
    public function run(array $options);
}