<?php


namespace Merkeleon\Log\Repositories;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class MysqlLogDriver extends LogDriver
{
    protected $query;

    public function save(array $data)
    {
        DB::table($this->getTableName())
          ->insert(
              $data
          );
    }

    public function query()
    {
        if (!$this->query)
        {
            $this->query = DB::table($this->getTableName());
        }

        return $this->query;
    }

    public function paginate($perPage)
    {
        $page = Paginator::resolveCurrentPage();

        $total = $this->getCountForPagination(['*']);

        $results = $total ? $this->forPage($page, $perPage)->get(['*']) : collect();

        $results = $results->map(
            function ($row) {
               return $this->prepareLog((array)$row);

            }
        );

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function get()
    {
        return $this->prepareLog($this->query()->get());
    }

    protected function prepareLog($row)
    {
        return new $this->logClassName($row);
    }

    public function range($name, $from, $to)
    {
        if ($from)
        {
            $this->query()->where($name, '>=', $from);
        }

        if ($to)
        {
            $this->query()->where($name, '<=', $from);
        }
    }

    public function matchSubString($name, $value, $searchInObject)
    {
        $this->query()->where($name, 'like', '%' . $value . '%');
    }

    public function __call($name, $arguments)
    {
//        if (!is_callable($this->query(), $name))
//        {
//            throw  new LogException('method doesn\'t exists');
//        }

        return  $this->query()->$name(...$arguments);
    }


}