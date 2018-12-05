<?php

namespace Merkeleon\Log\Model;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Merkeleon\Log\Exceptions\LogException;
use Carbon\Carbon;

abstract class Log
{
    protected static $attributes = [
        'uuid'       => 'uuid',
        'created_at' => 'datetime',
        'ip'         => 'string',
        'user_agent' => 'string',
        'event_type' => 'string',
    ];

    protected static $customAttributes = [];

    protected static $rules = [];

    public static $dateTimeFormat = 'Y-m-d H:i:s';

    protected $values;

    protected static $table;

    public function __construct($row)
    {
        $this->validate($row);

        $this->values = array_intersect_key($row, array_flip(static::getAttributes()));
    }

    public static function getDefaultValues()
    {
        $attributes = static::getAttributes();

        $values = [];

        foreach ($attributes as $attribute)
        {
            if ($defaultValue = static::getDefaultValue($attribute))
            {
                $values[$attribute] = $defaultValue;
            }
        }

        return $values;
    }

    public static function getTableName()
    {
        return static::$table;
    }

    public static function getAttributes()
    {
        return array_keys(static::getAttributesWithCasts());
    }

    public static function getAttributesWithCasts()
    {
        return array_merge(static::$attributes, static::$customAttributes);
    }

    public function __get($name)
    {
        if (!in_array($name, static::getAttributes()))
        {
            throw new LogException('Attribute ' . $name . 'not exists');
        }

        return array_get($this->values, $name);
    }

    public function getValues()
    {
        return $this->values;
    }

    public static function getRules()
    {
        return static::$rules;
    }

    protected function validate(array $data)
    {
        $validator = Validator::make($data, static::$rules);

        if ($validator->fails())
        {
            throw new LogException('Log is not valid' . $validator->getMessageBag());
        }
    }

    public function toLogFileArray()
    {
        return [];
    }

    protected static function getDefaultValue($attribute)
    {
        $defaultValueMethodName = studly_method_name('get_' . $attribute . '_default_value');

        if (!method_exists(static::class, $defaultValueMethodName))
        {
            return null;
        }

        return static::$defaultValueMethodName();
    }

    protected static function getIpDefaultValue()
    {
        return request()->ip();
    }

    protected static function getUserAgentDefaultValue()
    {
        return request()->userAgent();
    }

    protected static function getCreatedAtDefaultValue()
    {
        return new Carbon();
    }
}
