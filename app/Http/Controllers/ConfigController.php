<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class ConfigController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $user = $this->user->first();

        return view('config.index', compact('user'));
    }

    public function saveConfig(Request $request): Application|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $user = $this->user->first();
        $user->setAttribute('savings_percentage', $request->input('savings_percentage'));
        $user->save();

        return redirect()->route('config');
    }
}
