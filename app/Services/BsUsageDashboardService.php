<?php

namespace App\Services;

use App\Models\BsUsageCheck;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BsUsageDashboardService
{
    public function filters(Request $request): array
    {
        $filters = $request->validate([
            'pemakaian_periode' => ['nullable', 'regex:/^\d{4}-?(0[1-9]|1[0-2])$/D'],
            'pemakaian_jenis' => 'nullable|in:OPR,PD,EDK,ELK',
            'pemakaian_page' => 'nullable|integer|min:1',
            'pemakaian_status' => 'nullable|in:FOLLOW_UP,PENDING,APPROVED,REJECTED,REVISION,RECHECK,DIFFERENCE',
            'pemakaian_referensi' => 'nullable|string|max:64',
            'tampilan' => 'nullable|in:ringkasan,daftar,pengajuan,finance',
        ]);
        // Accept legacy bookmarks, but the module route now determines the view.
        unset($filters['tampilan']);
        $periods = app(PeriodService::class);
        $filters['pemakaian_periode'] = $request->query->has('pemakaian_periode')
            ? (!empty($filters['pemakaian_periode']) ? $periods->normalize($filters['pemakaian_periode']) : '')
            : $periods->current();

        return $filters;
    }

    public function data(Request $request): array
    {
        $filters = $this->filters($request);
        $union = null;
        foreach (BsUsageCheckService::TYPES as $type => $model) {
            $part = $this->transactions($type, $model, $request->user());
            if (!empty($filters['pemakaian_periode'])) {
                $part->where((new $model)->getTable().'.periode', $filters['pemakaian_periode']);
            }
            if (!empty($filters['pemakaian_jenis']) && $filters['pemakaian_jenis'] !== $type) {
                $part->whereRaw('1 = 0');
            }
            $union = $union ? $union->unionAll($part) : $part;
        }
        $query = $this->classified($union);
        if (!empty($filters['pemakaian_referensi'])) $query->where('no_referensi', 'like', '%'.$filters['pemakaian_referensi'].'%');
        if (($filters['pemakaian_status'] ?? '') === 'FOLLOW_UP') {
            $query->where('in_pending_group', 1);
        } elseif (($filters['pemakaian_status'] ?? '') === 'DIFFERENCE') {
            $query->where('finance_status', 'APPROVED')->where('different_items', '>', 0);
        } elseif (!empty($filters['pemakaian_status'])) {
            $query->where('finance_status', $filters['pemakaian_status']);
        }
        $summaryQuery = $this->summary(clone $query);
        $summary = (clone $summaryQuery)->first();
        $byModule = (clone $summaryQuery)->addSelect('jenis')->groupBy('jenis')->get()->keyBy('jenis');
        $empty = (object) array_fill_keys(array_keys((array) $summary), 0);
        $modules = [];
        foreach (BsUsageCheckService::LABELS as $type => $label) $modules[$type] = $byModule->get($type, clone $empty);
        $rows = $query->select('usage.*')
            ->selectRaw("CASE WHEN check_id IS NOT NULL AND needs_recheck = 0 AND keputusan <> 'REVISION' THEN nominal_user - nominal_finance ELSE NULL END as selisih")
            ->orderByRaw('COALESCE(validated_at, tanggal) DESC')->orderBy('jenis')->orderByDesc('source_id')
            ->paginate(10, ['*'], 'pemakaian_page')
            ->appends(array_diff_key($filters, ['pemakaian_page' => true, 'pemakaian_jenis' => true]))->fragment('pemakaian-bs');

        return compact('filters', 'summary', 'modules', 'rows');
    }

    public function sourceResult(User $viewer, string $type, int $id): ?\stdClass
    {
        abort_unless(isset(BsUsageCheckService::TYPES[$type]), 404);
        $model = BsUsageCheckService::TYPES[$type];
        $source = $this->transactions($type, $model, $viewer)->where((new $model)->getTable().'.id', $id);

        return $this->classified($source)->select('usage.*')
            ->selectRaw("CASE WHEN check_id IS NOT NULL AND needs_recheck = 0 AND keputusan <> 'REVISION' THEN nominal_user - nominal_finance ELSE NULL END as selisih")->first();
    }

    public function financeSources(User $viewer): Builder
    {
        abort_unless($viewer->can('akses.approval-finance'), 403);
        $union = null;
        foreach (BsUsageCheckService::TYPES as $type => $model) {
            // Finance may also inspect saved OPR/PD drafts. Events require confirmation.
            $part = $this->transactions($type, $model, $viewer, includeDrafts: true);
            $union = $union ? $union->unionAll($part) : $part;
        }

        return $this->classified($union);
    }

    private function classified(Builder $sources): Builder
    {
        $classified = DB::query()->fromSub($sources, 'usage')->select('usage.*')
            ->selectRaw("CASE WHEN check_id IS NULL THEN 'PENDING' WHEN needs_recheck = 1 THEN 'RECHECK' ELSE keputusan END as finance_status");
        // Retain the combined follow-up amount for compatibility. Public summaries
        // display unchecked, revision, recheck and approval differences separately.
        $amounted = DB::query()->fromSub($classified, 'usage')->select('usage.*')
            ->selectRaw("CASE WHEN finance_status IN ('PENDING', 'RECHECK', 'REVISION') THEN nominal_user WHEN finance_status = 'APPROVED' THEN nominal_user - nominal_finance ELSE 0 END as nominal_pending")
            ->selectRaw("CASE WHEN finance_status IN ('PENDING', 'RECHECK', 'REVISION') OR (finance_status = 'APPROVED' AND different_items > 0) THEN 1 ELSE 0 END as in_pending_group");
        return DB::query()->fromSub($amounted, 'usage');
    }

    private function summary(Builder $query): Builder
    {
        $reviewed = "finance_status = 'APPROVED'";
        return $query->selectRaw("COUNT(*) as confirmed_count,
            COALESCE(SUM(nominal_user), 0) as nominal_user,
            COALESCE(SUM(CASE WHEN check_id IS NULL THEN 1 ELSE 0 END), 0) as unchecked_count,
            COALESCE(SUM(needs_recheck), 0) as recheck_count,
            COALESCE(SUM(CASE WHEN $reviewed THEN 1 ELSE 0 END), 0) as checked_count,
            COALESCE(SUM(CASE WHEN $reviewed AND different_items > 0 THEN 1 ELSE 0 END), 0) as different_count,
            COALESCE(SUM(nominal_pending), 0) as nominal_pending,
            COALESCE(SUM(in_pending_group), 0) as pending_count,
            COALESCE(SUM(CASE WHEN finance_status IN ('PENDING', 'RECHECK', 'REVISION') THEN nominal_user ELSE 0 END), 0) as nominal_waiting,
            COALESCE(SUM(CASE WHEN finance_status = 'PENDING' THEN nominal_user ELSE 0 END), 0) as nominal_unchecked,
            COALESCE(SUM(CASE WHEN finance_status = 'REVISION' THEN nominal_user ELSE 0 END), 0) as nominal_revision,
            COALESCE(SUM(CASE WHEN finance_status = 'RECHECK' THEN nominal_user ELSE 0 END), 0) as nominal_recheck,
            COALESCE(SUM(CASE WHEN finance_status = 'REVISION' THEN 1 ELSE 0 END), 0) as revision_count,
            COALESCE(SUM(CASE WHEN finance_status = 'REJECTED' THEN 1 ELSE 0 END), 0) as rejected_count,
            COALESCE(SUM(CASE WHEN finance_status = 'REJECTED' THEN nominal_user ELSE 0 END), 0) as nominal_rejected,
            COALESCE(SUM(CASE WHEN $reviewed THEN nominal_finance ELSE 0 END), 0) as nominal_finance,
            COALESCE(SUM(CASE WHEN $reviewed THEN nominal_user - nominal_finance ELSE 0 END), 0) as selisih");
    }

    private function transactions(string $type, string $model, User $viewer, bool $includeDrafts = false): Builder
    {
        $header = (new $model)->getTable();
        // Eloquent applies SoftDeletes only for PD. OPR has no deleted_at column.
        $event = BsUsageCheckService::isEvent(new $model);
        $items = $event ? DB::table($header)->select('transaksi_id')
            ->selectRaw('NULL as cost_category_id, ROUND(nominal, 2) as nominal_asal')
            : (new $model)->details()->getRelated()->newQuery()
            ->select('transaksi_id', 'cost_category_id')->selectRaw('ROUND(SUM(nominal), 2) as nominal_asal')
            ->groupBy('transaksi_id', 'cost_category_id')->toBase();
        $amounts = DB::query()->fromSub(clone $items, 'items')->select('transaksi_id')
            ->selectRaw('SUM(nominal_asal) as nominal_user')->groupBy('transaksi_id');
        $latest = BsUsageCheck::query()->where('jenis_transaksi', $type)->latestPerSource()->toBase();
        $approved = DB::table('bs_usage_check_details')->select('bs_usage_check_id')
            ->selectRaw('SUM(nominal_disetujui) as nominal_finance, SUM(CASE WHEN nominal_asal <> nominal_disetujui THEN 1 ELSE 0 END) as different_items')
            ->groupBy('bs_usage_check_id');

        // A check may have been saved against a draft that was changed before confirmation.
        // Compare each category, including added/removed ones; matching totals alone is insufficient.
        $changed = DB::query()->fromSub(clone $items, 'current_item')
            ->leftJoin('bs_usage_check_details as checked_item', function (JoinClause $join) {
                $join->whereRaw('(checked_item.cost_category_id = current_item.cost_category_id OR (checked_item.cost_category_id IS NULL AND current_item.cost_category_id IS NULL))')
                    ->on('checked_item.bs_usage_check_id', '=', 'finance.id');
            })->whereColumn('current_item.transaksi_id', $header.'.transaksi_id')
            ->where(fn ($q) => $q->whereNull('checked_item.id')->orWhereColumn('checked_item.nominal_asal', '<>', 'current_item.nominal_asal'))->selectRaw('1');
        $removed = DB::table('bs_usage_check_details as old_item')
            ->whereColumn('old_item.bs_usage_check_id', 'finance.id')
            ->whereNotExists(DB::query()->fromSub(clone $items, 'remaining_item')
                ->whereColumn('remaining_item.transaksi_id', $header.'.transaksi_id')
                ->whereRaw('(remaining_item.cost_category_id = old_item.cost_category_id OR (remaining_item.cost_category_id IS NULL AND old_item.cost_category_id IS NULL))')->selectRaw('1'))->selectRaw('1');

        if ($event) {
            // Event totals belong to their numeric ID, even if a legacy reference is empty/duplicated.
            $amounts = DB::table($header)->select('id as source_id')->selectRaw('ROUND(nominal, 2) as nominal_user');
            $changed = DB::query()->selectRaw('1')->whereNotExists(DB::table('bs_usage_check_details as event_item')
                ->whereColumn('event_item.bs_usage_check_id', 'finance.id')->whereNull('event_item.cost_category_id')
                ->whereRaw('event_item.nominal_asal = ROUND('.$header.'.nominal, 2)')->selectRaw('1'));
            $removed = DB::table('bs_usage_check_details as other_item')->whereColumn('other_item.bs_usage_check_id', 'finance.id')
                ->whereNotNull('other_item.cost_category_id')->selectRaw('1');
        }

        $query = DB::table($header)->join('user as owner', 'owner.id', '=', $header.'.user_id')
            ->leftJoin('user as confirmer', 'confirmer.id', '=', $header.'.validated_by')
            ->leftJoinSub($amounts, 'amounts', $event ? 'amounts.source_id' : 'amounts.transaksi_id', '=', $header.($event ? '.id' : '.transaksi_id'))
            ->leftJoinSub($latest, 'finance', 'finance.transaksi_pemakaian_id', '=', $header.'.id')
            ->leftJoinSub($approved, 'approved', 'approved.bs_usage_check_id', '=', 'finance.id')
            ->select([
                $header.'.id as source_id', $header.'.transaksi_id as no_referensi', $header.'.tanggal',
                $header.'.periode', $header.'.status as source_status', $header.'.user_id', $header.'.validated_at', 'owner.nama as user_name',
                'confirmer.nama as confirmer_name', 'finance.id as check_id', 'finance.document_no as check_no',
                'finance.tanggal_pengecekan', 'finance.created_at as checked_at', 'finance.checker_name',
                'finance.keputusan', 'finance.catatan_keputusan', 'finance.periode as check_period',
                'approved.nominal_finance', 'approved.different_items',
            ])->selectRaw('? as jenis, COALESCE(amounts.nominal_user, 0) as nominal_user', [$type])
            ->selectRaw('CASE WHEN finance.id IS NOT NULL AND (EXISTS ('.$changed->toSql().') OR EXISTS ('.$removed->toSql().')'
                .($event ? ' OR finance.source_revision IS NULL OR finance.source_revision <> ' : ' OR COALESCE(finance.source_revision, 0) <> ')
                .$header.'.submission_version) THEN 1 ELSE 0 END as needs_recheck', [...$changed->getBindings(), ...$removed->getBindings()]);

        if (!$includeDrafts || $event) $query->where($header.'.status', 'VALIDATED');
        if ($includeDrafts && !$event) {
            $query->where(fn ($q) => $q->whereNull($header.'.finance_correction_check_id')->orWhere($header.'.status', 'VALIDATED'));
        }

        // Finance already has permission to review all sources. Other dashboards keep their owner scope.
        if (!$viewer->can('akses.approval-finance') && !$viewer->canSeeAllTransactions()) {
            $query->where($header.'.user_id', $viewer->id);
        }

        return $query;
    }
}
