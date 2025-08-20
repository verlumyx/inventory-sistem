<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the company settings page.
     */
    public function edit(): Response
    {
        $company = Company::getOrCreateCompany();

        return Inertia::render('settings/company', [
            'company' => $company->toApiArray(),
        ]);
    }

    /**
     * Update the company settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_company' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $company = Company::getOrCreateCompany();
        $company->update($validated);

        return back()->with('success', 'Configuración de empresa actualizada correctamente.');
    }
}
