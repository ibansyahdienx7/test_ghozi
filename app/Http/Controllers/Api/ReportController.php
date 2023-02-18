<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Report;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportController extends Controller
{
    use MyHelper;

    public function list($id = NULL)
    {
        try {

            if ($id) {
                $Report = Report::where('user_id', $id)->orderBy('created_at', 'DESC')->get();

                if (count($Report) > 0) {
                    $x = 0;
                    foreach ($Report as $u) {
                        // USER //
                        $user = User::where('id', $u->user_id)->where('status', 10)->first();

                        if ($u->report_id_user) {
                            // REPORT ID USER //
                            $report_user = User::where('id', $u->report_id_user)->where('status', 10)->first();
                            $Report[$x]->report_user = $report_user ? $report_user->name : 'Unknown';
                        } else {
                            $Report[$x]->report_user = NULL;
                        }

                        if ($u->status == 10) {
                            $status = 'Created';
                        } else if ($u->status == 1) {
                            $status = 'Progress';
                        } else {
                            $status = 'Close';
                        }

                        $Report[$x]->status = $status;
                        $Report[$x]->user_id = $user ? $user->name : 'Unknown';
                        $Report[$x]->time_ago = $this->timeAgo($u->created_at);

                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $Report,
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
                $Report = Report::orderBy('created_at', 'DESC')->get();

                if (count($Report) > 0) {
                    $x = 0;
                    foreach ($Report as $u) {
                        // USER //
                        $user = User::where('id', $u->user_id)->where('status', 10)->first();

                        if ($u->report_id_user) {
                            // REPORT ID USER //
                            $report_user = User::where('id', $u->report_id_user)->where('status', 10)->first();
                            $Report[$x]->report_user = $report_user ? $report_user->name : 'Unknown';
                        } else {
                            $Report[$x]->report_user = NULL;
                        }

                        if ($u->status == 10) {
                            $status = 'Created';
                        } else if ($u->status == 1) {
                            $status = 'Progress';
                        } else {
                            $status = 'Close';
                        }

                        $Report[$x]->status = $status;
                        $Report[$x]->user_id = $user ? $user->name : 'Unknown';
                        $Report[$x]->time_ago = $this->timeAgo($u->created_at);

                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $Report,
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
            'type_report' => 'required|string',
            'message' => 'required'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $user_id = request()->user_id;
            $type_report = request()->type_report;
            $message = request()->message;
            $report_id_user = request()->report_id_user;

            if ($type_report !== 'user' && $type_report !== 'bug') {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Please input type report Bug / User in lowercase',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/^[0-9]*$/", $user_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/^[a-z]*$/", $type_report)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only lowercase are allowed',
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

            if ($report_id_user) {
                $cek_user_report = User::where('id', $report_id_user)->first();
                if (empty($cek_user_report)) {
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'msg' => 'Data User For Report User not found',
                        'error' => 1
                    ], 404);
                }
            }

            $insert_Report = Report::create([
                'user_id' => $user_id,
                'report_id_user' => $report_id_user ? $report_id_user : NULL,
                'type_report' => $type_report,
                'message' => $message,
                'status' => 10,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Data Reports created successfully',
                'data' => [
                    'Report' => $insert_Report,
                    'user' => $cek_user,
                    'user_report' => $report_id_user ? $cek_user_report : NULL
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

    public function updateStatus()
    {
        $validation = Validator::make(request()->all(), [
            'status' => 'required|integer',
            'id' => 'required|integer'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $id = request()->id;
            $status = request()->status;

            if (auth()->guard('api')->user()->user_type_id !== 1) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'msg' => 'You do not have access to perform this action',
                    'error' => 1,
                ], 403);
            }

            if (!preg_match("/^[0-9]*$/", $status) || !preg_match("/^[0-9]*$/", $id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_report = Report::where('id', $id)->first();
            if (empty($cek_report)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data not found',
                    'error' => 1
                ], 404);
            }
            $cek_report->update([
                'status' => $status,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'Data Reports update successfully',
                'data' => $cek_report,
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

            $cek_Report = Report::where('id', $id)->first();
            if (empty($cek_Report)) {
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

            $cek_Report->delete();

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Data Report delete is successfully',
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
