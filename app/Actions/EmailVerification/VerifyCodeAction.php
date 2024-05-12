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
                'status' => HttpStatusCodesEnum::OK,
                'message' => 'User verified successfully.',
                'user' => $user,
                'token' => $user->createToken('API Token')->plainTextToken,
            ];
        } else {
            return [
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'Invalid verification code.',
            ];
        }
    }
}
