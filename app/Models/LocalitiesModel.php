<?php

namespace App\Models;

use CodeIgniter\Model;

class LocalitiesModel extends Model
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
