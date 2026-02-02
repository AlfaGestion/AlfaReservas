<?php

namespace App\Controllers;

use App\Models\TimeModel;

class Time extends BaseController
{

    public $schedules = ['07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '00', '01', '02', '03', '04', '05', '06'];

    public function saveTime()
    {
        $timeModel = new TimeModel();

        $from = $this->request->getVar('from');
        $until = $this->request->getVar('until');
        $isSunday = $this->request->getVar('switchSunday');
        // $from_cut = $this->request->getVar('from_cut');
        // $until_cut = $this->request->getVar('until_cut');

        $nocturnalTime = $this->request->getVar('horarioNocturno');

        if($from == '' || $until == '' || $nocturnalTime == ''){
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'danger', 'body' => 'Debe completar todos los campos']);
        }

        $query = [
            'from' => $from,
            'until' => $until,
            // 'from_cut' => $from_cut,
            // 'until_cut' => $until_cut,
            'nocturnal_time' => $nocturnalTime,
            'is_sunday' => $isSunday,
        ];

        $existingHours = $timeModel->findAll();

        if($existingHours){
            try {
                $timeModel->update($existingHours[0]['id'], $query);
            } catch (\Exception $e) {
                return "Error al insertar datos: ".$e->getMessage();
            }
    
            return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Horarios editados correctamente']);
        }

        try {
            $timeModel->insert($query);
        } catch (\Exception $e) {
            return "Error al insertar datos: ".$e->getMessage();
        }

        return redirect()->to('abmAdmin')->with('msg', ['type' => 'success', 'body' => 'Horarios guardados correctamente']);
    }

    public function getTime(){
        $timeModel = new TimeModel();
        $times = $timeModel->findAll();
        $time = [];
        
        if($times){
            $time = array_slice($this->schedules, $times[0]['from'], $times[0]['until']);
        }

        // if ($times) {
        //     if ($times[0]['from_cut']) {
        //         $from_cut = $times[0]['from_cut'] - 1;
        //         $until_cut = $times[0]['until_cut'];

        //         while ($from_cut != $until_cut) {
        //             $from_cut++;
        //             array_push($time, strval($from_cut));
        //         }
        //     }
        // }

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $time, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function getNocturnalTime(){
        $timeModel = new TimeModel();
        $openingTime = $timeModel->getOpeningTime();
        $times = $timeModel->findAll()[0];
        $index = array_search($times['nocturnal_time'], $openingTime);

        $nocturnalTime = array_slice($openingTime, $index);

        try {
            return  $this->response->setJSON($this->setResponse(null, null, $nocturnalTime, 'Respuesta exitosa'));
        } catch (\Exception $e) {
            return  $this->response->setJSON($this->setResponse(404, true, null, $e->getMessage()));
        }
    }

    public function setResponse($code = 200, $error = false, $data = null, $message = '')
    {
        $response = [
            'error' => $error,
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ];

        return $response;
    }

}
