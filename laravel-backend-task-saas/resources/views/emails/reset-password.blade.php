<x-mail::message>
    # Reset Your Password

    You requested to reset your password.
    Click the button below to set a new password:

    <x-mail::button :url="$url">
        Reset Password
    </x-mail::button>

    If you did not request this, please ignore this email.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>