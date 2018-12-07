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

    public static function make($logName)
    {
        return new static($logName);
    }

    protected function checkConfig($config)
    {
        if (!$config)
        {
            throw new LogException('There is no merkeleon log config');
        }

        if (!is_array($config))
        {
            throw new LogException('Merkeleon log config must be array');
        }

        if (!array_key_exists('driver', $config))
        {
            throw new LogException('You should point merkeleon log driver');
        }

        if (!array_key_exists('class', $config))
        {
            throw new LogException('You should point merkeleon log class');
        }

        if (!class_exists($config['class']))
        {
            throw new LogException('There is no class ' . $config['class']);
        }

        if (!is_subclass_of($config['class'], Log::class))
        {
            throw new LogException('Merkeleon log class should extend ' . Log::class . ' class');
        }

        if (!in_array($config['driver'], array_keys($this->drivers)))
        {
            throw new LogException('Merkeleon log driver can be: ' . implode(",", $this->drivers));
        }
    }

    protected function getDriver($driverName, $logClassName, $logFile)
    {
        $driverClass = $this->drivers[$driverName];

        return new $driverClass($logClassName, $logFile);
    }

    public function write(array $data)
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