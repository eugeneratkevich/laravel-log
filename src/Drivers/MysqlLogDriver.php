<?php


namespace Merkeleon\Log\Drivers;

use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Merkeleon\Log\Model\Log;
use Ramsey\Uuid\Uuid;

class MysqlLogDriver extends LogDriver
{
    protected $query;

    protected function saveToDb($row)
    {
        return DB::table($this->getTableName())
                 ->insert($row);
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

        $results = $total ? $this->forPage($page, $perPage)
                                 ->get(['*']) : collect();

        $results = $results->map(
            function ($row) {
                return $this->prepareLog((array)$row);

            }
        );

        return $this->paginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    public function get()
    {
        return $this->prepareLog($this->query()
                                      ->get());
    }

    public function range($name, $from, $to)
    {
        if ($from)
        {
            $this->query()
                 ->where($name, '>=', $from);
        }

        if ($to)
        {
            $this->query()
                 ->where($name, '<=', $to);
        }

        return $this->query();
    }

    public function matchSubString($name, $value, $searchInObject)
    {
        $this->query()
             ->where($name, 'like', '%' . $value . '%');
    }

    public function __call($name, $arguments)
    {
        if (!is_callable([$this->query(), $name]))
        {
            throw  new LogException('Method' . $name .' doesn\'t exists in LogDriver');
        }

        return $this->query()
                    ->$name(...$arguments);
    }

    public function whereOr(array $conditions)
    {
        $this->query()
             ->where(function ($query) use ($conditions) {
                 $firstKey   = array_first(array_keys($conditions));
                 $firstValue = array_shift($conditions);
                 $query->where($firstKey, $firstValue);

                 foreach ($conditions as $key => $value)
                 {
                     $query->orWhere($key, $value);
                 }
             });
    }

    protected function prepareLog($row)
    {
        return $this->newLog($row);
    }

    protected function fromCastUuid($value)
    {
        if (is_null($value))
        {
            return (string)Uuid::uuid4();
        }

        return $value;
    }

    protected function fromCastArray(array $value)
    {
        $value = $this->asJson($value);

        if ($value === false)
        {
            throw JsonEncodingException::forAttribute(
                $this, $key, json_last_error_msg()
            );
        }

        return $value;
    }
}