<?php

namespace App\Http\Controllers;

use App\Models\FixedExpense;
use App\Models\FixedIncome;
use App\Models\MonthClosure;
use App\Models\MonthlyExpense;
use App\Models\MonthlyIncome;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected MonthlyExpense $monthly_expense;
    protected MonthlyIncome $monthly_income;
    protected FixedExpense $fixed_expense;
    protected FixedIncome $fixed_income;
    protected MonthClosure $month_closure;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->monthly_expense  = new MonthlyExpense();
        $this->monthly_income   = new MonthlyIncome();
        $this->fixed_expense    = new FixedExpense();
        $this->fixed_income     = new FixedIncome();
        $this->month_closure    = new MonthClosure();

        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getValuesHome(): JsonResponse
    {
        $month_closure = $this->month_closure->where('economy', '<>', null)->orderBy('id', 'DESC')->first();

        $month_year = date('m-Y', strtotime('+1 month', strtotime("$month_closure->reference_year-" . str_pad($month_closure->reference_month, 2, '0', STR_PAD_LEFT) . "-01")));
        $exp_date   = explode('-',$month_year);
        $month      = (int)$exp_date[0];
        $year       = (int)$exp_date[1];

        $monthly_expenses = $this->monthly_expense->where(array(
                'reference_month' => $month,
                'reference_year' => $year)
        )->get();
        $fixed_expenses = $this->fixed_expense->get();

        $monthly_incomes = $this->monthly_income->where(array(
                'reference_month' => $month,
                'reference_year' => $year)
        )->get();
        $fixed_incomes = $this->fixed_income->get();

        $expenses = array_sum(
            [
                array_sum(
                    array_map(
                        function ($expense){
                            return (float)$expense['price'];
                        }, $fixed_expenses->toArray()
                    )
                ),
                array_sum(
                    array_map(
                        function ($expense){
                            return (float)$expense['price'];
                        }, $monthly_expenses->toArray()
                    )
                )
            ]
        );

        $incomes = array_sum(
            [
                array_sum(
                    array_map(
                        function ($income){
                            return (float)$income['price'];
                        }, $fixed_incomes->toArray()
                    )
                ),
                array_sum(
                    array_map(
                        function ($income){
                            return (float)$income['price'];
                        }, $monthly_incomes->toArray()
                    )
                )
            ]
        );

        $leisure_value = Auth::user()->savings_percentage;

        return response()->json(array(
            'incomes'                   => (float)number_format($incomes, 2, '.', ''),
            'expenses'                  => (float)number_format($expenses, 2, '.', ''),
            'liquid'                    => (float)number_format(($incomes - $expenses), 2, '.', ''),
            'economy'                   => (float)number_format((((($expenses / $incomes) * 100) - 100) * (-1)), 2, '.', ''),
            'savings_goal'              => (float)number_format(($incomes * ($leisure_value / 100)), 2, '.', ''),
            'leisure_value'             => $leisure_value,
            'amount_available_for_use'  => (float)number_format(($incomes - $expenses) - ($incomes * ($leisure_value / 100)), 2, '.', ''),
        ));
    }
}
