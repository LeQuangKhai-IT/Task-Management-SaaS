<x-mail::message>
    # Hello {{ $user->name ?? 'there' }},

    We received a request to reset the password for your account ({{ $user->email }}).
    Click the button below to set a new password:

    <x-mail::button :url="$url">
        Reset Password
    </x-mail::button>

    If you did not request a password reset, please ignore this email.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>