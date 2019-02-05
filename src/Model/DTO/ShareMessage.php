<?php

declare(strict_types=1);

namespace App\Model\DTO;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

class ShareMessage
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int
     *
     * @Groups({"write"})
     */
    private $secondsLimit;

    /**
     * @var int
     */
    private $requestsLimit;

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function getRequestsLimit(): ?int
    {
        return $this->requestsLimit;
    }

    public function setRequestsLimit(int $requestsLimit)
    {
        $this->requestsLimit = $requestsLimit;
    }

    public function getSecondsLimit(): ?int
    {
        return $this->secondsLimit;
    }

    public function setSecondsLimit(int $secondsLimit): void
    {
        $this->secondsLimit = $secondsLimit;
    }
}
