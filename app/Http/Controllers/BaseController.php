<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class BaseController extends Controller
{

    /**
     * return success response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendSuccessResponse($data = null, $message = null)
    {
        $response = [
            'success' => true,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($message = null, $code = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($code) {
            return response()->json($response, $code);
        } else {
            return response()->json($response);
        }
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmail($page, $toEmail, $bccEmail, $data, $title)
    {
       try {
            $email = config('mail.from')['address'];
            $name_email = config('mail.from')['name'];

            Mail::send(['text' => $page], $data, function ($message) use ($toEmail, $bccEmail, $email, $name_email, $title) {
                    $message->from($email, $name_email)
                    ->subject($title);
                    $message->to($toEmail);

                    if (!empty($bccEmail)) {
                        $message->bcc($bccEmail);
                    }
                    
                    $message->setContentType('text/plain');
                });

       } catch (\Exception $e) {
            logger($e->getMessage());
       }
    }

}
