<?php

namespace App\Models;

class LocalitiesModel extends TenantModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'localities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name'];

    protected $useTimestamps = false;
}
