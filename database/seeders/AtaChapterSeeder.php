<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\AtaChapter;
use App\Models\AtaSubchapter;

class AtaChapterSeeder extends Seeder
{
    public function run(): void
    {
        $atas = [

            // 🔹 ATA 00-12 GENERAL
            ['codigo' => '00', 'descripcion' => 'General', 'sub' => [
                ['codigo' => '00-00', 'descripcion' => 'General', 'horas' => 1000, 'tipo' => 'D'],
            ]],
            ['codigo' => '05', 'descripcion' => 'Time Limits', 'sub' => [
                ['codigo' => '05-10', 'descripcion' => 'Time Limits', 'horas' => 100, 'dias' => 30, 'tipo' => 'A'],
                ['codigo' => '05-20', 'descripcion' => 'Scheduled Maintenance', 'horas' => 500, 'dias' => 180, 'tipo' => 'C'],
            ]],
            ['codigo' => '06', 'descripcion' => 'Dimensions', 'sub' => [
                ['codigo' => '06-00', 'descripcion' => 'General', 'tipo' => 'D'],
            ]],
            ['codigo' => '07', 'descripcion' => 'Lifting and Shoring', 'sub' => [
                ['codigo' => '07-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '08', 'descripcion' => 'Leveling and Weighing', 'sub' => [
                ['codigo' => '08-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '09', 'descripcion' => 'Towing and Taxiing', 'sub' => [
                ['codigo' => '09-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '10', 'descripcion' => 'Parking and Storage', 'sub' => [
                ['codigo' => '10-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '11', 'descripcion' => 'Placards and Markings', 'sub' => [
                ['codigo' => '11-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '12', 'descripcion' => 'Servicing', 'sub' => [
                ['codigo' => '12-00', 'descripcion' => 'General'],
            ]],

            // 🔹 ATA 20-49 SYSTEMS
            ['codigo' => '20', 'descripcion' => 'Standard Practices', 'sub' => [
                ['codigo' => '20-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '21', 'descripcion' => 'Air Conditioning', 'sub' => [
                ['codigo' => '21-10', 'descripcion' => 'Distribution', 'horas' => 200, 'tipo' => 'A'],
                ['codigo' => '21-20', 'descripcion' => 'Cooling', 'horas' => 300, 'tipo' => 'B'],
                ['codigo' => '21-30', 'descripcion' => 'Pressurization', 'horas' => 400, 'tipo' => 'C'],
            ]],
            ['codigo' => '22', 'descripcion' => 'Auto Flight', 'sub' => [
                ['codigo' => '22-10', 'descripcion' => 'Autopilot', 'horas' => 200],
            ]],
            ['codigo' => '23', 'descripcion' => 'Communications', 'sub' => [
                ['codigo' => '23-10', 'descripcion' => 'HF', 'horas' => 200],
                ['codigo' => '23-20', 'descripcion' => 'VHF', 'horas' => 200],
            ]],
            ['codigo' => '24', 'descripcion' => 'Electrical Power', 'sub' => [
                ['codigo' => '24-10', 'descripcion' => 'Generator', 'horas' => 300, 'tipo' => 'B'],
                ['codigo' => '24-20', 'descripcion' => 'AC System', 'horas' => 200, 'tipo' => 'A'],
                ['codigo' => '24-30', 'descripcion' => 'DC System', 'horas' => 150, 'tipo' => 'A'],
            ]],
            ['codigo' => '25', 'descripcion' => 'Equipment/Furnishings', 'sub' => [
                ['codigo' => '25-10', 'descripcion' => 'Flight Compartment'],
                ['codigo' => '25-20', 'descripcion' => 'Passenger'],
            ]],
            ['codigo' => '26', 'descripcion' => 'Fire Protection', 'sub' => [
                ['codigo' => '26-10', 'descripcion' => 'Detection'],
                ['codigo' => '26-20', 'descripcion' => 'Extinguishing'],
            ]],
            ['codigo' => '27', 'descripcion' => 'Flight Controls', 'sub' => [
                ['codigo' => '27-10', 'descripcion' => 'Aileron', 'horas' => 100],
                ['codigo' => '27-20', 'descripcion' => 'Rudder', 'horas' => 100],
                ['codigo' => '27-30', 'descripcion' => 'Elevator', 'horas' => 100],
            ]],
            ['codigo' => '28', 'descripcion' => 'Fuel', 'sub' => [
                ['codigo' => '28-10', 'descripcion' => 'Storage', 'horas' => 400],
                ['codigo' => '28-20', 'descripcion' => 'Distribution', 'horas' => 300],
            ]],
            ['codigo' => '29', 'descripcion' => 'Hydraulic Power', 'sub' => [
                ['codigo' => '29-10', 'descripcion' => 'Main System', 'horas' => 200],
            ]],
            ['codigo' => '30', 'descripcion' => 'Ice and Rain Protection', 'sub' => [
                ['codigo' => '30-10', 'descripcion' => 'Anti-Ice'],
            ]],
            ['codigo' => '31', 'descripcion' => 'Instruments', 'sub' => [
                ['codigo' => '31-10', 'descripcion' => 'Indicating'],
            ]],
            ['codigo' => '32', 'descripcion' => 'Landing Gear', 'sub' => [
                ['codigo' => '32-10', 'descripcion' => 'Main Gear', 'ciclos' => 200],
                ['codigo' => '32-20', 'descripcion' => 'Nose Gear'],
                ['codigo' => '32-30', 'descripcion' => 'Brakes', 'ciclos' => 100],
                ['codigo' => '32-40', 'descripcion' => 'Wheels', 'ciclos' => 150],
            ]],
            ['codigo' => '33', 'descripcion' => 'Lights', 'sub' => [
                ['codigo' => '33-10', 'descripcion' => 'Interior'],
                ['codigo' => '33-20', 'descripcion' => 'Exterior'],
            ]],
            ['codigo' => '34', 'descripcion' => 'Navigation', 'sub' => [
                ['codigo' => '34-20', 'descripcion' => 'Radio Navigation'],
            ]],
            ['codigo' => '35', 'descripcion' => 'Oxygen', 'sub' => [
                ['codigo' => '35-10', 'descripcion' => 'Crew'],
                ['codigo' => '35-20', 'descripcion' => 'Passenger'],
            ]],
            ['codigo' => '36', 'descripcion' => 'Pneumatic', 'sub' => [
                ['codigo' => '36-10', 'descripcion' => 'Distribution'],
            ]],
            ['codigo' => '38', 'descripcion' => 'Water/Waste', 'sub' => [
                ['codigo' => '38-10', 'descripcion' => 'Water'],
                ['codigo' => '38-20', 'descripcion' => 'Waste'],
            ]],
            ['codigo' => '45', 'descripcion' => 'Central Maintenance System', 'sub' => [
                ['codigo' => '45-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '46', 'descripcion' => 'Information Systems', 'sub' => [
                ['codigo' => '46-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '47', 'descripcion' => 'Inert Gas System', 'sub' => [
                ['codigo' => '47-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '49', 'descripcion' => 'APU', 'sub' => [
                ['codigo' => '49-10', 'descripcion' => 'Power Section', 'horas' => 300],
            ]],

            // 🔹 ATA 51-57 STRUCTURES
            ['codigo' => '51', 'descripcion' => 'Standard Practices Structures', 'sub' => [
                ['codigo' => '51-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '52', 'descripcion' => 'Doors', 'sub' => [
                ['codigo' => '52-10', 'descripcion' => 'Passenger'],
                ['codigo' => '52-20', 'descripcion' => 'Cargo'],
            ]],
            ['codigo' => '53', 'descripcion' => 'Fuselage', 'sub' => [
                ['codigo' => '53-10', 'descripcion' => 'Structure', 'horas' => 1000, 'tipo' => 'D'],
            ]],
            ['codigo' => '54', 'descripcion' => 'Nacelles/Pylons', 'sub' => [
                ['codigo' => '54-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '55', 'descripcion' => 'Stabilizers', 'sub' => [
                ['codigo' => '55-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '56', 'descripcion' => 'Windows', 'sub' => [
                ['codigo' => '56-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '57', 'descripcion' => 'Wings', 'sub' => [
                ['codigo' => '57-10', 'descripcion' => 'Structure'],
            ]],

            // 🔹 ATA 61-80 POWERPLANT
            ['codigo' => '61', 'descripcion' => 'Propellers', 'sub' => [
                ['codigo' => '61-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '70', 'descripcion' => 'Standard Practices Engine', 'sub' => [
                ['codigo' => '70-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '71', 'descripcion' => 'Power Plant', 'sub' => [
                ['codigo' => '71-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '72', 'descripcion' => 'Engine', 'sub' => [
                ['codigo' => '72-00', 'descripcion' => 'General', 'horas' => 500, 'tipo' => 'C'],
            ]],
            ['codigo' => '73', 'descripcion' => 'Engine Fuel', 'sub' => [
                ['codigo' => '73-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '74', 'descripcion' => 'Ignition', 'sub' => [
                ['codigo' => '74-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '75', 'descripcion' => 'Air', 'sub' => [
                ['codigo' => '75-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '76', 'descripcion' => 'Engine Controls', 'sub' => [
                ['codigo' => '76-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '77', 'descripcion' => 'Engine Indicating', 'sub' => [
                ['codigo' => '77-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '78', 'descripcion' => 'Exhaust', 'sub' => [
                ['codigo' => '78-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '79', 'descripcion' => 'Oil', 'sub' => [
                ['codigo' => '79-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '80', 'descripcion' => 'Starting', 'sub' => [
                ['codigo' => '80-00', 'descripcion' => 'General'],
            ]],
            ['codigo' => '100', 'descripcion' => 'Documentacion Tecnica ATA 100', 'sub' => [
                ['codigo' => '100-00', 'descripcion' => 'General'],
                ['codigo' => '100-10', 'descripcion' => 'Manual de mantenimiento'],
                ['codigo' => '100-20', 'descripcion' => 'Procedimientos e inspecciones'],
            ]],

        ];

        foreach ($atas as $ataData) {

            $chapter = AtaChapter::updateOrCreate(
                ['codigo' => $ataData['codigo']],
                ['descripcion' => $ataData['descripcion']]
            );

            foreach ($ataData['sub'] as $sub) {

                AtaSubchapter::updateOrCreate(
                    ['codigo' => $sub['codigo']],
                    [
                        'ata_chapter_id' => $chapter->id,
                        'descripcion' => $sub['descripcion'],
                        'intervalo_horas' => $sub['horas'] ?? null,
                        'intervalo_ciclos' => $sub['ciclos'] ?? null,
                        'intervalo_dias' => $sub['dias'] ?? null,
                        'tipo_mantenimiento' => $sub['tipo'] ?? null,
                    ]
                );
            }
        }
    }
}
