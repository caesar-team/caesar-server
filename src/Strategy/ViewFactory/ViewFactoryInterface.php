<?php

declare(strict_types=1);

namespace App\Strategy\ViewFactory;

interface ViewFactoryInterface
{
    /**
     * @param mixed $data
     */
    public function canView($data): bool;

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function view($data);

    /**
     * @return mixed
     */
    public function viewList(array $data);
}
