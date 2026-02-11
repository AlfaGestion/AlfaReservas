<?php

namespace App\Controllers;

class Debug extends BaseController
{
    public function phpinfo()
    {
        phpinfo();
        return;
    }
}
