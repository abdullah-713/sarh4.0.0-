<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/login.form.email.label'))
            ->email()
            ->required(false) // اختياري للـ Easter Egg
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2])
            ->extraAttributes(['id' => 'password-field']);
    }

    public function authenticate(): ?LoginResponse
    {
        // Easter Egg: "المدير" أو "المالك" في Password وEmail فارغ
        $data = $this->form->getState();

        if (empty($data['email']) && in_array($data['password'], ['المدير', 'المالك'])) {
            throw ValidationException::withMessages([
                'data.password' => [
                    '🔒 حقوق الملكية الفكرية محفوظة لصالح السيد عبدالحكيم المذهول',
                    '📜 Copyright © 2026 Mr. Abdulhakim Al-Madhoul',
                    '⚠️ يمنع استخدام أو تعديل أو نسخ أي جزء من الكود',
                    '⚠️ Unauthorized use, modification, or copying of any part of this code is strictly prohibited.',
                ],
            ]);
        }

        // إذا كان Email فارغ ولكن Password ليس easter egg
        if (empty($data['email'])) {
            throw ValidationException::withMessages([
                'data.email' => 'البريد الإلكتروني مطلوب',
            ]);
        }

        return parent::authenticate();
    }

    public function getView(): string
    {
        return 'filament.pages.auth.custom-login';
    }
}
