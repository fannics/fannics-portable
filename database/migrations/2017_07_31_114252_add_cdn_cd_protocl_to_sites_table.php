<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCdnCdProtoclToSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('cdn_forge_id')->nullable();
            $table->string('cdn_domain')->nullable();
            $table->string('cdn_protocol')->default('http');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('cdn_forge_id');
            $table->dropColumn('cdn_domain');
            $table->dropColumn('cdn_protocol');
        });
    }
}
