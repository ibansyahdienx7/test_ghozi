<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Gabung;
use App\Models\Api\Keluar;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class GabungsController extends Controller
{
    use MyHelper;

    public function list($id = NULL)
    {
        try {

            if ($id) {
                $gabung = Gabung::where('user_id', $id)->first();

                if ($gabung) {
                    // USER //
                    $user = User::where('id', $gabung->user_id)->where('status', 10)->first();
                    $gabung->user_id = $user ? $user->name : 'Unknown';
                    $gabung->time_ago = $this->timeAgo($gabung->created_at);
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $gabung,
                        'error' => 0
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Data not found',
                        'error' => 1
                    ], 404);
                }
            } else {
                $gabung = Gabung::get();

                if (count($gabung) > 0) {
                    $x = 0;
                    foreach ($gabung as $u) {
                        // USER //
                        $user = User::where('id', $u->user_id)->where('status', 10)->first();

                        $gabung[$x]->user_id = $user ? $user->name : 'Unknown';
                        $gabung[$x]->time_ago = $this->timeAgo($u->created_at);

                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $gabung,
                        'error' => 0
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Data not found',
                        'error' => 1
                    ], 404);
                }
            }
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
            'user_id' => 'required|integer'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $user_id = request()->user_id;

            if (!preg_match("/^[0-9]*$/", $user_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_user = User::where('id', $user_id)->first();
            if (empty($cek_user)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User not found',
                    'error' => 1
                ], 404);
            }

            $cek_gabung = Gabung::where('user_id', $user_id)->first();
            if ($cek_gabung) {
                return response()->json([
                    'code' => 419,
                    'status' => false,
                    'msg' => 'Data is available',
                    'data' => $cek_gabung,
                    'error' => 1
                ], 419);
            }

            $cek_keluar = Keluar::where('user_id', $user_id)->first();
            if ($cek_keluar) {
                $cek_keluar->delete();
            }

            $insert_gabung = Gabung::create([
                'user_id' => $user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Data Gabungs created successfully',
                'data' => [
                    'gabung' => $insert_gabung,
                    'user' => $cek_user
                ],
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

    public function delete()
    {
        $validation = Validator::make(request()->all(), [
            'id' => 'required|integer',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $id = request()->id;

            $cek_gabung = Gabung::where('id', $id)->first();
            if (empty($cek_gabung)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data not found',
                    'error' => 1
                ], 404);
            }

            if (auth()->guard('api')->user()->user_type_id !== 1) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'msg' => 'You do not have access to perform this action',
                    'error' => 1,
                ], 403);
            }

            $cek_gabung->delete();

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Data Gabung delete is successfully',
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
