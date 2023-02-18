<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\UserDelete;
use App\Models\Api\UserType;
use App\Models\User;
use App\Traits\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use MyHelper;

    public function list($type = NULL)
    {
        try {

            if (auth()->guard('api')->user()->user_type_id !== 1) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'msg' => 'You do not have access to perform this action',
                    'error' => 1,
                ], 403);
            }

            if ($type) {
                $user = UserDelete::select(
                    'users.id as id_user',
                    'user_deletes.id as id_user_delete',
                    'user_types.id as id_type',
                    'user_types.name as name_type',
                    'user_deletes.name',
                    'user_deletes.delete_by',
                    'user_deletes.created_at',
                    'user_deletes.updated_at',
                    'users.status as status_user'
                )
                    ->leftJoin('user_types', 'user_deletes.user_type_id', '=', 'user_types.id')
                    ->leftJoin('users', 'user_deletes.user_id', '=', 'users.id')
                    ->where('user_types.name', 'PELANGGAN')
                    ->orWhere('user_types.slug', 'pelanggan')
                    ->groupBy(
                        'users.id',
                        'user_deletes.id',
                        'user_types.id',
                        'user_types.name',
                        'user_deletes.name',
                        'user_deletes.delete_by',
                        'user_deletes.created_at',
                        'user_deletes.updated_at',
                        'users.status'
                    )
                    ->orderBy('user_deletes.created_at', 'DESC')
                    ->get();

                if (count($user) > 0) {
                    $x = 0;
                    foreach ($user as $u) {
                        if ($u->status_user == 1) {
                            $status = 'Not Active';
                        } else if ($u->status_user == 0) {
                            $status = 'Deleted';
                        } else {
                            $status = 'Active';
                        }
                        $user[$x]->status_user = $status;
                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $user,
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
                $user = User::select(
                    'users.id as id_user',
                    'user_types.id as id_type',
                    'user_types.name as name_type',
                    'users.name',
                    'users.email',
                    'users.pzn',
                    'users.slug',
                    'users.created_at',
                    'users.updated_at',
                    'users.deleted_at',
                    'users.status as status_user'
                )
                    ->leftJoin('user_types', 'users.user_type_id', '=', 'user_types.id')
                    ->where('user_types.name', 'PELANGGAN')
                    ->orWhere('user_types.slug', 'pelanggan')
                    ->groupBy(
                        'users.id',
                        'user_types.id',
                        'user_types.name',
                        'users.name',
                        'users.email',
                        'users.pzn',
                        'users.slug',
                        'users.created_at',
                        'users.updated_at',
                        'users.deleted_at',
                        'users.status'
                    )
                    ->orderBy('users.created_at', 'DESC')
                    ->get();

                if (count($user) > 0) {
                    $x = 0;
                    foreach ($user as $u) {
                        if ($u->status_user == 1) {
                            $status = 'Not Active';
                        } else if ($u->status_user == 0) {
                            $status = 'Deleted';
                        } else {
                            $status = 'Active';
                        }
                        $user[$x]->status_user = $status;
                        $x++;
                    }
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'msg' => 'Data Found',
                        'data' => $user,
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
    public function register()
    {
        $validation = Validator::make(request()->all(), [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|unique:users,name',
            'password' => 'required',
            'user_type_id' => 'required|integer'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $email = Str::lower(request()->email);
            $name = Str::upper(request()->name);
            $password = request()->password;
            $pzn = $this->encryptPin($password);
            $user_type_id = request()->user_type_id;
            $slug = Str::replace(" ", "_", Str::lower(request()->name));

            if (!preg_match("/^[a-z A-Z]*$/", $name)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only letters are allowed',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/^[0-9]*$/", $user_type_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_type = UserType::where('id', $user_type_id)->first();
            if (empty($cek_type)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User Type is not found',
                    'error' => 1
                ], 404);
            }

            $cek_user = User::where('slug', $slug)->first();
            if ($cek_user) {
                return response()->json([
                    'code' => 419,
                    'status' => false,
                    'msg' => 'Data User is available',
                    'data' => $cek_user,
                    'error' => 1
                ], 419);
            }

            $insert_user = User::create([
                'user_type_id' => $cek_type->id,
                'name' => $name,
                'password' => Hash::make($password),
                'email' => $email,
                'pzn' => $pzn,
                'slug' => $slug,
                'status' => 10
            ]);

            return response()->json([
                'code' => 201,
                'status' => true,
                'msg' => 'User created successfully',
                'data' => [
                    'user_type' => $cek_type,
                    'user' => $insert_user
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

    public function login()
    {
        $validation = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $email = Str::lower(request()->email);
            $password = request()->password;

            $cek_auth = User::where('email', $email)->first();
            if (empty($cek_auth)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            if (!Hash::check($password, $cek_auth->password)) {
                return response()->json([
                    'code' => 401,
                    'status' => false,
                    'msg' => 'Password wrong',
                    'error' => 1
                ], 401);
            }

            $credentials = request()->only('email', 'password');
            if (!$token = auth()->guard('api')->attempt($credentials)) {
                return response()->json([
                    'code' => 401,
                    'status' => false,
                    'msg' => 'Your email or password is wrong',
                    'error' => 1
                ], 401);
            }

            $expired = now();
            $expired = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($expired)));
            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Login Challange is successfully',
                'data' => [
                    'auth' => [
                        'type' => 'Bearer',
                        'token' => $token,
                        'expired' => $expired
                    ],
                    'user' => $cek_auth
                ],
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

    public function update()
    {
        $validation = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|email',
            'user_type_id' => 'required|integer'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $id = request()->id;
            $email = Str::lower(request()->email);
            $name = Str::upper(request()->name);
            $user_type_id = (int)request()->user_type_id;
            $slug = Str::replace(" ", "_", Str::lower(request()->name));

            if (!preg_match("/^[a-z A-Z]*$/", $name)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only letters are allowed',
                    'error' => 1
                ], 400);
            }

            if (!preg_match("/^[0-9]*$/", $user_type_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_type = UserType::where('id', $user_type_id)->first();
            if (empty($cek_type)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'Data User Type is not found',
                    'error' => 1
                ], 404);
            }

            $cek_auth = User::where('id', $id)->first();
            if (empty($cek_auth)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            if ($cek_auth->status == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User has been deleted',
                    'error' => 1
                ], 404);
            }

            if ($cek_auth->status == 1) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'msg' => 'User disabled',
                    'error' => 1
                ], 403);
            }

            $cek_auth->update([
                'email' => $email,
                'name' => $name,
                'slug' => $slug,
                'user_type_id' => $user_type_id,
                'updated_at' => now(),
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Data User updated is successfully',
                'data' => $cek_auth,
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

            $cek_auth = User::where('id', $id)->first();
            if (empty($cek_auth)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
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

            if ($cek_auth->status == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User has been deleted',
                    'error' => 1,
                ], 404);
            }

            $insert_delete = UserDelete::where('user_id', $id)->first();
            if (empty($insert_delete)) {
                UserDelete::create([
                    'user_id' => $cek_auth->id,
                    'user_type_id' => $cek_auth->user_type_id,
                    'name' => $cek_auth->name,
                    'delete_by' => auth()->guard('api')->user()->name,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $cek_auth->update([
                'status' => 0,
                'deleted_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Data User delete is successfully',
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

    public function changePassword()
    {
        $validation = Validator::make(request()->all(), [
            'id' => 'required|integer',
            'old_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $id = request()->id;
            $old_password = request()->old_password;
            $new_password = request()->new_password;
            $pzn = $this->encryptPin($new_password);

            $cek_auth = User::where('id', $id)->first();
            if (empty($cek_auth)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            if (!Hash::check($old_password, $cek_auth->password)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Wrong password',
                    'error' => 1
                ], 400);
            }

            if ($cek_auth->status == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User has been deleted',
                    'error' => 1
                ], 404);
            }

            if ($cek_auth->status == 1) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'msg' => 'User disabled',
                    'error' => 1
                ], 403);
            }

            $cek_auth->update([
                'password' => Hash::make($new_password),
                'pzn' => $pzn,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Password updated is successfully',
                'data' => $cek_auth,
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

    public function checkPassword()
    {
        $validation = Validator::make(request()->all(), [
            'value_id_or_email' => 'required',
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $value_id_or_email = request()->value_id_or_email;

            $cek_auth = User::where('id', $value_id_or_email)->orWhere('email', $value_id_or_email)->first();
            if (empty($cek_auth)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            if ($cek_auth->status == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User has been deleted',
                    'error' => 1
                ], 404);
            }

            if ($cek_auth->status == 1) {
                return response()->json([
                    'code' => 403,
                    'status' => false,
                    'msg' => 'User disabled',
                    'error' => 1
                ], 403);
            }

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Password check results',
                'data' => [
                    'password' => $this->decryptPin($cek_auth->pzn),
                    'user' => $cek_auth
                ],
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

    public function updateStatus()
    {
        $validation = Validator::make(request()->all(), [
            'value_id_or_email' => 'required',
            'status' => 'required|integer'
        ]);

        if ($validation->fails()) return response()->json($validation->errors(), 422);

        try {
            $value_id_or_email = request()->value_id_or_email;
            $status = request()->status;

            $cek_auth = User::where('id', $value_id_or_email)->orWhere('email', $value_id_or_email)->first();
            if (empty($cek_auth)) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User not found',
                    'error' => 1
                ], 404);
            }

            if ($cek_auth->status == 0) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'msg' => 'User has been deleted',
                    'error' => 1
                ], 404);
            }

            if (!preg_match("/^[0-9]*$/", $status)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'msg' => 'Input only numbers are allowed',
                    'error' => 1
                ], 400);
            }

            $cek_auth->update([
                'status' => $status,
                'updated_at' => now()
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'msg' => 'Status update successfully',
                'data' => $cek_auth,
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
