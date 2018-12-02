<?php


namespace Merkeleon\Log\Repositories;


use Merkeleon\ElasticReader\Elastic\SearchModelNew;

class ElasticSearchLogDriver extends LogDriver
{
    protected $elasticSearchModel;

    public function __construct($logClassName)
    {
        parent::__construct($logClassName);

        $this->elasticSearchModel = new SearchModelNew(
            $this->getTableName(),
            [$this, 'prepareHit']
        );
    }

    public function save(array $data)
    {
        return $this->elasticSearchModel->create($data);
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
        if (!method_exists($this->query(), $name))
        {
            throw  new LogException('method doesn\'t exists');
        }

        $this->query()->$name(...$arguments);
    }

    public function prepareHit($hit)
    {
        $data = ['id' => array_get($hit, '_id')] + array_get($hit, '_source');

        return new $this->logClassName($data);
    }

    public function matchSubString($name, $value, $searchInObject)
    {
        $name = $searchInObject ? null : $name;

        return $this->query()->matchSubString($value, $name);
    }
}