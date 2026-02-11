<?php

namespace App\Models;

use CodeIgniter\Model;

class CancelReservationsModel extends Model
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
