<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Services\DocumentPdfService;
use App\Services\DocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClientPortalController extends Controller
{
    public function index(Request $request): View
    {
        $client = $this->clientFor($request);
        $documents = $client->documents()
            ->latest('issue_date')
            ->paginate(10);

        return view('portal.index', [
            'client' => $client,
            'company' => CompanyProfile::current(),
            'documents' => $documents,
        ]);
    }

    public function show(Request $request, Document $document): View
    {
        $document = $this->findClientDocument($request, $document)
            ->load(['lines', 'invoice', 'sourceQuote']);

        return view('portal.documents.show', [
            'company' => CompanyProfile::current(),
            'document' => $document,
        ]);
    }

    public function downloadPdf(
        Request $request,
        Document $document,
        DocumentPdfService $pdfService
    ): BinaryFileResponse {
        $document = $this->findClientDocument($request, $document);
        $path = $document->pdfPath();

        if (! Storage::disk('local')->exists($path)) {
            $pdfService->generate($document);
        }

        return response()->download(
            Storage::disk('local')->path($path),
            "{$document->number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    public function acceptQuote(
        Request $request,
        Document $document,
        DocumentService $documents
    ): RedirectResponse {
        $document = $this->findClientDocument($request, $document);
        abort_unless($document->type === Document::TYPE_QUOTE, 404);

        $documents->acceptQuote($document);

        return to_route('portal.documents.show', $document)
            ->with('success', 'Votre accord sur le devis a bien été enregistré.');
    }

    public function rejectQuote(
        Request $request,
        Document $document,
        DocumentService $documents
    ): RedirectResponse {
        $document = $this->findClientDocument($request, $document);
        abort_unless($document->type === Document::TYPE_QUOTE, 404);

        $documents->rejectQuote($document);

        return to_route('portal.documents.show', $document)
            ->with('success', 'Votre refus du devis a bien été enregistré.');
    }

    private function clientFor(Request $request): Client
    {
        return $request->user()->client()->firstOrFail();
    }

    private function findClientDocument(Request $request, Document $document): Document
    {
        return $this->clientFor($request)
            ->documents()
            ->whereKey($document->getKey())
            ->firstOrFail();
    }
}
