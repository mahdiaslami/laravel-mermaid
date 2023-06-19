<?php

namespace MahdiAslami\Database\Facades;

use Illuminate\Support\Facades\Facade;

class Mermaid extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'mermaid';
    }
}
