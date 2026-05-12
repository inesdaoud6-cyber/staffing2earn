<?php

namespace App\Filament\Candidate\Pages;
namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'logout';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function () {
            Auth::logout();
            Session::invalidate();
            Session::regenerateToken();

            return redirect()->route('home');
        });
    }
}