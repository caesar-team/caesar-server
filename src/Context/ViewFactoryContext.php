<?php

declare(strict_types=1);

namespace App\Context;

use App\Strategy\ViewFactory\ViewFactoryInterface;

final class ViewFactoryContext
{
    /**
     * @var array|ViewFactoryInterface[]
     */
    private $factories;

    public function __construct(ViewFactoryInterface ...$factories)
    {
        $this->factories = $factories;
    }

    /**
     * Any model or entity.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function view($data)
    {
        foreach ($this->factories as $factory) {
            if (!$factory->canView($data)) {
                continue;
            }

            return $factory->view($data);
        }

        return null;
    }

    /**
     * Any model[] or entity[].
     *
     * @param array $data
     *
     * @return mixed
     */
    public function viewList(array $data)
    {
        foreach ($this->factories as $factory) {
            if (!$factory->canView(current($data))) {
                continue;
            }

            return $factory->viewList($data);
        }

        return [];
    }
}