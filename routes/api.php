<?php

use App\Http\Controllers\Api\ApiArticleController;
use App\Http\Controllers\Api\ApiClientController;
use App\Http\Controllers\Api\ApiDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware([
    'auth:sanctum',
    'api.token',
    'can:access-api',
    'throttle:60,1',
])->group(function () {
    Route::get('clients', [ApiClientController::class, 'index'])
        ->middleware('abilities:documents:read')
        ->name('clients.index');
    Route::post('clients', [ApiClientController::class, 'store'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('clients.store');
    Route::get('articles', [ApiArticleController::class, 'index'])
        ->middleware('abilities:documents:read')
        ->name('articles.index');

    Route::get('quotes', [ApiDocumentController::class, 'quotes'])
        ->middleware('abilities:documents:read')
        ->name('quotes.index');
    Route::post('quotes', [ApiDocumentController::class, 'createQuote'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('quotes.store');
    Route::get('quotes/{document}', [ApiDocumentController::class, 'showQuote'])
        ->middleware('abilities:documents:read')
        ->name('quotes.show');
    Route::post('quotes/{document}/send', [ApiDocumentController::class, 'send'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('quotes.send');
    Route::post('quotes/{document}/accept', [ApiDocumentController::class, 'acceptQuote'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('quotes.accept');
    Route::post('quotes/{document}/reject', [ApiDocumentController::class, 'rejectQuote'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('quotes.reject');
    Route::post('quotes/{document}/convert', [ApiDocumentController::class, 'convertQuote'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('quotes.convert');

    Route::get('invoices', [ApiDocumentController::class, 'invoices'])
        ->middleware('abilities:documents:read')
        ->name('invoices.index');
    Route::get('invoices/{document}', [ApiDocumentController::class, 'showInvoice'])
        ->middleware('abilities:documents:read')
        ->name('invoices.show');
    Route::post('invoices/{document}/send', [ApiDocumentController::class, 'send'])
        ->middleware(['abilities:documents:write', 'can:manage-documents'])
        ->name('invoices.send');
    Route::post('invoices/{document}/pay', [ApiDocumentController::class, 'markPaid'])
        ->middleware(['abilities:payments:write', 'can:record-payment'])
        ->name('invoices.pay');
});
