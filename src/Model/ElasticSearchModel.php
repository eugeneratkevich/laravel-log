<?php


namespace App\Services\AuditLog;


use App\Models\AuditLog\AuditLog;
use Carbon\Carbon;
use Merkeleon\ElasticReader\Elastic\SearchModel as MerkeleonElasticSearchModel;

class ElasticSearchModel extends MerkeleonElasticSearchModel
{
    /**
     * @param $hit
     * @return AuditLog
     * @throws \Exception
     */
    public static function prepareHit($hit)
    {
        $eventType = array_get($hit, '_source.event_type');

        $className = audit_log_repository()->prepareAuditLogClassNameByEvent($eventType);

        return new $className(
            array_get($hit, '_id'),
            array_get($hit, '_source.user_id'),
            array_get($hit, '_source.user_id_related'),
            array_get($hit, '_source.payment_request_id'),
            array_get($hit, '_source.ip'),
            array_get($hit, '_source.user_agent'),
            array_get($hit, '_source.data'),
            new Carbon(array_get($hit, '_source.created_at'))
        );
    }

    protected static function getIndex()
    {
        return config('audit_log.elastic_index');
    }
}