<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\FinanceMonitoringContextService;
use App\Services\FinanceMonitoringService;
use App\Services\PeriodService;
use Illuminate\Http\Request;

class FinanceMonitoringController extends Controller
{
    private const PERIODE_REGEX = '/^$|^\d{4}-?(0[1-9]|1[0-2])$/D';

    public function __construct(
        private FinanceMonitoringService $monitoring,
        private PeriodService $periods,
        private FinanceMonitoringContextService $context,
    ) {}

    private function normalizeOrCurrent(?string $periode): string
    {
        return $periode !== null && $periode !== '' ? $this->periods->normalize($periode) : $this->periods->current();
    }

    public function index(Request $request)
    {
        $validated = $request->validate(['periode' => ['nullable', 'string', 'regex:' . self::PERIODE_REGEX]]);
        $periode = $this->normalizeOrCurrent($validated['periode'] ?? null);
        $companies = $this->monitoring->getAllCompanies();
        return view('finance-monitoring.index', compact('companies', 'periode'));
    }

    public function showCompany(Request $request, Company $company)
    {
        $validated = $request->validate([
            'periode' => ['nullable', 'string', 'regex:' . self::PERIODE_REGEX],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $periode = $this->normalizeOrCurrent($validated['periode'] ?? null);
        $page = (int) ($validated['page'] ?? 1);
        $data = $this->monitoring->getCompanyData($company, $periode, $page);
        return view('finance-monitoring.company', $data);
    }

    public function showUser(Request $request, Company $company, User $user)
    {
        $validated = $request->validate([
            'periode' => ['nullable', 'string', 'regex:' . self::PERIODE_REGEX],
            'page' => ['nullable', 'integer', 'min:1'],
            'jenis_filter' => ['nullable', 'string', 'in:OPR,PD,EDK,ELK'],
        ]);
        $periode = $this->normalizeOrCurrent($validated['periode'] ?? null);
        $page = (int) ($validated['page'] ?? 1);
        $jenisFilter = $validated['jenis_filter'] ?? null;

        $this->context->assertCompanyUser($company, $user);

        $data = $this->monitoring->getUserData($user, $periode, $jenisFilter, $page);
        $data['company'] = $company;
        $data['back_url'] = route('monitoring-finance.company', ['company' => $company->getKey(), 'periode' => $periode]);
        $data['current_filter'] = $jenisFilter;
        $data['current_page'] = $page;
        return view('finance-monitoring.user', $data);
    }

    /**
     * Daftar transaksi modul asli untuk user yang dimonitor (read-only).
     */
    public function moduleIndex(Request $request, Company $company, User $user, string $jenis)
    {
        $this->context->assertType($jenis);
        $this->context->assertCompanyUser($company, $user);

        $validated = $request->validate([
            'periode' => ['nullable', 'string', 'regex:' . self::PERIODE_REGEX],
        ]);
        $periode = $this->normalizeOrCurrent($validated['periode'] ?? null);

        $data = $this->monitoring->getModuleIndex($company, $user, $jenis, $periode);
        $data['monitoringReadOnly'] = true;
        $data['monitoringCompany'] = $company;
        $data['monitoringUser'] = $user;
        $data['monitoringPeriode'] = $periode;
        $data['monitoringBackUrl'] = route('monitoring-finance.user', [
            'company' => $company->getKey(), 'user' => $user->getKey(), 'periode' => $periode,
        ]);

        return view($data['view'], $data);
    }

    /**
     * Detail transaksi modul asli (read-only).
     */
    public function moduleShow(Request $request, Company $company, User $user, string $jenis, string $source)
    {
        $this->context->assertType($jenis);
        $this->context->assertCompanyUser($company, $user);

        $validated = $request->validate([
            'periode' => ['nullable', 'string', 'regex:' . self::PERIODE_REGEX],
        ]);
        $periode = $this->normalizeOrCurrent($validated['periode'] ?? null);

        $data = $this->monitoring->getModuleShow($company, $user, $jenis, $source);
        $data['monitoringReadOnly'] = true;
        $data['monitoringCompany'] = $company;
        $data['monitoringUser'] = $user;
        $data['monitoringPeriode'] = $periode;
        $data['monitoringBackUrl'] = route('monitoring-finance.module.index', [
            'company' => $company->getKey(), 'user' => $user->getKey(),
            'jenis' => $jenis, 'periode' => $periode,
        ]);

        return view($data['view'], $data);
    }
}