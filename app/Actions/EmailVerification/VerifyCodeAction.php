<?php

namespace App\Actions\EmailVerification;

use App\Enums\HttpStatusCodesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerifyCodeAction
{
    public function handle($request): array
    {
        $user = User::whereEmail($request->email)
            ->whereVerificationCode($request->verification_code)
            ->first();
        if ($user) {
            if ($request->type == 'verify_email') {
                $user->email_verified_at = now();
                $user->save();
            }

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
