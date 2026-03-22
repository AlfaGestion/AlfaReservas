<?php

namespace App\Controllers;

use App\Models\LocalitiesModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected $session;

    function __construct()
    {
        $this->session = \Config\Services::session();
        $this->session->start();
    }
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['app'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    protected function ensureLocalityExists(?string $locality): void
    {
        if (!is_string($locality)) {
            return;
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $locality));
        if ($normalized === '') {
            return;
        }

        $lower = function_exists('mb_strtolower')
            ? mb_strtolower($normalized, 'UTF-8')
            : strtolower($normalized);

        $localitiesModel = new LocalitiesModel();
        $existing = $localitiesModel->where('LOWER(name)', $lower)->first();
        if (!$existing) {
            $localitiesModel->insert(['name' => $normalized]);
        }
    }

    protected function resolveTenantBrandingAssets(string $codigo): array
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return [
                'logo' => null,
                'background' => null,
                'tenantDir' => null,
            ];
        }

        $basePath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR;
        $candidates = [
            [
                'dir' => $basePath . $codigo . DIRECTORY_SEPARATOR,
                'url' => base_url(PUBLIC_FOLDER . $codigo . '/'),
                'public_dir' => 'public/' . $codigo . '/',
            ],
            [
                'dir' => $basePath . 'assets' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $codigo . DIRECTORY_SEPARATOR,
                'url' => base_url(PUBLIC_FOLDER . 'assets/tenants/' . $codigo . '/'),
                'public_dir' => 'public/assets/tenants/' . $codigo . '/',
            ],
        ];
        $logoCandidates = ['logo.png', 'logo.jpg', 'logo.jpeg', 'logo.webp', 'LOGO.png', 'LOGO.jpg', 'LOGO.jpeg', 'LOGO.webp'];
        $backgroundCandidates = ['fondo.jpg', 'fondo.png', 'fondo.webp', 'background.jpg', 'background.png', 'background.webp'];

        $branding = [
            'logo' => null,
            'background' => null,
            'tenantDir' => null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_dir($candidate['dir'])) {
                continue;
            }

            if ($branding['tenantDir'] === null) {
                $branding['tenantDir'] = $candidate['public_dir'];
            }

            if ($branding['logo'] === null) {
                foreach ($logoCandidates as $file) {
                    $fullPath = $candidate['dir'] . $file;
                    if (is_file($fullPath)) {
                        $branding['logo'] = $candidate['url'] . $file . '?v=' . ((string) (@filemtime($fullPath) ?: time()));
                        break;
                    }
                }
            }

            if ($branding['background'] === null) {
                foreach ($backgroundCandidates as $file) {
                    $fullPath = $candidate['dir'] . $file;
                    if (is_file($fullPath)) {
                        $branding['background'] = $candidate['url'] . $file . '?v=' . ((string) (@filemtime($fullPath) ?: time()));
                        break;
                    }
                }
            }

            if ($branding['logo'] !== null && $branding['background'] !== null) {
                break;
            }
        }

        return $branding;
    }
}
