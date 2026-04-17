<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clientes')
            ->select(['id', 'contrasena_portal'])
            ->whereNotNull('contrasena_portal')
            ->orderBy('id')
            ->chunkById(100, function ($clientes): void {
                foreach ($clientes as $cliente) {
                    $rawValue = trim((string) $cliente->contrasena_portal);

                    if ($rawValue === '') {
                        continue;
                    }

                    try {
                        Crypt::decryptString($rawValue);
                        continue;
                    } catch (DecryptException) {
                        DB::table('clientes')
                            ->where('id', $cliente->id)
                            ->update([
                                'contrasena_portal' => Crypt::encryptString($rawValue),
                            ]);
                    }
                }
            }, 'id');
    }

    public function down(): void
    {
        // No revertimos para evitar exponer contrasenas previamente cifradas.
    }
};
