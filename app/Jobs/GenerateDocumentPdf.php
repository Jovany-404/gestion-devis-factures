<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDocumentPdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $documentId) {}

    public function handle(DocumentPdfService $pdfService): void
    {
        $document = Document::query()->findOrFail($this->documentId);
        $pdfService->generate($document);
    }
}
