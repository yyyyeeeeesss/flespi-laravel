<?php

namespace App\Console\Commands;

use App\Bus\GetRoles;
use App\Bus\UpdateRole;
use App\Bus\UpdateUser;
use App\Bus\GetUsers;
use App\Role;
use App\Services\MqttService;
use App\User;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class BusListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bus:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bus command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        App::make(MqttService::class)->rpc([
            'v1/get/users' => GetUsers::class,
            'v1/update/user' => UpdateUser::class,
            'v1/get/roles' => GetRoles::class,
            'v1/update/role' => UpdateRole::class
        ]);
    }
}
