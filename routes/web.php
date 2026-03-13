<?php

use App\Http\Controllers\DomainReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', DomainReportController::class)->name('domains.index');
