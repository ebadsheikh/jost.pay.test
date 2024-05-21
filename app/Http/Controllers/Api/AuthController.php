<?php

namespace App\Http\Controllers\Api;

use App\Actions\EmailVerification\VerifyCodeAction;
use App\Actions\User\PasswordRequestEmailAction;
use App\Actions\User\StoreUserAction;
use App\Enums\HttpStatusCodesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreatePinCode;
use App\Http\Requests\Auth\PasswordResetCodeRequest;
use App\Http\Requests\Auth\PasswordResetEmailRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Http\Requests\Auth\VerifyPinCode;
use App\Mail\ResetPassword\ResetPasswordMail;
use App\Mail\User\UserRegistered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private StoreUserAction $storeUserAction;
    private VerifyCodeAction $verifyCodeAction;
    private PasswordRequestEmailAction $passwordRequestEmailAction;

    public function __construct(
        StoreUserAction            $storeUserAction,
        VerifyCodeAction           $verifyCodeAction,
        PasswordRequestEmailAction $passwordRequestEmailAction
    )
    {
        $this->storeUserAction = $storeUserAction;
        $this->verifyCodeAction = $verifyCodeAction;
        $this->passwordRequestEmailAction = $passwordRequestEmailAction;
    }

    public function register(SignUpRequest $request)
    {
        $user = $this->storeUserAction->handle($request->validated());
        $verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->verification_code = $verificationCode;
        $user->save();
        Mail::to($user->email)->send(new UserRegistered([
            'full_name' => $user->full_name,
            'email' => $user->email,
            'verification_code' => $verificationCode,
        ]));

        return response()->json([
            'status' => HttpStatusCodesEnum::OK,
            'message' => 'Successfully registered',
            'user' => $user,
            'email_verified_at' => $user->email_verified_at,
            'token' => $user->createToken('API Token')->plainTextToken,
        ], HttpStatusCodesEnum::OK);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $verificationResult = $this->verifyCodeAction->handle($request->verification_code);

        if ($verificationResult['success']) {
            return response()->json([
                'status' => HttpStatusCodesEnum::OK,
                'message' => 'User verified successfully.',
                'user' => $verificationResult['user'],
            ], HttpStatusCodesEnum::OK);
        } else {
            return response()->json([
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'Invalid verification code.',
            ], HttpStatusCodesEnum::UNAUTHORIZED);
        }
    }

    public function createPinCode(CreatePinCode $request)
    {
        $user = Auth::user();

        $user->pin_code = bcrypt($request->pin_code);
        $user->save();

        return response()->json([
            'status' => HttpStatusCodesEnum::OK,
            'message' => 'Pin code created successfully.',
        ], HttpStatusCodesEnum::OK);
    }

    public function verifyPinCode(VerifyPinCode $request)
    {
        $user = Auth::user();

        if (Hash::check($request->pin_code, $user->pin_code)) {
            return response()->json([
                'status' => HttpStatusCodesEnum::OK,
                'message' => 'Pin code verified successfully.',
            ], HttpStatusCodesEnum::OK);
        } else {
            return response()->json([
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'Unauthorized.',
            ], HttpStatusCodesEnum::UNAUTHORIZED);
        }
    }

    public function resendVerificationCode(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->verification_code = $verificationCode;
            $user->save();
            Mail::to($user->email)->send(new UserRegistered([
                'full_name' => $user->full_name,
                'email' => $user->email,
                'verification_code' => $verificationCode,
            ]));

        }
        return response()->json([
            'status' => HttpStatusCodesEnum::OK,
            'message' => 'Verification code resent successfully.',
        ], HttpStatusCodesEnum::OK);
    }

    public function login(SignInRequest $request)
    {
        $data = $request->validated();

        if (!$user = User::where('email', $data['email'])->first()) {
            return response()->json([
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'User not found.',
            ], HttpStatusCodesEnum::UNAUTHORIZED);
        }
        if (!$user->email_verified_at) {
            return response()->json([
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'Email not verified. Please verify your email to login.',
            ], HttpStatusCodesEnum::UNAUTHORIZED);
        }
        if (!Auth::attempt($data)) {
            return response()->json([
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'Credentials not match',
            ], HttpStatusCodesEnum::UNAUTHORIZED);
        }

        return response()->json([
            'status' => HttpStatusCodesEnum::OK,
            'message' => 'Successfully Logged In',
            'user' => auth()->user(),
            'token' => auth()->user()->createToken('API Token')->plainTextToken,
        ], HttpStatusCodesEnum::OK);
    }

    public function requestPasswordReset(PasswordResetEmailRequest $request)
    {
        $user = $this->passwordRequestEmailAction->handle($request->validated());

        return response()->json([
            'status' => HttpStatusCodesEnum::OK,
            'message' => 'Verification code sent to your email',
            'user' => $user,
        ], HttpStatusCodesEnum::OK);
    }

    public function verifyPasswordResetCode(PasswordResetCodeRequest $request, User $user)
    {
        if ($user->verification_code == $request->verification_code) {
            return response()->json([
                'status' => HttpStatusCodesEnum::OK,
                'message' => 'Verification code is correct',
                'user' => $user,
                'token' => $user->createToken('API Token')->plainTextToken,
            ], HttpStatusCodesEnum::OK);
        } else {
            return response()->json([
                'status' => HttpStatusCodesEnum::UNAUTHORIZED,
                'message' => 'User not found.',
            ], HttpStatusCodesEnum::UNAUTHORIZED);
        }
    }

    public function resetPassword(ResetPasswordRequest $request, User $user)
    {
        $user->password = $request->password;
        $user->save();
        return response()->json([
            'status' => HttpStatusCodesEnum::OK,
            'message' => 'Password reset successfully',
        ], HttpStatusCodesEnum::OK);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Tokens Revoked! Logout Successfully',
        ]);
    }
}
