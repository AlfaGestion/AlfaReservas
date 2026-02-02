<?php

namespace App\Controllers;

use App\Models\UploadModel;

class Upload extends BaseController
{
    protected $helpers = ['form'];

    public function index()
    {
        return view('upload/upload_form', ['errors' => []]);
    }

    public function upload()
    {
        $validationRule = [
            'userfile' => [
                'label' => 'The selected file',
                'rules' => 'uploaded[userfile]'
                    . '|is_image[userfile]'
                    . '|mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                    . '|max_size[userfile,2000]'
                    . '|max_dims[userfile,2500,2500]'
            ],
        ];

        if (!$this->validate($validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return redirect()->to('upload')->with('msg', ['type' => 'danger', 'body' => $data]);
        }

        $img = $this->request->getFile('userfile');

        if (!$img->hasMoved()) {
            $modelUpload = new UploadModel();
            $extension = explode('.', $img->getName());
            $fileName = uniqid() . '.' . $extension[count($extension) -1];

            $query = [
                'name' => $fileName,
            ];

            $existingBg = $modelUpload->first();

            if($existingBg){
                $modelUpload->delete($existingBg['id']);
            }

            $modelUpload->insert($query);

            $img->move(ROOTPATH . 'public/assets/images/uploads', $fileName);

            $data = ['errors' => 'Archivo subido exitosamente.'];

            return redirect()->to('upload')->with('msg', ['type' => 'success', 'body' => $data]);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return view('upload/upload_form', $data);
    }

    public function deleteBackground(){
        $modelUpload = new UploadModel();

        $bg = $modelUpload->first();

        if($bg){
            $modelUpload->delete($bg['id']);
            return redirect()->to(base_url('abmAdmin'))->with('msg', ['type' => 'success', 'body' => 'Eliminado correctamente']);
        } else {
            return redirect()->to(base_url('abmAdmin'))->with('msg', ['type' => 'danger', 'body' => 'No hay archivos para eliminar']);
        }
        

    }
}
