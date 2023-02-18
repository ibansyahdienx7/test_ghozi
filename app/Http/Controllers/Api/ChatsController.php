<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Chat;
use App\Models\Api\Gabung;
use App\Models\Api\UserType;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class ChatsController extends Controller
{
    use MyHelper;

    public function list($id = NULL)
    {
        try {

            if ($id) {
                $chat = Chat::where('user_id', $id)->get();

                if (count($chat) > 0) {
                    $x = 0;
                    foreach ($chat as $u) {
                        $cek_join = Gabung::where('user_id', $u->user_id)->first();
                        if (empty($cek_join)) {
                            return response()->json([
                                'code' => 400,
                                'status' => false,
                                'msg' => 'Please join first',
                                'error' => 1
                            ], 400);
                        }
                        // FROM //
                        $from = User::where('id', $u->user_id)->where('status', 10)->first();
                        // TO //
                        $to = User::where('id', $u->user_id_to)->where('status', 10)->first();
                        if ($u->status == 10) {
                            $status = 'Read';
                        } else if ($u->status == 0) {
                            $status = 'Unread';
                        }

                        $chat[$x]->user_id = $from ? $from->name : 'Unknown';
                        $chat[$x]->user_id_to = $to ? $to->name : 'Unknown';
                        $chat[$x]->time_ago = $this->timeAgo($u->created_at);
                        $chat[$x]->status = Str::upper($status);

                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $chat,
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
                $chat = Chat::get();

                if (count($chat) > 0) {
                    $x = 0;
                    foreach ($chat as $u) {
                        $cek_join = Gabung::where('user_id', $u->user_id)->first();
                        if (empty($cek_join)) {
                            return response()->json([
                                'code' => 400,
                                'status' => false,
                                'msg' => 'Please join first',
                                'error' => 1
                            ], 400);
                        }
                        // FROM //
                        $from = User::where('id', $u->user_id)->where('status', 10)->first();
                        // TO //
                        $to = User::where('id', $u->user_id_to)->where('status', 10)->first();
                        if ($u->status == 10) {
                            $status = 'Read';
                        } else if ($u->status == 0) {
                            $status = 'Unread';
                        }

                        $chat[$x]->user_id = $from ? $from->name : 'Unknown';
                        $chat[$x]->user_id_to = $to ? $to->name : 'Unknown';
                        $chat[$x]->time_ago = $this->timeAgo($u->created_at);
                        $chat[$x]->status = Str::upper($status);
                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $chat,
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
            'user_id' => 'required|integer',
            'user_id_to' => 'required|integer',
            'message' => 'required',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $user_id = request()->user_id;
            $user_id_to = request()->user_id_to;
            $msg = request()->message;

            if (!preg_match("/^[0-9]*$/", $user_id) || !preg_match("/^[0-9]*$/", $user_id_to)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_join = Gabung::where('user_id', $user_id)->first();
            if (empty($cek_join)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Please join first',
                    'error' => 1
                ], 400);
            }

            if ($user_id == $user_id_to) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'You cannot chat with yourself',
                    'error' => 1
                ], 400);
            }

            $cek_user_pelanggan = User::where('id', $user_id)->where('status', 10)->first();
            if (empty($cek_user_pelanggan)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data Not Found',
                    'error' => 1
                ], 404);
            }

            $cek_type_pelanggan = UserType::where('id', $cek_user_pelanggan->user_type_id)->first();
            if (empty($cek_type_pelanggan)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User Type Not Found',
                    'error' => 1
                ], 404);
            }

            $cek_user_staff = User::where('id', $user_id_to)->where('status', 10)->first();
            if (empty($cek_user_staff)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data Not Found',
                    'error' => 1
                ], 404);
            }

            $cek_type_staff = UserType::where('id', $cek_user_staff->user_type_id)->first();
            if (empty($cek_type_staff)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User Type Not Found',
                    'error' => 1
                ], 404);
            }

            if ($cek_type_pelanggan->id !== $cek_type_staff->id) {
                if ($cek_type_pelanggan->name == 'PELANGGAN') {
                    return response()->json([
                        'code' => 403,
                        'status' => false,
                        'msg' => 'You are not allowed to perform this action',
                        'error' => 1
                    ], 403);
                }
            }

            $insert_chat = Chat::create([
                'user_id' => $user_id,
                'user_id_to' => $user_id_to,
                'message' => $msg,
                'status' => 0
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Chat sent successfully',
                'data' => $insert_chat,
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

    public function deleteByUserID()
    {
        $validation = Validator::make(request()->all(), [
            'user_id' => 'required|integer',
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

            $cek_join = Gabung::where('user_id', $user_id)->first();
            if (empty($cek_join)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Please join first',
                    'error' => 1
                ], 400);
            }

            $cek_chat = Chat::where('user_id', $user_id)->get();

            if (count($cek_chat) > 0) {
                Chat::where('user_id', $user_id)->delete();

                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'msg' => 'Chat User ID ' . $user_id . ' deleted successfully',
                    'error' => 0
                ], 200);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Chat not found',
                    'error' => 1
                ], 404);
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

    public function deleteByUserTo()
    {
        $validation = Validator::make(request()->all(), [
            'user_id' => 'required|integer',
            'user_id_to' => 'required|integer',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $user_id = request()->user_id;
            $user_id_to = request()->user_id_to;

            if (!preg_match("/^[0-9]*$/", $user_id_to) || !preg_match("/^[0-9]*$/", $user_id_to)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_join = Gabung::where('user_id', $user_id)->first();
            if (empty($cek_join)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Please join first',
                    'error' => 1
                ], 400);
            }

            $cek_chat = Chat::where('user_id', $user_id)->where('user_id_to', $user_id_to)->get();

            if (count($cek_chat) > 0) {
                Chat::where('user_id', $user_id)->where('user_id_to', $user_id_to)->delete();

                return response()->json([
                    'code' => 200,
                    'status' => true,
                    'msg' => 'Chat User ID To ' . $user_id_to . ' deleted successfully',
                    'error' => 0
                ], 200);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Chat not found',
                    'error' => 1
                ], 404);
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
}
