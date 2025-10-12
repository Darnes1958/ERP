<?php
namespace App\Http\Responses;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class LogoutResponse extends \Filament\Auth\Http\Responses\LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        if (Filament::getCurrentOrDefaultPanel()->getId() === 'admin') {
            return redirect()->to(Filament::getLoginUrl());
        }
        return parent::toResponse($request);
    }
}
