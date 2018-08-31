<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new User;
    }

    /**
     * 注册用户
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function regist(Request $req)
    {

        $mobile     = $req->get('mobile');
        $password   = $req->get('password');

        $userId = $this->userModel->registerUser([
            'mobile'    => $mobile,
            'name'      => $mobile,
            'password'  => bcrypt($password),
        ]);

        if (!$userId) return response()->json(Config::get('constants.REGIST_ERROR'));

        return response()->json(Config::get('constants.REGIST_SUCCESS'));
    }

    /**
     * 登录
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $req)
    {

        $mobile    = $req->get('mobile');
        $password  = $req->get('password');

        if ($token = Auth::guard('api')->attempt(['mobile' => $mobile, 'password' => $password])) {
            Log::info($mobile . ' login success');
            return response()->json(array_merge(
                    ['token' => 'bearer ' . $token],
                    Config::get('constants.LOGIN_SUCCESS'))
            );
        } else {
            return response()->json(Config::get('constants.LOGIN_ERROR'));
            Log::error($mobile . ' login error');
        }
    }

    /**
     * 登出
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $req)
    {
        Auth::guard('api')->logout();
        return response()->json(Config::get('constants.LOGIN_OUT'));
    }
}
