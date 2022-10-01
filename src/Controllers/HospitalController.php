<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Hospital;

class HospitalController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $changwat   = empty($request->getParam('changwat')) ? '30' : $request->getParam('changwat');
        $amphur     = empty($request->getParam('amphur')) ? '01' : $request->getParam('amphur');
        $type       = $request->getParam('type');

        $hospitals = Hospital::with('changwat')
                        ->with(['amphur' => function($query) use ($changwat) {
                            $query->where('chw_id', $changwat);
                        }])
                        ->where('chwpart', $changwat)
                        ->where('amppart', $amphur)
                        ->when(empty($type), function($q) {
                            $q->whereIn('hospital_type_id', [2, 3, 7]);
                        })
                        ->when(!empty($type), function($q) use ($type) {
                            $q->where('hospital_type_id', $type);
                        })
                        ->get(['hospcode', 'name', 'hosptype', 'amppart', 'chwpart']);
        
        $data = json_encode($hospitals, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
    
    public function getById($request, $response, $args)
    {
        $hospital = Hospital::where('hospcode', $args['id'])
                    ->get(['hospcode', 'name', 'hosptype', 'amppart', 'chwpart'])
                    ->first();
                    
        $data = json_encode($hospital, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
}
