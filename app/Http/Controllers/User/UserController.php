<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\UserTmp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class UserController extends BaseController
{
    public function sendMailRegister(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|max:50|email|unique:users',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            //get User by email and create token
            $tokenArr[0] = $request->email;
            $email_verify_token_expiration = Carbon::now()->addDays(1);
            $tokenArr[1] = $email_verify_token_expiration;

            $token = Crypt::encrypt($tokenArr);

            DB::beginTransaction();

            // Find token in PasswordReset table
            $userExists = UserTmp::where('email', $request->email)->first();
            if ($userExists) {
                $userExists->delete();
            }

            $passwordReset = UserTmp::updateOrCreate([
                'email' => $request->email,
                'token' => $token,
                'expiration' => $email_verify_token_expiration,
            ]);

            DB::commit();
            $url = config('app.url_fe').'/'.$token.'?email='. $request->email;

            if ($passwordReset) {

                $data = array(
                    'url' => $url,
                    'email' => $request->email,
                );
                $this->sendEmail('emails.emailRegistration', $request->email, null, $data, 'User registration email');
            }

            return $this->sendSuccessResponse(null, 'success');

        } catch (\Exception$e) {

            DB::rollBack();
            logger( $e->getMessage());

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function create(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($request->token);
            $email = $decryptToken[0];

            $userExists = UserTmp::where('token', $request->token)->first();

            if($userExists) {

                if ($time_now > Carbon::parse($userExists->expiration)->toDateTimeString()) {

                    return $this->sendError(__('app.token_invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {

                return $this->sendError(__('app.token_invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $expire_at = Carbon::parse($decryptToken[1])->toDateTimeString();

            if ($time_now > $expire_at) {
               
                return $this->sendError(__('app.token_invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->sendSuccessResponse(null, 'success');

        } catch (\Exception$e) {

            DB::rollBack();
            logger( $e->getMessage());

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|max:50|email|unique:users',
                'token' => 'required',
                'name' => 'required',
                'phone' => ['required', 'string', 'regex:/(0)[0-9]{9}/'],
                'address' => 'required',
                'password' => 'required|string|confirmed|min:8|max:20|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($request->token);
            $email = $decryptToken[0];

            $userExists = UserTmp::where('token', $request->token)->first();

            if($userExists) {

                if ($time_now > Carbon::parse($userExists->expiration)->toDateTimeString()) {

                    return $this->sendError(__('app.token_invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {

                return $this->sendError(__('app.token_invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $expire_at = Carbon::parse($decryptToken[1])->toDateTimeString();

            if ($time_now > $expire_at) {
               
                return $this->sendError(__('app.token_invalid'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            DB::beginTransaction();

            $user = User::updateOrCreate([
                'email' => $request->email,
                'name' => $request->name,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $userExists->delete();

            DB::commit();

            $token = auth('users')->login($user);
            $data = [
                'access_token' => $token,
                'token_type' => 'bearer'
            ];
            return $this->sendSuccessResponse($data);

        } catch (\Exception $e) {

            DB::rollBack();
            logger( $e->getMessage());

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
