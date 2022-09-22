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
        try {
            $parsedBody = $request->getParsedBody();
            var_dump($parsedBody);

            $uploadedFiles = $request->getUploadedFiles();
            var_dump($uploadedFiles);

            // $user = new User;
        } catch (\Exception $ex) {
            $data = json_encode(['message' => $ex->getMessage()], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

            return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write($data);
        }
    }

    public function update($request, $response, $args)
    {
        try {
            $parsedBody = getParsedPutBody($request);

            /** ================ Upload file section ================ */
            // $uploadDir = APP_PUBLIC_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';

            // foreach($_FILES as $file) {
            //     $target_file = $uploadDir . DIRECTORY_SEPARATOR . basename($file['name']);

            //     if(file_exists($file['tmp_name'])) {
            //         if (copy($file['tmp_name'], $target_file)) {
            //             echo 'Success';
            //         }
            //     }
            // }
            /** ================ Upload file section ================ */

            $user = User::find($args['id']);

            $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

            return $response->withStatus(200)
                            ->withHeader("Content-Type", "application/json")
                            ->write($data);
        } catch (\Exception $ex) {
            $data = json_encode(['message' => $ex->getMessage()], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

            return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write($data);
        }
    }
}
