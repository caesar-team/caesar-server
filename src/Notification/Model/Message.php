<?php

declare(strict_types=1);

namespace App\Notification\Model;

use App\Entity\MessageLog;
use App\Entity\User;

final class Message
{
    private string $email;

    private ?string $code;

    private bool $deferred = false;

    private bool $storage = true;

    private array $options = [];

    private ?User $user;

    public function __construct(string $email, string $code, array $options = [], bool $storage = true, bool $deferred = false)
    {
        $this->user = null;
        $this->email = $email;
        $this->code = $code;
        $this->options = $options;
        $this->storage = $storage;
        $this->deferred = $deferred;
    }

    public static function createFromUser(User $user, string $code, array $options = [], bool $storage = true): self
    {
        $message = new self($user->getEmail(), $code, $options, $storage);
        $message->user = $user;

        return $message;
    }

    public static function createDeferredFromUser(User $user, string $code, array $options = [], bool $storage = true): self
    {
        $message = new self($user->getEmail(), $code, $options, $storage, true);
        $message->user = $user;

        return $message;
    }

    public static function createMessageFromMessageLog(MessageLog $message, ?string $code = null): self
    {
        return new Message(
            $message->getRecipient(),
            $code ?: $message->getEvent(),
            $message->getOptions(),
            false
        );
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isStorage(): bool
    {
        return $this->storage;
    }
}
