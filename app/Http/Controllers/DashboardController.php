<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'clientCount' => Client::count(),
            'openQuotes' => Document::query()->ofType(Document::TYPE_QUOTE)->whereIn('status', ['draft', 'sent', 'accepted'])->count(),
            'quotePipeline' => Document::query()->ofType(Document::TYPE_QUOTE)->whereIn('status', ['draft', 'sent', 'accepted'])->sum('total'),
            'outstandingInvoices' => Document::query()->ofType(Document::TYPE_INVOICE)->where('status', 'sent')->sum('total'),
            'paidThisMonth' => Document::query()->ofType(Document::TYPE_INVOICE)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total'),
            'recentDocuments' => Document::query()->with('client')->latest('issue_date')->take(6)->get(),
            'profileReady' => CompanyProfile::current()->isReadyForDocuments(),
        ]);
    }
}
