<?php

use App\Enums\HoeveelstePoging;
use App\Enums\TypeVoorlopigRijbewijs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('datum_slagen_theorieB')->nullable();
            $table->enum('type_voorlopig_rijbewijs', TypeVoorlopigRijbewijs::values())->nullable();
            $table->string('afgiftedatum_voorlopig_rijbewijsB')->nullable();
            $table->enum('hoeveelste_poging', HoeveelstePoging::values())->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('datum_slagen_theorieB');
            $table->dropColumn('type_voorlopig_rijbewijs');
            $table->dropColumn('afgiftedatum_voorlopig_rijbewijsB');
            $table->dropColumn('hoeveelste_poging');
        });
    }
};
