<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 28.09.2018
 * Time: 19:44
 */

namespace App\Bus;


use App\Services\MqttService;

class UpdateMethod
{
    protected $service;

    public function __construct(MqttService $service)
    {
        $this->service = $service;
    }
}