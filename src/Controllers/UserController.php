<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\User;
use App\Models\UserPermission;

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
        $user = User::with('hospital','permissions','permissions.role')->find($args['id']);

        $data = json_encode($user, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE);

        return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write($data);
    }

    public function store($request, $response, $args)
    {
        try {
            $post = $request->getParsedBody();
            // $uploadedFiles = $request->getUploadedFiles();
            // var_dump($uploadedFiles);

            $user = new User;
            $user->fullname     = $post['fullname'];
            $user->email        = $post['email'];
            $user->username     = $post['username'];
            $user->password     = password_hash($post['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $user->hospcode     = $post['hospcode'];
            // $user->position     = $post['position'];

            if ($user->save()) {
                $permission = new UserPermission;
                $permission->user_id    = $user->id;
                $permission->role       = $post['position'];
                $permission->save();

                return $response->withStatus(200)
                            ->withHeader("Content-Type", "application/json")
                            ->write(json_encode([
                                'status'    => 1,
                                'message'   => 'Success',
                                'user'      => $user
                            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write(json_encode([
                                'status'    => 0,
                                'message'   => 'Failure'
                            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write(json_encode([
                                'status'    => 0,
                                'message' => $ex->getMessage()
                            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }

    public function update($request, $response, $args)
    {
        try {
            // $post = getParsedPutBody($request);

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

            $post = $request->getParsedBody();

            $user = User::find($args['id']);
            $user->fullname     = $post['fullname'];
            $user->email        = $post['email'];
            $user->hospcode     = $post['hospcode'];

            if ($user->save()) {
                $permission = UserPermission::where('user_id', $args['id'])->first();
                $permission->role = $post['position'];
                $permission->save();

                return $response->withStatus(200)
                                ->withHeader("Content-Type", "application/json")
                                ->write(json_encode([
                                    'status'    => 1,
                                    'message'   => 'Success',
                                    'user'      => $user
                                ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            } else {
                return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write(json_encode([
                                'status'    => 0,
                                'message'   => 'Failure'
                            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
            }
        } catch (\Exception $ex) {
            return $response->withStatus(500)
                            ->withHeader("Content-Type", "application/json")
                            ->write(json_encode([
                                'status'    => 0,
                                'message' => $ex->getMessage()
                            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT |  JSON_UNESCAPED_UNICODE));
        }
    }
}
