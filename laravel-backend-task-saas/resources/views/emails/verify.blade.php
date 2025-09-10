<x-mail::message>
    # You're nearly there!

    Hi {{ $user->name ?? 'there' }},

    To finish setting up your account and start using {{ config('app.name') }},
    please confirm we've got the correct email for you.

    <x-mail::button :url="$url">
        Verify Your Email
    </x-mail::button>

    If you did not request this, please ignore this email.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>