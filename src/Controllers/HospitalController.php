<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Hospital;
use App\Models\Changwat;
use App\Models\Amphur;
use App\Models\Tambon;

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
                            $q->whereIn('hospital_type_id', [3,5,7,9,13]);
                        })
                        ->when(!empty($type), function($q) use ($type) {
                            $q->where('hospital_type_id', $type);
                        })
                        ->get(['hospcode','name','hosptype','hospital_type_id','amppart','chwpart','hospital_phone']);
        
        $data = json_encode($hospitals, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $hospital = Hospital::where('hospcode', $args['id'])
                    ->get([
                        'hospcode','name','addrpart','moopart','tmbpart','amppart','chwpart',
                        'hosptype','hospital_type_id','hospital_phone','hospital_fax','area_code',
                        'province_name'
                    ])
                    ->first();
                    
        $data = json_encode($hospital, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write($data);
    }

    public function getInitForm($request, $response, $args)
    {
        $data = [
            'changwats'     => Changwat::all(),
            'amphurs'       => Amphur::all(),
            'tambons'       => Tambon::all(),
        ];

        return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
    }

    public function update($request, $response, $args)
    {
        try {
            $post = (array)$request->getParsedBody();

            $hospital = Hospital::where('hospcode', $args['id'])->first();
            $hospital->hospcode         = $post['hospcode'];
            $hospital->name             = $post['name'];
            $hospital->addrpart         = $post['addrpart'];
            $hospital->moopart          = $post['moopart'];
            $hospital->tmbpart          = $post['tmbpart'];
            $hospital->amppart          = $post['amppart'];
            $hospital->chwpart          = $post['chwpart'];
            $hospital->hospital_type_id = $post['hospital_type_id'];
            $hospital->hospital_phone   = $post['hospital_phone'];
            $hospital->hospital_fax     = $post['hospital_fax'];
            $hospital->area_code        = $post['area_code'];
            // $hospital->province_name    = $post['province_name'];

            if ($hospital->save()) {
                return $response->withStatus(200)
                                ->withHeader("Content-Type", "application/json")
                                ->write(json_encode([
                                    'status'    => 1,
                                    'message'   => 'Success',
                                    'hospital'  => $hospital
                                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response->withStatus(500)
                                ->withHeader("Content-Type", "application/json")
                                ->write(json_encode([
                                    'status'    => 0,
                                    'message'   => 'Failure',
                                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write(json_encode([
                                'status'    => 0,
                                'message'   => $ex->getMessage(),
                            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
