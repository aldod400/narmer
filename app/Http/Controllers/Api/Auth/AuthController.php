<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\UpdadeProfileRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->identifier, $request->password, $request->fcm_token ?? null);
        if (!$result['success']) {
            return Response::api($result['message'], 400, false, 400);
        }
        return Response::api(__('message.login_success'), 200, true, null, [
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'image' => $request->image,
            'user_type' => $request->user_type,
            'fcm_token' => $request->fcm_token ?? null,
        ]);
        return Response::api(__('message.register_success'), 200, true, null, $result);
    }

    public function profile()
    {
        $result = $this->authService->profile();
        return Response::api(__('message.Success'), 200, true, null, $result);
    }

    public function updateProfile(UpdadeProfileRequest $request)
    {
        $result = $this->authService->updateProfile([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'image' => $request->image,
            'fcm_token' => $request->fcm_token,
            'password' => $request->password ?? null,
        ]);
        return Response::api(__('message.Success'), 200, true, null, $result);
    }
    public function deleteProfile()
    {
        $result = $this->authService->deleteProfile();
        return Response::api(__('message.Success'), 200, true, null);
    }
    public function saveFcmTokenToAdmin(Request $request)
    {
        $result = $this->authService->saveFcmTokenToAdmin($request->fcm_token, $request->user_id);

        if (!$result['success']) {
            return Response::api($result['message'], 400, false, 400);
        }

        return Response::api(__('message.Success'), 200, true, null, $result);
    }
}
