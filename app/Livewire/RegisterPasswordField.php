<?php

namespace App\Livewire;

use App\Rules\StrongPassword;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;

class RegisterPasswordField extends Component
{
    public bool $passwordValid = false;
    public string $password = '';
    public string $name = '';
    public string $email = '';

    protected function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'max:100',
                new StrongPassword($this->email, $this->name),
            ],
        ];
    }

    public function updatedPassword(): void
    {
        $this->checkPassword();
    }

    #[On('registration-context-updated')]
    public function updateContext(
        string $name = '',
        string $email = ''
    ): void {
        $this->name = $name;
        $this->email = $email;

        $this->checkPassword();
    }

    private function checkPassword(): void
    {
        $this->resetValidation('password');
        $this->passwordValid = false;

        if ($this->password !== '') {
            $validator = Validator::make(
                ['password' => $this->password],
                $this->rules()
            );

            if ($validator->fails()) {
                $this->setErrorBag($validator->errors());
            } else {
                $this->passwordValid = true;
            }
        }

        $this->dispatch('password-validation-updated', valid: $this->passwordValid);
    }

    #[On('clear-password-error')]
    public function clearPasswordError(): void
    {
        $this->resetValidation('password');
    }

    public function render()
    {
        return view('livewire.register-password-field');
    }
}
