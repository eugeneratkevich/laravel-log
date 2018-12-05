# Laravel log
Laravel module for logs generating 

##Installation
First, require the package using Composer:

`composer require merkeleon/laravel-log`

Add new class Log that should be extended Merkeleon\Log\Model\Log
Our package save uuid, event_type, created_at, ip, user_agent by default.  

Add the config
`php artisan vendor:publish --provider="Merkeleon\Log\Providers\MerkeleonLogProvider"`


##Examples

    $auditLogRepositoryNew = new LogRepository('audit_log');

    $auditLogRepositoryNew->make([
        'user_id' => 5555,
        'event_type' => 'admin.cms.alert.delete',
        'user_id_related' => 2434,
        'payment_request_id' => 22,
        'data' => [
            'user_id' => '3333',
            'esks' => 222,
            'lakjsd' => [
                'fff' => 'ddddd',
                'dasda' => 'dasdas',
                'sdlas' => [
                    'dasdasd' => 'dasdas'
                ]
            ]
        ]
    ]);

    $table = AuditLogTable::make($auditLogRepositoryNew);