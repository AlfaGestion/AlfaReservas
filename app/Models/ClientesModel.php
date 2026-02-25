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
    protected $allowedFields    = [
        'codigo',
        'NombreApellido',
        'razon_social',
        'base',
        'id_rubro',
        'email',
        'telefono',
        'dni',
        'localidad',
        'habilitado',
        'estado',
        'trial_start',
        'trial_end',
        'paid_through',
        'grace_end',
        'link',
    ];

    protected $useTimestamps = false;
}
