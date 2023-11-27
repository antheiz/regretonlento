<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GeospatialController extends Controller
{
    public function getDistricts()
    {
        $districts = DB::select("SELECT distrik_id, nama_distrik, ST_AsGeoJSON(peta_distrik) AS geojson FROM distriks");
        return response()->json($this->createGeoJSON($districts));
    }

    public function getVillages(Request $request)
    {
        $districtId = $request->query('districtId');
        $query = "SELECT desa_id, distrik_id, nama_desa, ST_AsGeoJSON(peta_desa) AS geojson FROM desas";
        if ($districtId) {
            $query .= " WHERE distrik_id = ?";
            $villages = DB::select($query, [$districtId]);
        } else {
            $villages = DB::select($query);
        }
        return response()->json($this->createGeoJSON($villages));
    }

    private function createGeoJSON($data)
    {
        $features = array_map(function ($item) {
            return [
                'type' => 'Feature',
                'geometry' => json_decode($item->geojson),
                'properties' => [
                    'id' => $item->distrik_id ?? $item->desa_id,
                    'name' => $item->nama_distrik ?? $item->nama_desa,
                ],
            ];
        }, $data);
        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    public function index()
    {
        return view('index');
    }
}
