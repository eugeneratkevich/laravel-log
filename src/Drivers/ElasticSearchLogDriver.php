<?php


namespace Merkeleon\Log\Drivers;

use Merkeleon\ElasticReader\Elastic\SearchModelNew;
use Merkeleon\Log\Exceptions\LogException;

class ElasticSearchLogDriver extends LogDriver
{
    protected $elasticSearchModel;

    public function __construct($logClassName, $logRepository, $logFile = null)
    {
        parent::__construct($logClassName, $logRepository, $logFile);

        $this->elasticSearchModel = new SearchModelNew(
            $this->getTableName(),
            [$this, 'prepareHit']
        );
    }

    public function orderBy($orderField, $orderDirection)
    {
        $this->elasticSearchModel->orderBy($orderField, $orderDirection);

        return $this;
    }

    protected function saveToDb($row)
    {
        return $this->elasticSearchModel->create($row);
    }

    public function query()
    {
        return $this->elasticSearchModel->query();
    }

    public function paginate($perPage)
    {
        return $this->elasticSearchModel->paginate($perPage);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->elasticSearchModel, $name))
        {
            return $this->elasticSearchModel->$name(...$arguments);
        }

        if (!is_callable([$this->query(), $name]))
        {
            throw new LogException('Method' . $name .' doesn\'t exists in LogDriver');
        }

        $this->query()
             ->$name(...$arguments);

        return $this->logRepository;
    }

    public function prepareHit($hit)
    {
        return $this->newLog(array_get($hit, '_source'));
    }

    public function matchSubString($name, $value, $searchInObject)
    {
        $name = $searchInObject ? null : $name;

        return $this->query()
                    ->matchSubString($value, $name);
    }

    public function addCollectionCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback)
        {
            $this->elasticSearchModel->addCallback($callback);
        }

        return $this;
    }

    public function find($id)
    {
        return $this->where('id', $id)->first();
    }
}