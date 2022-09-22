<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\User;

class UserController extends Controller
{
    public function getAll($request, $response, $args)
    {
        $users = User::with('permissions', 'permissions.role')->get();
        
        $data = json_encode($users, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function getById($request, $response, $args)
    {
        $user = User::with('permissions', 'permissions.role')->find($args['id']);

        $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        var_dump($parsedBody);

        $uploadedFiles = $request->getUploadedFiles();
        var_dump($uploadedFiles);

        // $user = User::find($args['id']);
    }

    public function update($request, $response, $args)
    {
        try {
            $parsedBody = getParsedPutBody($request);
            var_dump($parsedBody);
    
            var_dump($_FILES);
    
            $uploadDir = APP_ROOT_DIR . 'uploads/avatars/';
    
            foreach($_FILES as $file) {
                $target_file = $uploadDir . basename($file['name']);

                /** Write file to root directory */
                // if (file_put_contents($filename, $body)) {

                // }

                // if (move_uploaded_file($file['tmp_name'], $target_file)) {
                //     echo 'Success'
                // }
            }
    
            // $user = User::find($args['id']);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
