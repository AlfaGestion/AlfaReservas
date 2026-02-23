<?php

namespace App\Models;

use CodeIgniter\Model;

class RubrosModel extends Model
{
    protected $DBGroup          = 'alfareserva';
    protected $table            = 'rubros';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['descripcion'];

    protected $useTimestamps = false;
}

