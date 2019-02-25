<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class ShareView
{
    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    public $userId;

    /**
     * @var string
     *
     * @SWG\Property(example="user@mail.com")
     */
    public $email;

    /**
     * @var string
     * @SWG\Property(example="WAITING|ACCEPTED")
     */
    public $status;

    /**
     * @var string
     * @SWG\Property(example="asfd7sdfasdf8dsfdsfgdfg8dfg7sdfg")
     */
    public $link;

    /**
     * @var string[]
     * @SWG\Property(example="['ROLE_USER']")
     */
    public $roles;
    /**
     * @var \DateTime
     */
    public $createdAt;
    /**
     * @var \DateTime
     */
    public $updatedAt;
}