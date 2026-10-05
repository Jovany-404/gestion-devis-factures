<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentPdfService
{
    public function generate(Document $document): string
    {
        $document->loadMissing('lines', 'client', 'sourceQuote');

        $pdf = Pdf::loadView('documents.pdf', [
            'document' => $document,
        ])->setPaper('a4');

        $path = $document->pdfPath();
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
