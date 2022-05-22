<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserTmp;
use App\Models\PasswordReset;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class UserController extends BaseController
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

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
            $url = config('app.url_fe') . '/signup/input/' . $token . '?email=' . $request->email;

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
            logger($e->getMessage());

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function create($token, Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
            ]);

            if (empty($token)) {
                return $this->sendError(__('app.token_invalid'), Response::HTTP_BAD_REQUEST);
            }

            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($token);
            $email = $decryptToken[0];

            $userExists = UserTmp::where('token', $token)->first();

            if ($userExists) {

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
            logger($e->getMessage());

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function store($token, Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|max:50|email|unique:users',
                'name' => 'required',
                'phone' => ['required', 'string', 'regex:/(0)[0-9]{9}/'],
                'address' => 'required',
                'password' => 'required|string|min:8|max:20|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
                'avatar' => 'nullable|mimes:jpg,jpeg,png|max:500',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            if (empty($token)) {
                return $this->sendError(__('app.token_invalid'), Response::HTTP_BAD_REQUEST);
            }

            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($request->token);
            $email = $decryptToken[0];

            $userExists = UserTmp::where('token', $request->token)->first();

            $requestUser = $request->except(['avatar']);

            if ($request->hasFile('avatar')) {
                $path = $this->uploadFile($request->avatar);

                $requestUser = array_merge($requestUser, [
                    'avatar' => $path,
                ]);
            }

            if ($userExists) {

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

            $user = User::updateOrCreate(array_merge($requestUser, [
                'password' => bcrypt($request->password),
            ]));

            $userExists->delete();

            DB::commit();

            $token = auth('users')->login($user);
            $data = [
                'access_token' => $token,
                'token_type' => 'bearer',
            ];
            return $this->sendSuccessResponse($data);

        } catch (\Exception$e) {

            DB::rollBack();
            logger($e->getMessage());

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function sendMailResetPassword(Request $request)
    {

        try {

            $credentials = $request->only('email');

            $validator = Validator::make($credentials, [
                'email' => 'required|max:50|email',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $info = $this->user->where('email', $request->email)->first();

            if (!$info) {

                logger('Not found user');
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            //get User by email and create token
            $tokenArr[0] = $info->email;
            $email_verify_token_expiration = Carbon::now()->addDays(Constant::TIME_EXPIRATION_TOKEN);
            $tokenArr[1] = $email_verify_token_expiration;

            $token = Crypt::encrypt($tokenArr);

            DB::beginTransaction();

            // Find token in PasswordReset table
            $userExists = PasswordReset::where('user_id', $info->id)->get();

            foreach ($userExists as $user) {
                $user->delete();
            }

            $passwordReset = PasswordReset::updateOrCreate([
                'user_id' => $info->id,
                'token' => $token,
                'expiration' => $email_verify_token_expiration,
            ]);

            DB::commit();

            $url = config('app.url_fe') . '/reset/input/' . $token . '?email=' . $request->email;

            if ($passwordReset) {

                $data = array(
                    'url' => $url,
                    'name' => $info->name,
                );
                $this->sendEmail('emails.emailForgotPassword', $request->email, null, $data, 'Reset password');
            }

            return $this->sendSuccessResponse(null, 'success');
        } catch (\Exception$e) {

            DB::rollBack();
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function viewReset($token, Request $request)
    {

        try {
            $time_now = Carbon::now()->toDateTimeString();
            $decryptToken = Crypt::decrypt($token);
            $email = $decryptToken[0];

            $user = $this->user->where('email', $email)->first();

            $userExists = PasswordReset::where('token', $token)->first();

            if($userExists) {

                if ($time_now > Carbon::parse($userExists->expiration)->toDateTimeString()) {

                    return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {

                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $expire_at = Carbon::parse($decryptToken[1])->toDateTimeString();

            if ($time_now > $expire_at) {
               
                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            } else if (!$user) {

                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->sendSuccessResponse(null, 'success');
        } catch (\Exception $e) {

            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    public function reset($token, ResetPasswordRequest $request) {

        try {

            $time_now = Carbon::now()->toDateTimeString();
            $userExists = PasswordReset::where('token', $token)->first();
            if($userExists) {

                if ($time_now > Carbon::parse($userExists->expiration)->toDateTimeString()) {

                    return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
           
            $decryptToken = Crypt::decrypt($token);
            $email = $decryptToken[0];

            $user = $this->user->where('email', $email)->first();

            $expire_at = Carbon::parse($decryptToken[1])->toDateTimeString();

            if ($time_now > $expire_at) {

                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            } else if (!$user) {

                return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            DB::beginTransaction();

            $user->update([
                'password' => bcrypt($request->password)
            ]);

            $userExists->delete();

            DB::commit();

            $token = auth('users')->login($user);
            $data = [
                'access_token' => $token,
                'token_type' => 'bearer',
            ];
            return $this->sendSuccessResponse($data);

        } catch (\Exception $e) {

            DB::rollBack();
            return $this->sendError(__('app.system_error'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
