<?php


namespace Merkeleon\Log\Drivers;

use Merkeleon\ElasticReader\Elastic\SearchModelNew;
use Merkeleon\Log\Exceptions\LogException;
use Merkeleon\Log\Model\Log;

class ElasticSearchLogDriver extends LogDriver
{
    protected $elasticSearchModel;

    public function __construct($logClassName, $logFile = null)
    {
        parent::__construct($logClassName, $logFile);

        $this->elasticSearchModel = new SearchModelNew(
            $this->getTableName(),
            [$this, 'prepareHit']
        );
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

    public function get()
    {
        return $this->elasticSearchModel->get();
    }

    public function __call($name, $arguments)
    {
        if (!is_callable([$this->query(), $name]))
        {
            throw new LogException('Method' . $name .' doesn\'t exists in LogDriver');
        }

        $this->query()
             ->$name(...$arguments);

        return $this;
    }

    public function prepareHit($hit)
    {
        $data = ['uuid' => array_get($hit, '_id')] + array_get($hit, '_source');

        return $this->newLog($data);
    }

    public function matchSubString($name, $value, $searchInObject)
    {
        $name = $searchInObject ? null : $name;

        return $this->query()
                    ->matchSubString($value, $name);
    }
}