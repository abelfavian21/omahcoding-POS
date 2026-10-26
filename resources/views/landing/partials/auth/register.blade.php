{{-- ========== REGISTRASI 3 LANGKAH ========== --}}
<div id="view-register" class="p-5 sm:p-10">
    <div class="max-w-2xl mx-auto mb-10">
        <div class="flex items-center justify-between relative">
            <div class="absolute left-0 top-5 w-full h-1 bg-slate-100"></div>
            <div id="stepper-line"
                class="absolute left-0 top-5 w-0 h-1 bg-blue-600 transition-all duration-300">
            </div>
            @foreach (['Data Pemilik', 'Kategori Usaha', 'Konfirmasi Usaha'] as $i => $lbl)
                <div class="flex flex-col items-center relative">
                    <div id="step-badge-{{ $i + 1 }}"
                        class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 font-black text-sm flex items-center justify-center transition-all">
                        {{ $i + 1 }}</div>
                    <span id="step-label-{{ $i + 1 }}"
                        class="text-[11px] font-bold text-slate-400 mt-2">{{ $lbl }}</span>
                </div>
            @endforeach
        </div>
    </div>

    @if (old('_form') === 'register' && $errors->any())
        <div
            class="max-w-xl mx-auto mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700">
            <ul class="list-disc pl-4 space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="register-form" method="POST" action="{{ route('register') }}" novalidate>
        @csrf
        <input type="hidden" name="_form" value="register">
        <input type="hidden" name="category" id="reg-category" value="{{ $selectedSlug }}">

        <div id="register-server-errors"
            class="hidden max-w-xl mx-auto mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700"
            role="alert" aria-live="polite">
            <ul id="register-server-errors-list" class="list-disc pl-4 space-y-1"></ul>
        </div>

        @include('landing.partials.auth.step-1')

        @include('landing.partials.auth.step-2')

        @include('landing.partials.auth.step-3')
    </form>

    @include('landing.partials.auth.success')
</div>
