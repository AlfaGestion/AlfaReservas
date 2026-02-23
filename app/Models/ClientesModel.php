<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientesModel extends Model
{
    protected $DBGroup          = 'alfareserva';
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['codigo', 'razon_social', 'base', 'id_rubro', 'email', 'habilitado', 'link'];

    protected $useTimestamps = false;
}
