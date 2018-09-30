<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('users_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->unsigned();
            $table->integer('roles_id')->unsigned();

            $table
                ->foreign('users_id', 'fk_users')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('roles_id', 'fk_roles')
                ->references('id')->on('roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');;

            $table->timestamps();
        });

        $faker = \Faker\Factory::create();

        for($i = 0; $i < 7;$i++) {
            \App\Role::create([
                'title' => $faker->text(20)
            ]);
        }

        $roles = \App\Role::get();

        for($i = 0; $i < 10000;$i++) {
            $user = \App\User::create([
                'title' => $faker->text(50)
            ]);

            $rand = rand(0,6);
            $ids = [];
            for ($j =1; $j < $rand; $j++) {
                $ids[] = $roles[$rand]->id;
            }
            $user->roles()->sync($ids);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
}
