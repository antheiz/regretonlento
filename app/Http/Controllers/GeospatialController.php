<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GeospatialController extends Controller
{
    public function getDistricts()
    {
        $query = "
        SELECT d.id, d.nama_distrik, ST_AsGeoJSON(d.peta_distrik) AS geojson, SUM(de.jumlah_penduduk) AS total_penduduk
        FROM distriks d
        LEFT JOIN desas de ON d.id = de.id
        GROUP BY d.id, d.nama_distrik, d.peta_distrik
    ";
        $districts = DB::select($query);
        return response()->json($this->createGeoJSON($districts, true));
    }

    public function getVillages(Request $request)
    {
        $districtId = $request->query('districtId');
        $query = "SELECT id, distrik_id, nama_desa, ST_AsGeoJSON(peta_desa) AS geojson FROM desas";
        if ($districtId) {
            $query .= " WHERE distrik_id = ?";
            $villages = DB::select($query, [$districtId]);
        } else {
            $villages = DB::select($query);
        }
        return response()->json($this->createGeoJSON($villages, false));
    }


    private function createGeoJSON($data, $isDistrict = false)
    {
        $features = array_map(function ($item) use ($isDistrict) {
            $properties = [
                'name' => $item->nama_distrik ?? $item->nama_desa,
            ];

            if ($isDistrict) {
                $properties['id'] = $item->id;
                $properties['total_penduduk'] = $item->total_penduduk; // Menambahkan total penduduk
            } else {
                $properties['id'] = $item->id;
                $properties['distrik_id'] = $item->distrik_id;
            }

            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geojson),
                'properties' => $properties,
            ];
        }, $data);

        return ['type' => 'FeatureCollection', 'features' => $features];
    }




    public function index()
    {
        return view('index');
    }
}
