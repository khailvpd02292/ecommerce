<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends BaseController
{

    public function login(Request $request) {

        try {

            $credentials = $request->only('email', 'password');

            $validator = Validator::make($credentials, [
                'email' => 'required|max:50|email|unique:users',
                'password'=> 'required|min:8|max:20',
            ]);
     
            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (! $token = auth('users')->attempt($credentials)) {
                return $this->sendError(__('app.login_failed'), Response::HTTP_BAD_REQUEST);
            } 

            $data = [
                'access_token' => $token,
                'token_type' => 'bearer'
            ];
            return $this->sendSuccessResponse($data);

        } catch (JWTException $e) {
            
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function logout() {

        try {
            auth('users')->logout();
            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => 'Logout', 'attribute' => __('app.user')]));
        } catch (JWTException $th) {

            return $this->sendError(__('Token invalid'), Response::HTTP_UNAUTHORIZED);
        }
       
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->sendSuccessResponse(auth('users')->user());
    }

}
