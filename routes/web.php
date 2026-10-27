<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ConceptController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ObjectController;
use App\Livewire\EstimateView;
use App\Livewire\RoomForm;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'form'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [ObjectController::class, 'index'])->name('objects.index');
    Route::post('/objects', [ObjectController::class, 'store'])->name('objects.store');
    Route::get('/objects/{object}', [ObjectController::class, 'show'])->name('objects.show');
    Route::get('/objects/{object}/edit', [ObjectController::class, 'edit'])->name('objects.edit');
    Route::put('/objects/{object}', [ObjectController::class, 'update'])->name('objects.update');
    Route::delete('/objects/{object}', [ObjectController::class, 'destroy'])->name('objects.destroy');
    Route::post('/objects/{object}/rooms/{room}/copy', [ObjectController::class, 'copyRoom'])->name('rooms.copy');
    Route::delete('/objects/{object}/rooms/{room}', [ObjectController::class, 'deleteRoom'])->name('rooms.delete');

    Route::get('/objects/{object}/rooms/new', RoomForm::class)->name('rooms.create');
    Route::get('/objects/{object}/rooms/{room}', RoomForm::class)->name('rooms.edit');

    Route::get('/objects/{object}/estimate', EstimateView::class)->name('estimate');
    Route::get('/objects/{object}/estimate.xlsx', [ExportController::class, 'xlsx'])->name('estimate.xlsx');
    Route::get('/objects/{object}/estimate.pdf', [ExportController::class, 'pdf'])->name('estimate.pdf');
    Route::get('/estimates/{estimate}.xlsx', [ExportController::class, 'savedXlsx'])->name('estimates.xlsx');
    Route::get('/estimates/{estimate}.pdf', [ExportController::class, 'savedPdf'])->name('estimates.pdf');

    Route::get('/catalog', CatalogController::class)->name('catalog');
    Route::get('/concept/new', [ConceptController::class, 'create'])->name('concept.new');
    Route::get('/concept/built', [ConceptController::class, 'built'])->name('concept.built');
    Route::get('/concept/house/{house}', [ConceptController::class, 'house'])->whereNumber('house')->name('concept.house');
    Route::get('/concept/house/{house}/entrance/{section}/{view?}', [ConceptController::class, 'section'])->whereNumber(['house', 'section'])->name('concept.section');
    Route::get('/concept/house/{house}/flat/{n}', [ConceptController::class, 'flat'])->whereNumber(['house', 'n'])->name('concept.flat');
    Route::get('/concept/house/{house}/flat/{n}/room/{i}', [ConceptController::class, 'room'])->whereNumber(['house', 'n', 'i'])->name('concept.room');
    Route::get('/concept/{tab?}', [ConceptController::class, 'object'])->name('concept');
});
