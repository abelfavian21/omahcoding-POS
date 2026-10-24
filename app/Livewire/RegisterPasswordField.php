<?php

namespace App\Livewire;

use App\Rules\StrongPassword;
use Livewire\Attributes\On;
use Livewire\Component;

class RegisterPasswordField extends Component
{
    public string $password = '';

    public string $ownerName = '';

    public string $email = '';

    public function updatedPassword(): void
    {
        $this->checkPassword();
    }

    #[On('registration-context-updated')]
    public function syncContext(string $name = '', string $email = ''): void
    {
        $this->ownerName = $name;
        $this->email = $email;

        if ($this->password !== '') {
            $this->checkPassword();
        }
    }

    public function render()
    {
        return view('livewire.register-password-field');
    }

    private function checkPassword(): void
    {
        $validator = validator(
            ['password' => $this->password],
            ['password' => ['required', 'string', 'max:100', new StrongPassword($this->email, $this->ownerName)]],
        );

        $this->resetErrorBag('password');

        if ($validator->fails()) {
            $this->addError('password', $validator->errors()->first('password'));
        }

        $this->dispatch('password-validation-updated', valid: ! $validator->fails());
    }
}
