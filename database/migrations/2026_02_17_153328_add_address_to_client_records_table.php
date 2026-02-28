<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_records', function (Blueprint $table) {
            // I-add ni kung wala pa
            if (!Schema::hasColumn('client_records', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
        });
    }

    public function down()
    {
        Schema::table('client_records', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
