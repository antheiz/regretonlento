<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesaGeoJSONSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Membaca file JSON
        $json = file_get_contents(storage_path('app/geojson/kampung.geo.json'));
        $data = json_decode($json, true);

        // Iterasi setiap 'feature' dalam file JSON
        foreach ($data['features'] as $feature) {
            $geometry = json_encode($feature['geometry']); // Konversi geometry ke JSON string

            // Mengakses kode_kec, nama dalam 'properties'
            $kode_kec = $feature['kode_kec'];
            $nama = $feature['nama'];

            // Menyimpan data ke database
            DB::table('desas')->insert([
                'distrik_id' => $kode_kec,
                'nama_desa' => $nama,
                'peta_desa' => DB::raw("ST_GeomFromGeoJSON('$geometry')"),
                'created_at' => now(),
            ]);
        }
    }
}
