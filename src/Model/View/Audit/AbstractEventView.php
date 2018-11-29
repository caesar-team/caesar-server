<?php

declare(strict_types=1);

namespace App\Model\View\Audit;

use Swagger\Annotations as SWG;

abstract class AbstractEventView
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
     * @SWG\Property(example="email@email")
     */
    public $blame;

    /**
     * @var string
     *
     * @SWG\Property(example="127.0.0.1")
     */
    public $ip;

    /**
     * @var string
     *
     * @SWG\Property(example="20-10-2000 00:00:00")
     */
    public $createdAt;

    /**
     * @var string
     *
     * @SWG\Property
     */
    public $message;

    /**
     * @var bool
     *
     * @SWG\Property
     */
    public $verify;
}
