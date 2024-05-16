<?php

namespace App\Actions\EmailVerification;

use App\Enums\HttpStatusCodesEnum;
use Illuminate\Support\Facades\Auth;

class VerifyCodeAction
{
    public function handle($verificationCode)
    {
        $user = Auth::user();

        if ($verificationCode == $user->verification_code) {
            $user->email_verified_at = now();
            $user->save();

            return [
                'success' => true,
                'user' => $user,
                'token' => $user->createToken('API Token')->plainTextToken,
            ];
        } else {
            return [
                'success' => false,
            ];
        }
    }
}
