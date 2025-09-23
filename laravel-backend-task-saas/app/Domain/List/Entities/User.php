<?php

namespace App\Domain\Entities;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class User
{
    private UuidInterface $id;
    private string $email;
    private ?string $name;
    private ?DateTimeImmutable $email_verified_at = null;
    private ?string $password;
    private ?string $avatar_url;

    public function __construct(
        UuidInterface $id,
        string $email,
        ?string $name,
        ?DateTimeImmutable $email_verified_at = null,
        ?string $password,
        ?string $avatar_url
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->email_verified_at = $email_verified_at;
        $this->password = $password;
        $this->avatar_url = $avatar_url;
    }
}
