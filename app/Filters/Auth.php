<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('logueado')) {
            session()->destroy();
            $path = '/' . ltrim($request->getUri()->getPath(), '/');
            $query = (string) $request->getUri()->getQuery();
            $target = $path . ($query !== '' ? ('?' . $query) : '');
            return redirect()->to('/auth/login?redirect=' . rawurlencode($target));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
