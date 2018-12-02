$auditLogRepositoryNew = new LogRepository('audit_log');

        $auditLogRepositoryNew->create([
            'user_id' => 345508,
            'event_type' => 'admin.cms.alert.delete',

        ]);

$auditLogRepositoryNew->paginate(10)
