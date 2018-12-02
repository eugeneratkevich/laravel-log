<?php

namespace Merkeleon\Log\Repositories;

use Merkeleon\Log\Exseptions\LogException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class LogRepository
{
    protected $logDriver;
    protected $logClassName;

    public function __construct($logName)
    {
        $config = config('merkeleon_log.' . $logName);

        $this->checkConfig($config);

        $this->logDriver = $this->getDriver($config['driver'], $config['class']);

        $this->logClassName = $config['class'];

    }

    protected function checkConfig($config)
    {
        /** todo */
    }

    protected function getDriver($driverName, $logClassName)
    {
        switch ($driverName)
        {
            case 'mysql':
                return new MysqlLogDriver($logClassName);

            case 'elastic':
                return new ElasticSearchLogDriver($logClassName);

            default:
                throw new LogException('Unespected log driver');
        }
    }

    public function create(array $data)
    {
        $data = $this->prepareData($data);

        $this->logDriver->save($data);
    }

    private function prepareData(array $data)
    {
        $logAttributes = $this->logClassName::getAttributes();

        $data = array_intersect_key($data, array_flip($logAttributes));

        $data = array_merge(
            [
                'ip' =>request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => (new Carbon())->format('d/M/Y:H:i:s O')
            ],
            $data
        );

        $this->validate($data);

        return $data;
    }

    private function validate(array $data)
    {
        $validator = Validator::make($data, $this->logClassName::getRules());
        if ($validator->fails())
        {
            throw new LogException('Log is not valid' . $validator->getMessageBag());
        }
    }

    public function __call($name, $arguments)
    {
//        if (!is_callable($this->logDriver, $name))
//        {
//            throw  new LogException('method '.$name.' doesn\'t exists');
//        }

        return $this->logDriver->$name(...$arguments);
    }
}
