<x-mail::message>
    Hello {{$mailData['full_name']}},

    We have received a request to reset your password. Please use the verification code below to reset your password:

    Verification Code: **{{ $mailData['verificationCode'] }}**

    If you did not request this password reset, please ignore this email. This verification code will expire after a certain period of time.

    If you have any questions or encounter any issues while accessing your account, feel free to reach out to our support team at [support@email.com].

    Thank you for joining our platform. We look forward to providing you with an exceptional experience.

    Best regards,
    {{ config('app.name') }}
</x-mail::message>
