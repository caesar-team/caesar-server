<?php

declare(strict_types=1);

namespace App\Model\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ShareMessage
{
    /**
     * @var string
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Groups({"read", "write"})
     */
    private $message;

    /**
     * @var \DateTime
     */
    private $expires;

    /**
     * @var int
     *
     * @Assert\NotBlank
     * @Assert\Type("int")
     * @Groups({"write"})
     */
    private $secondsLimit;

    /**
     * @var int
     *
     * @Assert\Type("int")
     * @Assert\GreaterThan(0)
     * @Assert\NotBlank
     * @Groups({"write"})
     */
    private $requestsLimit;

    /**
     * @return $this
     */
    public function initExpiration()
    {
        $secondsLimit = $this->secondsLimit;
        if (is_null($secondsLimit)) {
            return $this;
        }
        $this->setExpires(new \DateTime("now +{$secondsLimit} seconds"));

        return $this;
    }

    /**
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \DateTime $expires
     *
     * @return $this
     */
    public function setExpires($expires)
    {
        if ($expires instanceof \DateTime) {
            $this->expires = $expires;
        } elseif (is_string($expires)) {
            $this->expires = new \DateTime($expires);
        }

        return $this;
    }

    /**
     * @return \DateTime $expires
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param int $requestsLimit
     *
     * @return $this
     */
    public function setRequestsLimit($requestsLimit)
    {
        if (!$requestsLimit) {
            $requestsLimit = null;
        }

        $this->requestsLimit = $requestsLimit;

        return $this;
    }

    /**
     * @return int $requestsLimit
     */
    public function getRequestsLimit()
    {
        return $this->requestsLimit;
    }

    /**
     * @param int $secondsLimit
     *
     * @return $this
     */
    public function setSecondsLimit($secondsLimit)
    {
        $this->secondsLimit = $secondsLimit;

        return $this;
    }

    /**
     * @return int $secondsLimit
     */
    public function getSecondsLimit()
    {
        return $this->secondsLimit;
    }
}
