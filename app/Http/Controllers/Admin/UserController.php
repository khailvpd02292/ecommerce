<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends BaseController
{
    protected $user;

    public function __construct(
        User $user
    ) {
        $this->user = $user;
    }

    public function index() {

        $users = $this->user->all();

        return $this->sendSuccessResponse($users, null);

    }

    public function show($id) {

        $user = $this->user->where('id', $id)->first();

        return $this->sendSuccessResponse($user, null);

    }
}
