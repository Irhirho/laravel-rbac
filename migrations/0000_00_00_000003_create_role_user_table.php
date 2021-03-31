<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = App::make('config')->get('rbac');

        $roleUserTable = $config['tables']['role_user'];
        $rolesTable = $config['tables']['roles'];
        $user = App::make($config['user']);

        Schema::create($roleUserTable, function (Blueprint $table) use ($rolesTable, $user) {
            $table->unsignedInteger('role_id');
            $table->string('user_id');

            $table->foreign('role_id')->references('id')->on($rolesTable);
            $table->foreign('user_id')->references($user->getKeyName())->on($user->getTable());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
