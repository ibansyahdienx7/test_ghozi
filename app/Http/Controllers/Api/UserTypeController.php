<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\UserType;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class UserTypeController extends Controller
{
    use MyHelper;

    public function list($id = null)
    {
        try {
            if ($id) {
                $userType = UserType::where('id', $id)->first();
                if (empty($userType)) {
                    return response()
                        ->json([
                            'code' => 404,
                            'status' => false,
                            'msg' => 'User Type not found',
                            'error' => 1
                        ], 404);
                }
            } else {
                $userType = UserType::get();

                if (count($userType) == 0) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'User Type is empty',
                        'error' => 1
                    ], 404);
                }
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'User Type is already',
                'data' => $userType,
                'error' => 0
            ], 200);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }

    public function store()
    {
        $validation = Validator::make(request()->all(), [
            'name' => 'required|string|unique:user_types,name'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $name = Str::upper(request()->name);
            $slug = Str::replace(" ", "_", Str::lower(request()->name));

            if (!preg_match("/^[a-zA-Z]*$/", $name)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only letters are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_type = UserType::where('slug', $slug)->first();
            if ($cek_type) {
                return response()->json([
                    'code' => 419,
                    'status' => false,
                    'msg' => 'Data User Type is available',
                    'data' => $cek_type,
                    'error' => 1
                ], 419);
            }

            $insert_user = UserType::create([
                'name' => $name,
                'slug' => $slug
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'User Type created successfully',
                'data' => $insert_user,
                'error' => 0,
            ], 201);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }

    public function update()
    {
        $validation = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'name' => 'required|string'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $name = Str::upper(request()->name);
            $slug = Str::replace(" ", "_", Str::lower(request()->name));
            $id = request()->id;

            if (!preg_match("/^[a-zA-Z]*$/", $name)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only letters are allowed',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/^[0-9]*$/", $id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_type = UserType::where('id', $id)->first();
            if (empty($cek_type)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User Type not found',
                    'error' => 1
                ], 404);
            }

            $cek_type->update([
                'name' => $name,
                'slug' => $slug
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'User Type updated successfully',
                'data' => $cek_type,
                'error' => 0,
            ], 200);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }

    public function delete()
    {
        $validation = Validator::make(request()->all(), [
            'id' => 'required|integer',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $id = request()->id;

            if (!preg_match("/^[0-9]*$/", $id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_type = UserType::where('id', $id)->first();
            if (empty($cek_type)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User Type not found',
                    'error' => 1
                ], 404);
            }

            $cek_type->delete();

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'User Type deleted successfully',
                'error' => 0,
            ], 200);
        } catch (HttpException $exception) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        }
    }
}
