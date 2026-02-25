<?php

namespace App\Models;

class CancelReservationsModel extends TenantModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'cancel_reservations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cancel_date',
        'field_id',
        'field_label',
        'user_name',
        'created_at',
    ];

    protected $useTimestamps = false;
}
