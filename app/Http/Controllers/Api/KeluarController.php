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

class KeluarController extends Controller
{
    use MyHelper;

    public function list($id = NULL)
    {
        try {

            if ($id) {
                $Keluar = Keluar::where('user_id', $id)->first();

                if ($Keluar) {
                    // USER //
                    $user = User::where('id', $Keluar->user_id)->where('status', 10)->first();
                    $Keluar->user_id = $user ? $user->name : 'Unknown';
                    $Keluar->time_ago = $this->timeAgo($Keluar->created_at);
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $Keluar,
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
                $Keluar = Keluar::get();

                if (count($Keluar) > 0) {
                    $x = 0;
                    foreach ($Keluar as $u) {
                        // USER //
                        $user = User::where('id', $u->user_id)->where('status', 10)->first();

                        $Keluar[$x]->user_id = $user ? $user->name : 'Unknown';
                        $Keluar[$x]->time_ago = $this->timeAgo($u->created_at);

                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $Keluar,
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

            $cek_Keluar = Keluar::where('user_id', $user_id)->first();
            if ($cek_Keluar) {
                return response()->json([
                    'code' => 419,
                    'status' => false,
                    'msg' => 'Data is available',
                    'data' => [
                        'Keluar' => $cek_Keluar,
                        'user' => $cek_user
                    ],
                    'error' => 1
                ], 419);
            }

            $cek_gabung = Gabung::where('user_id', $user_id)->first();
            if ($cek_gabung) {
                $cek_gabung->delete();
            }

            $insert_Keluar = Keluar::create([
                'user_id' => $user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Data Keluars created successfully',
                'data' => [
                    'Keluar' => $insert_Keluar,
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

            $cek_Keluar = Keluar::where('id', $id)->first();
            if (empty($cek_Keluar)) {
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

            $cek_Keluar->delete();

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Data Keluar delete is successfully',
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
