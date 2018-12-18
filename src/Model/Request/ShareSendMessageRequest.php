<?php

declare(strict_types=1);

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ShareSendMessageRequest
{
    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $email = '';

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $message = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
