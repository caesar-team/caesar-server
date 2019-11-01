<?php

declare(strict_types=1);

namespace App\Entity\Message;

use App\Model\DTO\Message\MessageInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class BufferedMessage
 * @ORM\Entity()
 */
class BufferedMessage implements MessageInterface
{
    use TimestampableEntity;

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $template;
    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    private $recipients;
    /**
     * @var string
     * @ORM\Column(type="json", nullable=true)
     */
    private $content;

    public function __construct(MessageInterface $message)
    {
        $this->id = Uuid::uuid4();
        $this->template = $message->getTemplate();
        $this->recipients = $message->getRecipients();
        $this->content = $message->getContent();
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