<?php

namespace App\Http\Middleware;

use App\Models\License;
use App\Models\LicenseLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LicenseMiddleware
{
    /**
     * Rutas que no requieren verificación de licencia
     */
    protected $excludedRoutes = [
        'license.*',
        'login',
        'logout',
        'register',
        'password.*',
        'verification.*',
        'two-factor.*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si la ruta está excluida
        if ($this->shouldExcludeRoute($request)) {
            return $next($request);
        }

        // Obtener la licencia actual
        $currentLicense = License::getCurrentLicense();

        // Si no hay licencia activa, verificar si hay alguna licencia
        if (!$currentLicense) {
            $anyLicense = License::orderBy('created_at', 'desc')->first();

            if (!$anyLicense) {
                // No hay licencias en el sistema, crear una solicitud inicial
                $machineId = License::generateMachineId();
                License::createRequest($machineId, $request->user()?->email);

                LicenseLog::logAccessDenied('Sistema sin licencia - Solicitud inicial creada', $request);
            } else {
                LicenseLog::logAccessDenied('Licencia expirada o inválida', $request);
            }

            // Redireccionar a la página de renovación de licencia
            return redirect()->route('license.renewal');
        }

        // Verificar si la licencia está próxima a vencer (menos de 7 días)
        if ($currentLicense->daysUntilExpiration() <= 7) {
            // Agregar notificación flash pero permitir continuar
            session()->flash('warning',
                'Su licencia vencerá en ' . $currentLicense->daysUntilExpiration() . ' días. ' .
                'Por favor, renueve su licencia pronto.'
            );
        }

        // Log de acceso con licencia válida (solo para rutas importantes, no todas)
        if ($this->shouldLogAccess($request)) {
            LicenseLog::logValidAccess($currentLicense, $request);
        }

        return $next($request);
    }

    /**
     * Verificar si la ruta debe ser excluida de la verificación
     */
    protected function shouldExcludeRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return false;
        }

        foreach ($this->excludedRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determinar si se debe registrar el acceso (evitar spam de logs)
     */
    protected function shouldLogAccess(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        // Solo log para rutas importantes
        $importantRoutes = [
            'dashboard',
            'warehouses.*',
            'items.*',
            'entries.*',
            'invoices.*',
            'adjustments.*',
            'transfers.*',
        ];

        if (!$routeName) {
            return false;
        }

        foreach ($importantRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
