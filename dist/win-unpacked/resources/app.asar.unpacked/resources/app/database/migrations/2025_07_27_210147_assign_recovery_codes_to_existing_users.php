<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todos los usuarios que no tienen códigos de recuperación asignados
        $users = User::whereNull('two_factor_recovery_codes')->get();

        foreach ($users as $user) {
            // Generar códigos de recuperación usando el método del modelo
            $codes = [];
            for ($i = 0; $i < 8; $i++) {
                // Generar 8 caracteres alfanuméricos en formato XXXX-XXXX
                $part1 = $this->generateRandomString(4);
                $part2 = $this->generateRandomString(4);
                $codes[] = $part1 . '-' . $part2;
            }

            // Asignar los códigos al usuario
            $user->two_factor_recovery_codes = $codes;
            $user->two_factor_enabled = true;
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover los códigos de recuperación de todos los usuarios
        User::whereNotNull('two_factor_recovery_codes')->update([
            'two_factor_recovery_codes' => null
        ]);
    }

    /**
     * Generate a random string of specified length.
     */
    private function generateRandomString(int $length): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $result;
    }
};
