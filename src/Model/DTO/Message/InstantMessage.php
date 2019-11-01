<?php

declare(strict_types=1);

namespace App\Model\DTO\Message;

class InstantMessage implements MessageInterface
{
    /**
     * @var string
     */
    private $template;
    /**
     * @var array
     */
    private $recipients;
    /**
     * @var string
     */
    private $content;

    public function __construct(string $template, array $recipients, string $content)
    {
        $this->template = $template;
        $this->recipients = $recipients;
        $this->content = $content;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}