<?php


namespace Merkeleon\Log\Repositories;

use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;


abstract class LogDriver
{
    protected $logClassName;

    public function __construct($logClassName)
    {
        $this->logClassName = $logClassName;
    }

    protected function getTableName()
    {
        return $this->logClassName::getTableName();
    }

    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()
                        ->makeWith(LengthAwarePaginator::class, compact(
                            'items', 'total', 'perPage', 'currentPage', 'options'
                        ));
    }
}