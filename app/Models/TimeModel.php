<?php

namespace App\Models;

class TimeModel extends TenantModel
{
    public $schedules = ['07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '00', '01', '02', '03', '04', '05', '06'];

    protected $DBGroup          = 'default';
    protected $table            = 'time';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['from', 'until', 'from_cut', 'until_cut', 'nocturnal_time', 'is_sunday'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getOpeningTime(){
        $times = $this->findAll();
        $time = [];
        
        $fromIndex = array_search($times[0]['from'], $this->schedules);
        $untilIndex = array_search($times[0]['until'], $this->schedules);
        
        if ($fromIndex !== false && $untilIndex !== false) {
            if ($fromIndex > $untilIndex) {
                $time = array_merge(
                    array_slice($this->schedules, $fromIndex),
                    array_slice($this->schedules, 0, $untilIndex + 1)
                );
            } else {
                $time = array_slice($this->schedules, $fromIndex, $untilIndex - $fromIndex + 1);
            }
        } 

        return $time;
    }
}
