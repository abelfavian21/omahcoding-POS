<div>
    <label for="reg-pass"
        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
        Buat Kata Sandi Akun
    </label>

    <input
        id="reg-pass"
        type="password"
        name="password"
        wire:model.live.debounce.300ms="password"
        required
        minlength="8"
        autocomplete="new-password"
        placeholder="Minimal 8 karakter"
        class="w-full px-4 py-3 rounded-xl border text-sm font-semibold outline-none transition-all
        {{ $errors->has('password')
            ? 'border-rose-500 focus:ring-4 focus:ring-rose-100'
            : 'border-slate-200 focus:border-blue-600 focus:ring-4 focus:ring-blue-100' }}"
        aria-describedby="password-error"
        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">

    @error('password')
        <p id="password-error"
            class="mt-1.5 text-xs font-semibold text-rose-600"
            role="alert">
            {{ $message }}
        </p>
    @enderror
</div>