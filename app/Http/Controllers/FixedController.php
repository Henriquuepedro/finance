<?php

namespace App\Http\Controllers;

use App\Models\FixedExpense;
use App\Models\FixedIncome;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FixedController extends Controller
{
    private FixedExpense $fixed_expense;
    private FixedIncome $fixed_income;

    public function __construct()
    {
        $this->fixed_expense = new FixedExpense();
        $this->fixed_income = new FixedIncome();
    }

    public function expenseList(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('fixed.expense');
    }

    public function incomeList(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('fixed.income');
    }

    public function getExpenseList(): JsonResponse
    {
        $fixed_expenses = $this->fixed_expense->get();

        return response()->json(array_map(function ($expense){
            return [
                $expense['description'],
                'R$ ' . number_format($expense['price'], 2, ',', '.')
            ];
        }, $fixed_expenses->toArray()));
    }

    public function getIncomeList(): JsonResponse
    {
        $fixed_expenses = $this->fixed_income->get();

        return response()->json(array_map(function ($expense){
            return [
                $expense['description'],
                'R$ ' . number_format($expense['price'], 2, ',', '.')
            ];
        }, $fixed_expenses->toArray()));
    }

    public function store(Request $request): JsonResponse
    {
        $description    = $request->input('description');
        $type           = $request->input('type');
        $price          = $request->input('price');

        if (empty($description) || empty($type) || empty($price)) {
            return response()->json(array(
                'success' => false,
                'message' => 'Preencha todos os campos.'
            ));
        }

        if ($type === 'expense') {
            $instance_fixed = $this->fixed_expense;
        } elseif ($type === 'income') {
            $instance_fixed = $this->fixed_income;
        } else {
            return response()->json(array(
                'success' => false,
                'message' => 'Recarregue a página e tente novamente.'
            ));
        }

        $instance_fixed->setAttribute('description', $description);
        $instance_fixed->setAttribute('price', $price);
        $instance_fixed->save();

        return response()->json(array(
            'success' => true,
            'message' => 'Criado com sucesso.'
        ));
    }
}
