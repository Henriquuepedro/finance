<?php

namespace App\Http\Controllers;

use App\Models\FixedExpense;
use App\Models\FixedIncome;
use App\Models\MonthClosure;
use App\Models\MonthlyExpense;
use App\Models\MonthlyIncome;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyController extends Controller
{
    private MonthlyExpense $monthly_expense;
    private FixedExpense $fixed_expense;
    private MonthlyIncome $monthly_income;
    private MonthClosure $month_closure;
    private FixedIncome $fixed_income;

    public function __construct()
    {
        $this->fixed_expense = new FixedExpense();
        $this->monthly_expense = new MonthlyExpense();
        $this->monthly_income = new MonthlyIncome();
        $this->month_closure = new MonthClosure();
        $this->fixed_income = new FixedIncome();
    }

    public function expenseList(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('monthly.expense');
    }

    public function incomeList(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('monthly.income');
    }

    public function store(Request $request): JsonResponse
    {
        $description    = $request->input('description');
        $type           = $request->input('type');
        $price          = $request->input('price');
        $month          = explode('/', $request->input('month'));

        if (empty($description) || empty($type) || empty($price) || empty($month) || count($month) != 2) {
            return response()->json(array(
                'success' => false,
                'message' => 'Preencha todos os campos.'
            ));
        }

        if ($type === 'expense') {
            $instance_monthly = $this->monthly_expense;
        } elseif ($type === 'income') {
            $instance_monthly = $this->monthly_income;
        } else {
            return response()->json(array(
                'success' => false,
                'message' => 'Recarregue a página e tente novamente.'
            ));
        }

        $instance_monthly->setAttribute('description', $description);
        $instance_monthly->setAttribute('price', $price);
        $instance_monthly->setAttribute('reference_month', $month[0]);
        $instance_monthly->setAttribute('reference_year', $month[1]);
        $instance_monthly->save();

        return response()->json(array(
            'success' => true,
            'message' => 'Criado com sucesso.'
        ));
    }

    public function getMonthlyExpenseList(): JsonResponse
    {
        $fixed_expenses = $this->monthly_expense->orderByRaw('reference_year ASC, reference_month ASC, created_at DESC')->get();

        return response()->json(array_map(function ($expense){
            return [
                $expense['description'],
                'R$ ' . number_format($expense['price'], 2, ',', '.'),
                str_pad($expense['reference_month'], 2, '0', STR_PAD_LEFT) . "/$expense[reference_year]"
            ];
        }, $fixed_expenses->toArray()));
    }

    public function getMonthlyIncomeList(): JsonResponse
    {
        $fixed_expenses = $this->monthly_income->get();

        return response()->json(array_map(function ($expense){
            return [
                $expense['description'],
                'R$ ' . number_format($expense['price'], 2, ',', '.'),
                str_pad($expense['reference_month'], 2, '0', STR_PAD_LEFT) . "/$expense[reference_year]"
            ];
        }, $fixed_expenses->toArray()));
    }

    private function getLiquidThisMonth($month, $year)
    {
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

        return $incomes - $expenses;
    }

    public function getAllMonthlyExpenseLastMonths(int $months = 3): JsonResponse
    {
        $where_in_month = [];
        $where_in_year  = [];
        $result_monthly_expenses = [];
        $result_monthly_economy = [];

        $month_closure = $this->month_closure->where('economy', '<>', null)->orderBy('id', 'DESC')->first();

        $month_year_this_month = date('m-Y', strtotime('+1 month', strtotime("$month_closure->reference_year-" . str_pad($month_closure->reference_month, 2, '0', STR_PAD_LEFT) . "-01")));
        $exp_date   = explode('-',$month_year_this_month);
        $month      = (int)$exp_date[0];
        $year       = (int)$exp_date[1];

        for ($i = 0; $i < $months; $i++) {
            $month_year =  date('m-Y', strtotime("-$i month", time()));
            $exp_date = explode('-',$month_year);

            $where_in_month[] =  (int)$exp_date[0];
            $where_in_year[] = (int)$exp_date[1];

            $result_monthly_expenses[$month_year] = 0;
            $result_monthly_economy[$month_year] = $this->month_closure->where(array(
                'reference_month'   => (int)$exp_date[0],
                'reference_year'    => (int)$exp_date[1]
            ))->first()->economy ?? 0;
        }
        $result_monthly_economy[$month_year_this_month] = $this->getLiquidThisMonth($month, $year);

        $monthly_expenses = $this->monthly_expense->whereIn('reference_month', $where_in_month)->whereIn('reference_year', $where_in_year)->get();
        $fixed_expenses = $this->fixed_expense->get();

        $all_fixed_expenses = array_sum(
            array_map(
                function ($expense){
                    return (float)$expense['price'];
                }, $fixed_expenses->toArray()
            )
        );

        $result_monthly_expenses = array_map(
            function () use ($all_fixed_expenses) {
                return $all_fixed_expenses;
            }, $result_monthly_expenses
        );

        foreach ($monthly_expenses as $monthly_expense) {
            $key_month = str_pad($monthly_expense['reference_month'], 2, '0', STR_PAD_LEFT) . "-$monthly_expense[reference_year]";

            if (!array_key_exists($key_month, $result_monthly_expenses)) {
                $result_monthly_expenses[$key_month] = $all_fixed_expenses;
            }

            $result_monthly_expenses[$key_month] += $monthly_expense['price'];
        }

        return response()->json(
            array(
                'expense' => array_map(
                    function($expense) {
                        return (float)number_format($expense, 2, '.', '');
                    }, array_values($result_monthly_expenses)
                ),
                'economy' => array_map(
                    function($expense) {
                        return (float)number_format($expense, 2, '.', '');
                    }, array_values($result_monthly_economy)
                )
            )
        );
    }

    public function getExpensesThisMont(): JsonResponse
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

        $all_fixed_expenses = array_sum(
            array_map(
                function ($expense){
                    return (float)$expense['price'];
                }, $fixed_expenses->toArray()
            )
        );

        $result_monthly_expenses = array_sum(
            array_map(
                function ($expense){
                    return (float)$expense['price'];
                }, $monthly_expenses->toArray()
            )
        );
        return response()->json(array(
            'fixed'     => $all_fixed_expenses,
            'monthly'   => $result_monthly_expenses
        ));
    }
}
