<x-mail::message>
    Dear {{$mailData['full_name']}}

    We are pleased to inform you that your profile has been successfully created on the Jost Pay platform.

    Email: {{$mailData['email']}}

    Verification Code: {{$mailData['verification_code']}}

    Please ensure to keep your login information confidential and secure. If you have any questions or encounter any
    issues while accessing your account, feel free to reach out to our support team at [support@email.com].

    Thank you for joining our platform. We look forward to providing you with an exceptional experience.

    Best regards,
    {{ config('app.name') }}
</x-mail::message>
