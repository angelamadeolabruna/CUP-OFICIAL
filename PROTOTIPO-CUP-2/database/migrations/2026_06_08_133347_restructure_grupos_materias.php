<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->foreignId('id_materia')->nullable()->after('id_gestion')
                ->constrained('materias', 'id_materia')->restrictOnDelete();
        });

        Schema::table('grupo_horarios', function (Blueprint $table) {
            $table->dropForeign(['id_materia']);
            $table->dropUnique(['id_grupo', 'id_horario', 'id_materia']);
        });
        Schema::table('grupo_horarios', function (Blueprint $table) {
            $table->dropColumn('id_materia');
        });
        Schema::table('grupo_horarios', function (Blueprint $table) {
            $table->unique(['id_grupo', 'id_horario']);
        });

        Schema::table('cargas_horarias', function (Blueprint $table) {
            $table->dropForeign(['id_materia']);
            $table->dropForeign(['id_horario']);
            $table->dropUnique(['id_docente', 'id_grupo', 'id_materia']);
        });
        Schema::table('cargas_horarias', function (Blueprint $table) {
            $table->dropColumn('id_materia');
            $table->dropColumn('id_horario');
        });
        Schema::table('cargas_horarias', function (Blueprint $table) {
            $table->unique(['id_docente', 'id_grupo']);
        });
    }

    public function down(): void
    {
        Schema::table('grupo_horarios', function (Blueprint $table) {
            $table->dropUnique(['id_grupo', 'id_horario']);
        });
        Schema::table('grupo_horarios', function (Blueprint $table) {
            $table->foreignId('id_materia')->constrained('materias', 'id_materia')->restrictOnDelete();
        });
        Schema::table('grupo_horarios', function (Blueprint $table) {
            $table->unique(['id_grupo', 'id_horario', 'id_materia']);
        });

        Schema::table('cargas_horarias', function (Blueprint $table) {
            $table->dropUnique(['id_docente', 'id_grupo']);
        });
        Schema::table('cargas_horarias', function (Blueprint $table) {
            $table->foreignId('id_materia')->constrained('materias', 'id_materia')->restrictOnDelete();
            $table->foreignId('id_horario')->constrained('horarios', 'id_horario')->restrictOnDelete();
        });
        Schema::table('cargas_horarias', function (Blueprint $table) {
            $table->unique(['id_docente', 'id_grupo', 'id_materia']);
        });

        Schema::table('grupos', function (Blueprint $table) {
            $table->dropForeign(['id_materia']);
            $table->dropColumn('id_materia');
        });
    }
};
