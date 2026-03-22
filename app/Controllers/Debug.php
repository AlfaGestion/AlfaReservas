<?php

namespace App\Controllers;

class Debug extends BaseController
{
    public function phpinfo()
    {
        if (env('CI_ENVIRONMENT', 'production') === 'production') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        phpinfo();
        return;
    }
}
