<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class LoginController extends BaseController
{

    public function login(Request $request) {

        try {

            $credentials = $request->only('email', 'password');

            $validator = Validator::make($credentials, [
                'email' => 'required|max:50|email',
                'password'=> 'required|min:8|max:20',
            ]);
     
            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (! $token = auth('admins')->attempt($credentials)) {
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
            auth('admins')->logout();

            return $this->sendSuccessResponse(null, __('app.action_success', ['action' => 'Logout', 'attribute' => __('app.user')]));
        } catch (JWTException $th) {

            return $this->sendError(__('Token invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->sendSuccessResponse(auth('admins')->user());
    }

}
