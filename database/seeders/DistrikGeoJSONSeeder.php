<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrikGeoJSONSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Membaca file JSON
        $json = file_get_contents(storage_path('app/geojson/distrik.geo.json'));
        $data = json_decode($json, true);

        // Iterasi setiap 'feature' dalam file JSON
        foreach ($data['features'] as $feature) {
            $geometry = json_encode($feature['geometry']); // Konversi geometry ke JSON string
            $properties = $feature['properties'];

            // Mengakses kode_kec, nama dalam 'properties'
            $kode_kec = $properties['kode_kec'];
            $nama = $properties['nama'];

            // Menyimpan data ke database
            DB::table('distriks')->insert([
                'id' => $kode_kec,
                'nama_distrik' => $nama,
                'peta_distrik' => DB::raw("ST_GeomFromGeoJSON('$geometry')"),
                'created_at' => now(),
            ]);
        }
    }
}
