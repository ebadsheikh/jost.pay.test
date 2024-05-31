<x-mail::message>
    Dear {{$mailData['full_name']}}

    Please use the below OTP to login in Jost Pay App.

    Email: {{$mailData['email']}}

    Verification Code: {{$mailData['verification_code']}}

    Please ensure to keep your login information confidential and secure.

    Thank you for using our platform. We look forward to providing you with an exceptional experience.

    Best regards,
    {{ config('app.name') }}
</x-mail::message>
