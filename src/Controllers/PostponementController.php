<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Postponement;

class PostponementController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $postponements = Postponement::all();
        
        $data = json_encode($postponements, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $postponement = Postponement::find($args['id'])->first();
                    
        $data = json_encode($postponement, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getByAppoint($request, $response, $args)
    {
        $postponement = Postponement::where('appoint_id', $args['appointId'])->first();
                    
        $data = json_encode($postponement, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }
}
