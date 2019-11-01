<?php

declare(strict_types=1);

namespace App\Model\Request\Message;

use App\Model\DTO\Message\MessageInterface;

class MessageRequest implements MessageInterface
{
    /**
     * @var string|null
     */
    private $template;
    /**
     * @var string[]
     */
    private $recipients = [];
    /**
     * @var string|null
     */
    private $content;

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}