<?php

use App\Http\Controllers\ApiDocumentationController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/docs/openapi.yaml', ApiDocumentationController::class)->name('api.docs');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store')->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store')->middleware('throttle:3,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->middleware('can:manage-documents')->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->middleware('can:manage-documents')->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->middleware('can:manage-documents')->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->middleware('can:manage-documents')->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->middleware('can:manage-documents')->name('clients.destroy');

    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create', [ArticleController::class, 'create'])->middleware('can:manage-catalog')->name('articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->middleware('can:manage-catalog')->name('articles.store');
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->middleware('can:manage-catalog')->name('articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->middleware('can:manage-catalog')->name('articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->middleware('can:manage-catalog')->name('articles.destroy');

    Route::get('/quotes', [DocumentController::class, 'quotes'])->name('quotes.index');
    Route::get('/quotes/create', [DocumentController::class, 'createQuote'])->middleware('can:manage-documents')->name('quotes.create');
    Route::post('/quotes', [DocumentController::class, 'storeQuote'])->middleware('can:manage-documents')->name('quotes.store');
    Route::get('/quotes/{document}', [DocumentController::class, 'showQuote'])->name('quotes.show');
    Route::get('/quotes/{document}/edit', [DocumentController::class, 'editQuote'])->middleware('can:manage-documents')->name('quotes.edit');
    Route::put('/quotes/{document}', [DocumentController::class, 'updateQuote'])->middleware('can:manage-documents')->name('quotes.update');
    Route::delete('/quotes/{document}', [DocumentController::class, 'destroyQuote'])->middleware('can:manage-documents')->name('quotes.destroy');
    Route::post('/quotes/{document}/send', [DocumentController::class, 'send'])->middleware('can:manage-documents')->name('quotes.send');
    Route::post('/quotes/{document}/accept', [DocumentController::class, 'acceptQuote'])->middleware('can:manage-documents')->name('quotes.accept');
    Route::post('/quotes/{document}/reject', [DocumentController::class, 'rejectQuote'])->middleware('can:manage-documents')->name('quotes.reject');
    Route::post('/quotes/{document}/convert', [DocumentController::class, 'convertQuote'])->middleware('can:manage-documents')->name('quotes.convert');

    Route::get('/invoices', [DocumentController::class, 'invoices'])->name('invoices.index');
    Route::get('/invoices/{document}', [DocumentController::class, 'showInvoice'])->name('invoices.show');
    Route::post('/invoices/{document}/send', [DocumentController::class, 'send'])->middleware('can:manage-documents')->name('invoices.send');
    Route::post('/invoices/{document}/pay', [DocumentController::class, 'markPaid'])->middleware('can:record-payment')->name('invoices.pay');
    Route::get('/documents/{document}/pdf', [DocumentController::class, 'downloadPdf'])->name('documents.pdf');
    Route::get('/documents/export.csv', [DocumentController::class, 'exportCsv'])->name('documents.export');

    Route::get('/settings/company', [CompanyProfileController::class, 'edit'])->middleware('can:manage-company')->name('company.edit');
    Route::put('/settings/company', [CompanyProfileController::class, 'update'])->middleware('can:manage-company')->name('company.update');
    Route::get('/settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
    Route::get('/staff', [StaffController::class, 'index'])->middleware('can:manage-staff')->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('can:manage-staff')->name('staff.store');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->middleware('can:manage-staff')->name('staff.destroy');
});
