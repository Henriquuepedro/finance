<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/gastos-fixos', [App\Http\Controllers\FixedController::class, 'expenseList'])->name('fixed.expense.index');
    Route::get('/ganhos-fixos', [App\Http\Controllers\FixedController::class, 'incomeList'])->name('fixed.income.index');

    Route::get('/gastos-variaveis', [App\Http\Controllers\MonthlyController::class, 'expenseList'])->name('monthly.expense.index');
    Route::get('/ganhos-variaveis', [App\Http\Controllers\MonthlyController::class, 'incomeList'])->name('monthly.income.index');

    Route::get('/configuracoes', [App\Http\Controllers\ConfigController::class, 'index'])->name('config');
    Route::get('/save_config', [App\Http\Controllers\ConfigController::class, 'saveConfig'])->name('save_config');

    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::prefix('monthly')->name('monthly.')->group(function () {
            Route::post('/', [App\Http\Controllers\MonthlyController::class, 'store'])->name('store');
            Route::get('/expense_list', [App\Http\Controllers\MonthlyController::class, 'getMonthlyExpenseList'])->name('expense.list');
            Route::get('/income_list', [App\Http\Controllers\MonthlyController::class, 'getMonthlyIncomeList'])->name('income.list');
            Route::get('/all_expense_list', [App\Http\Controllers\MonthlyController::class, 'getAllMonthlyExpenseLastMonths'])->name('expense.all');
            Route::get('/expenses_this_month', [App\Http\Controllers\MonthlyController::class, 'getExpensesThisMont'])->name('expense.this_month');
            Route::get('/data_dashboard', [App\Http\Controllers\HomeController::class, 'getValuesHome'])->name('data_dashboard');
        });
        Route::prefix('fixed')->name('fixed.')->group(function () {
            Route::post('/', [App\Http\Controllers\FixedController::class, 'store'])->name('store');
            Route::get('/expense_list', [App\Http\Controllers\FixedController::class, 'getExpenseList'])->name('expense.list');
            Route::get('/income_list', [App\Http\Controllers\FixedController::class, 'getIncomeList'])->name('income.list');
        });
    });

});
