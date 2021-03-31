<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = App::make('config')->get('rbac');

        $permissionRoleTable = $config['tables']['permission_role'];
        $permissionsTable = $config['tables']['permissions'];
        $rolesTable = $config['tables']['roles'];

        Schema::create($permissionRoleTable, function (Blueprint $table) use ($permissionsTable, $rolesTable) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');

            $table->foreign('permission_id')->references('id')->on($permissionsTable);
            $table->foreign('role_id')->references('id')->on($rolesTable);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_role');
    }
}
