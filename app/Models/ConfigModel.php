<?php

namespace App\Models;

class ConfigModel extends TenantModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'ta_configuracion';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['clave', 'valor'];

    protected $useTimestamps = false;
}
