<?php

namespace App\Http\Controllers\Api;

use App\Actions\EmailVerification\VerifyCodeAction;
use App\Actions\User\StoreUserAction;
use App\Enums\HttpStatusCodesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Mail\User\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private StoreUserAction $storeUserAction;
    private VerifyCodeAction $verifyCodeAction;

    public function __construct(
        StoreUserAction $storeUserAction,
        VerifyCodeAction $verifyCodeAction
    ) {
        $this->storeUserAction = $storeUserAction;
        $this->verifyCodeAction = $verifyCodeAction;
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
            'token' => $user->createToken('API Token')->plainTextToken,
        ], HttpStatusCodesEnum::OK);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $verificationResult = $this->verifyCodeAction->handle($request->verification_code);

        return response()->json($verificationResult['message'], $verificationResult['status']);
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

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Tokens Revoked! Logout Successfully',
        ]);
    }
}
