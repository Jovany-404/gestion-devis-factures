<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDocumentController extends Controller
{
    public function quotes(Request $request)
    {
        return $this->listing($request, Document::TYPE_QUOTE);
    }

    public function invoices(Request $request)
    {
        return $this->listing($request, Document::TYPE_INVOICE);
    }

    public function createQuote(StoreDocumentRequest $request, DocumentService $documents): JsonResponse
    {
        $document = $documents->createQuote($request->user(), $request->validated());

        return (new DocumentResource($document->load('lines')))->response()->setStatusCode(201);
    }

    public function showQuote(Document $document)
    {
        abort_unless($document->type === Document::TYPE_QUOTE, 404);

        return new DocumentResource($document->load('lines'));
    }

    public function showInvoice(Document $document)
    {
        abort_unless($document->type === Document::TYPE_INVOICE, 404);

        return new DocumentResource($document->load('lines'));
    }

    public function send(Document $document, DocumentService $documents): DocumentResource
    {
        $expectedType = request()->routeIs('api.v1.quotes.*')
            ? Document::TYPE_QUOTE
            : Document::TYPE_INVOICE;
        abort_unless($document->type === $expectedType, 404);

        $documents->send($document);

        return new DocumentResource($document->fresh()->load('lines'));
    }

    public function acceptQuote(Document $document, DocumentService $documents): DocumentResource
    {
        abort_unless($document->type === Document::TYPE_QUOTE, 404);

        return new DocumentResource($documents->acceptQuote($document)->load('lines'));
    }

    public function rejectQuote(Document $document, DocumentService $documents): DocumentResource
    {
        abort_unless($document->type === Document::TYPE_QUOTE, 404);

        return new DocumentResource($documents->rejectQuote($document)->load('lines'));
    }

    public function convertQuote(Document $document, DocumentService $documents): JsonResponse
    {
        abort_unless($document->type === Document::TYPE_QUOTE, 404);

        $invoice = $documents->convertAcceptedQuoteToInvoice($document, request()->user());

        return (new DocumentResource($invoice->load('lines')))->response()->setStatusCode(201);
    }

    public function markPaid(Document $document, DocumentService $documents): DocumentResource
    {
        abort_unless($document->type === Document::TYPE_INVOICE, 404);

        return new DocumentResource($documents->markInvoicePaid($document)->load('lines'));
    }

    private function listing(Request $request, string $type)
    {
        $documents = Document::query()
            ->ofType($type)
            ->with('lines')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('issue_date')
            ->paginate(25);

        return DocumentResource::collection($documents);
    }
}
