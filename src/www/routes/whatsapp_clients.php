<?php

use App\Http\Controllers\WhatsappClientsController;
use Illuminate\Support\Facades\Route;

Route::post('/whatsapp-client-json', [WhatsappClientsController::class, 'store']);
Route::get('/whatsapp-client-json/{company_id}', [WhatsappClientsController::class, 'show']);

Route::post('/whatsapp-client-json-delete-account', [WhatsappClientsController::class, 'delete']);
Route::post('/whatsapp-test-message', [WhatsappClientsController::class, 'testMessage']);
