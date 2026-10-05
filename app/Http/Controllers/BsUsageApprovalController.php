<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPerjalananDinasHeader;
use App\Services\BsCashFlowService;
use App\Services\BsValidationService;
use Illuminate\Http\Request;

class BsUsageApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'sometimes|in:PENDING_OWNER,REJECTED_OWNER,VALIDATED,ALL']);
        $status = $request->input('status', 'PENDING_OWNER');
        $query = TransaksiPerjalananDinasHeader::forBsOwner((int) $request->user()->id)
            ->where('user_id', '<>', $request->user()->id)->where('approval_version', '>', 0)
            ->with(['user', 'tujuanDinas'])->withSum('details', 'nominal');
        if ($status !== 'ALL') $query->where('status', $status);
        $requests = $query->orderByDesc('submitted_at')->paginate(15)->withQueryString();
        return view('bs-usage-approvals.index', compact('requests', 'status'));
    }

    public function show(int $id, BsCashFlowService $cash)
    {
        $transaksi = $this->document($id);
        $transaksi->load(['user', 'bsOwner', 'details.costCategory', 'tujuanDinas', 'approvalLogs.actor']);
        $balance = $cash->calculate($transaksi->bsCashOwnerId(), $transaksi->periode);
        return view('bs-usage-approvals.show', compact('transaksi', 'balance'));
    }

    public function approve(Request $request, int $id, BsValidationService $service)
    {
        $request->validate(['approval_version' => 'required|integer|min:1']);
        $service->approve($this->document($id), (int) $request->approval_version);
        return redirect()->route('bs-usage-approvals.show', $id)
            ->with('success', 'Pemakaian disetujui dan dikonfirmasi. Saldo BS telah berkurang sesuai rincian biaya.');
    }

    public function reject(Request $request, int $id, BsValidationService $service)
    {
        $request->validate(['approval_version' => 'required|integer|min:1', 'reason' => 'required|string|max:1000']);
        $service->reject($this->document($id), (int) $request->approval_version, $request->reason);
        return redirect()->route('bs-usage-approvals.show', $id)
            ->with('success', 'Pemakaian ditolak. Saldo BS tidak berkurang.');
    }

    private function document(int $id): TransaksiPerjalananDinasHeader
    {
        $document = TransaksiPerjalananDinasHeader::findOrFail($id);
        abort_unless($document->bsCashOwnerId() === (int) auth()->id() && $document->needsOwnerApproval(), 403);
        abort_unless($document->approval_version > 0, 404);
        return $document;
    }
}
