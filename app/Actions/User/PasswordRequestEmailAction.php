<?php

namespace App\Actions\User;

use App\Mail\ResetPassword\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PasswordRequestEmailAction
{
    /**
     * Handle the incoming request.
     * @param array $data
     * @return User
     */
    public function handle(array $data): User
    {
        $user = User::where('email', $data['email'])->first();

        $verificationCode = rand(100000, 999999); // Generate a random 6-digit code
        $user->verification_code = $verificationCode;
        $user->save();

        Mail::to($user->email)->send(new ResetPasswordMail([
            'full_name' => $user->full_name,
            'verificationCode' => $verificationCode
        ]));
        return $user;
    }
}
