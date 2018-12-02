<?php

namespace Merkeleon\Log;

use Merkeleon\Log\Drivers\ElasticSearchLogDriver;
use Merkeleon\Log\Drivers\MysqlLogDriver;
use Merkeleon\Log\Exceptions\LogException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Merkeleon\Log\Model\Log;

class LogRepository
{
    protected $logDriver;
    protected $logClassName;

    protected $drivers = [
        'mysql'   => MysqlLogDriver::class,
        'elastic' => ElasticSearchLogDriver::class
    ];

    public function __construct($logName)
    {
        $config = config('merkeleon_log.' . $logName);

        $this->checkConfig($config);

        $this->logDriver = $this->getDriver($config['driver'], $config['class'], array_get($config, 'log_file'));

        $this->logClassName = $config['class'];
    }

    protected function checkConfig($config)
    {
        if ($config
            && is_array($config)
            && array_key_exists('driver', $config)
            && array_key_exists('class', $config)
            && class_exists($config['class'])
            && is_subclass_of($config['class'], Log::class)
            && in_array($config['driver'], array_keys($this->drivers))
        )
        {
            return;
        }

        throw new LogException('Invalid config');

    }

    protected function getDriver($driverName, $logClassName, $logFile)
    {
        $driverClass = $this->drivers[$driverName];

        return new $driverClass($logClassName, $logFile);
    }

    public function make(array $data)
    {
        $data = array_merge($data, $this->logClassName::getDefaultValues());

        $this->logDriver->makeLog($data);
    }

    public function __call($name, $arguments)
    {
        if (!is_callable([$this->logDriver, $name]))
        {
            throw new LogException('method ' . $name . ' doesn\'t exists');
        }

        return $this->logDriver->$name(...$arguments);
    }

    public function getDateFormat()
    {
        return $this->logClassName::$dateTimeFormat;
    }
}