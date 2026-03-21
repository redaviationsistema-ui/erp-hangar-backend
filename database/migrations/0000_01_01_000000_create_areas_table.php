<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * FOLIO DE OT 
     *CESA-AVCS  01 - AVIONICS AÑO/CONSECUTIVO
     *CESA-HANG  02 - PLANEADOR AÑO/CONSECUTIVO
     *CESA-BATT  03 - BATERIAS  AÑO/CONSECUTIVO
     *CESA-FREN  04 - FRENOS AÑO/CONSECUTIVO
     *CESA-TREN  05 - TRENES AÑO/CONSECUTIVO
     *CESA-HELI  06 - HELICÓPTEROS AÑO/CONSECUTIVO
     *CESA-PROP  07 - HÉLICES AÑO/CONSECUTIVO
     *CESA-PIST  08 - MOTORES RECÍPROCOS AÑO/CONSECUTIVO
     *CESA-VEST  09 - VESTIDURAS AÑO/CONSECUTIVO
     *CESA-ESTR  10 - ESTRUCTURAS AÑO/CONSECUTIVO
     *CESA-TORN  11 - TORNO AÑO/CONSECUTIVO
     *CESA-SALV  12 - SALVAMENTO ESPECIALIZADO AÑO/CONSECUTIVO
     *CESA-SOLD  13 - SOLDADURA ESPECIALIZADA AÑO/CONSECUTIVO
     */
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // AVIONICS, HANGAR...
            $table->string('codigo'); // AVCS, HANG...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
