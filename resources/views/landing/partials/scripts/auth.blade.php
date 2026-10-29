@verbatim
    <script>
        (() => {

            const $ = id => document.getElementById(id);

            let passwordIsValid = false;
            let emailOtpVerified = false;
            let currentStep = 1;
            let isSubmitting = false;
            let lockedUntil = 0;

            /* ---------- Helper error per field ---------- */
            const touched = new Set();

            const setFieldError = (id, msg) => {
                const el = $(id);
                const err = $('err-' + (el?.name || id));
                if (el) {
                    el.classList.toggle('border-rose-500', !!msg);
                    el.classList.toggle('border-slate-200', !msg);
                    el.setAttribute('aria-invalid', msg ? 'true' : 'false');
                }
                if (err) {
                    err.textContent = msg || '';
                    err.classList.toggle('hidden', !msg);
                }
            };

            /* ---------- Aturan validasi (samakan dengan RegisterController) ---------- */
            const rules = {
                'reg-owner-name': v => {
                    v = v.trim();
                    if (!v) return 'Nama pemilik wajib diisi.';
                    if (v.length < 3) return 'Nama pemilik minimal 3 karakter.';
                    if (v.length > 120) return 'Nama pemilik maksimal 120 karakter.';
                    return '';
                },
                'reg-business-name': v => {
                    v = v.trim();
                    if (!v) return 'Nama usaha wajib diisi.';
                    if (v.length < 2) return 'Nama usaha minimal 2 karakter.';
                    if (v.length > 120) return 'Nama usaha maksimal 120 karakter.';
                    return '';
                },
                'reg-email': v => {
                    v = v.trim();
                    if (!v) return 'Email wajib diisi.';
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) return 'Format email tidak valid.';
                    return '';
                },
                'reg-phone': v => {
                    v = v.trim();
                    if (!v) return 'Nomor WhatsApp wajib diisi.';
                    if (!/^[0-9+\-\s()]+$/.test(v))
                    return 'Nomor hanya boleh berisi angka, spasi, +, - atau ().';
                    const digits = v.replace(/\D/g, '');
                    if (digits.length < 8) return 'Nomor WhatsApp minimal 8 digit.';
                    if (digits.length > 15) return 'Nomor WhatsApp maksimal 15 digit.';
                    return '';
                },
                'reg-location': v => {
                    v = v.trim();
                    if (!v) return 'Kota / lokasi operasional wajib diisi.';
                    if (v.length < 3) return 'Lokasi minimal 3 karakter.';
                    if (v.length > 120) return 'Lokasi maksimal 120 karakter.';
                    return '';
                },
            };

            const step1Fields = ['reg-owner-name', 'reg-business-name', 'reg-email', 'reg-phone'];

            const fieldMsg = id => rules[id]($(id)?.value ?? '');

            // Hitung validitas langkah. show=true -> tampilkan pesan error di field.
            function checkStep(step, show = false) {
                let ok = true;

                if (step === 1) {
                    step1Fields.forEach(id => {
                        const msg = fieldMsg(id);
                        if (msg) ok = false;
                        if (show || touched.has(id)) setFieldError(id, msg);
                    });
                    const pass = $('reg-pass');
                    if (!pass || !pass.value || !passwordIsValid) ok = false;
                }

                if (step === 2) {
                    const hasCat = !!$('reg-category').value;
                    const catErr = $('step2-error');
                    if (!hasCat) ok = false;
                    if (show || touched.has('category')) {
                        catErr.textContent = hasCat ? '' : 'Pilih salah satu kategori usaha.';
                        catErr.classList.toggle('hidden', hasCat);
                    }
                    const msg = fieldMsg('reg-location');
                    if (msg) ok = false;
                    if (show || touched.has('reg-location')) setFieldError('reg-location', msg);
                }

                if (step === 3) {
                    const agreed = $('agree-terms').checked;
                    if (!agreed) ok = false;
                    if (show || touched.has('agree-terms')) {
                        const e = $('step3-error');
                        e.textContent = agreed ? '' : 'Centang persetujuan ketentuan layanan terlebih dahulu.';
                        e.classList.toggle('hidden', agreed);
                    }
                }
                return ok;
            }

            // Perbarui status tombol "lanjut" / "submit" sesuai langkah aktif.
            function refreshButtons() {
                const locked = Date.now() < lockedUntil;
                const b1 = $('btn-next-step-1'),
                    b2 = $('btn-next-step-2'),
                    b3 = $('btn-submit');
                if (b1) b1.disabled = !checkStep(1) || !emailOtpVerified;
                if (b2) b2.disabled = !checkStep(2);
                if (b3) b3.disabled = isSubmitting || locked || !(checkStep(1) && checkStep(2) && checkStep(3));
            }

            function onFieldInput(e) {
                touched.add(e.target.id);
                // tampilkan error langkah yang sedang aktif
                checkStep(currentStep);
                refreshButtons();
            }

            document.addEventListener('password-validation-updated', event => {
                passwordIsValid = event.detail.valid === true;
                refreshButtons();
            });

            [...step1Fields, 'reg-location', 'agree-terms'].forEach(id => {
                const f = $(id);
                if (!f) return;
                f.addEventListener('input', onFieldInput);
                f.addEventListener('change', onFieldInput);
                f.addEventListener('blur', onFieldInput);
            });
            // Password (Livewire): ikut hidupkan/matikan tombol saat diketik
            $('reg-pass')?.addEventListener('input', refreshButtons);

            refreshButtons();

            const auth = $('auth-section');
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;



            // user sudah login: form registrasi tidak dirender (hanya kartu "Kamu sedang masuk")
            if (!auth || !auth.dataset.initialTab) {
                window.switchAuthTab = () => {};
                return;
            }

            /* ---------- Tab Masuk / Registrasi ---------- */
            const ON =
                'py-3 px-3 sm:py-3.5 sm:px-6 rounded-2xl font-bold text-sm bg-white text-blue-600 shadow-sm transition-all';
            const OFF =
                'py-3 px-3 sm:py-3.5 sm:px-6 rounded-2xl font-bold text-sm text-slate-600 hover:text-slate-900 transition-all';
            window.switchAuthTab = tab => {
                const login = tab === 'login';
                $('view-login').classList.toggle('hidden', !login);
                $('view-register').classList.toggle('hidden', login);
                $('tab-login-btn').className = login ? ON : OFF;
                $('tab-register-btn').className = login ? OFF : ON;
            };
            const MON = 'py-2.5 rounded-lg bg-white text-blue-600 shadow-sm',
                MOFF = 'py-2.5 rounded-lg text-slate-500';
            window.switchLoginMode = mode => {
                const pin = mode === 'pin';
                $('form-email').classList.toggle('hidden', pin);
                $('form-pin').classList.toggle('hidden', !pin);
                $('mode-email').className = pin ? MOFF : MON;
                $('mode-pin').className = pin ? MON : MOFF;
            };

            /* ---------- Wizard registrasi ---------- */
            const badge = (i, state) => {
                const b = $('step-badge-' + i),
                    l = $('step-label-' + i);
                const base =
                    'w-10 h-10 rounded-full font-black text-sm flex items-center justify-center transition-all ';
                if (state === 'done') {
                    b.className = base + 'bg-emerald-600 text-white shadow-md';
                    b.innerHTML = '<span class="material-symbols-outlined" style="font-size:20px">check</span>';
                    l.className = 'text-[11px] font-bold text-emerald-600 mt-2';
                } else if (state === 'active') {
                    b.className = base + 'bg-blue-600 text-white shadow-md';
                    b.textContent = i;
                    l.className = 'text-[11px] font-bold text-blue-600 mt-2';
                } else {
                    b.className = base + 'bg-slate-100 text-slate-400';
                    b.textContent = i;
                    l.className = 'text-[11px] font-bold text-slate-400 mt-2';
                }
            };
            window.goToStep = step => {
                ['1', '2', '3'].forEach(n => $('step-' + n + '-content').classList.add('hidden'));
                $('register-form').classList.remove('hidden');
                $('step-success').classList.add('hidden');
                if (step === 'success') {
                    $('register-form').classList.add('hidden');
                    $('step-success').classList.remove('hidden');
                    [1, 2, 3].forEach(i => badge(i, 'done'));
                    $('stepper-line').style.width = '100%';
                    return;
                }
                if (step === 3) fillReview();
                currentStep = step;
                $('step-' + step + '-content').classList.remove('hidden');
                $('stepper-line').style.width = ['0%', '50%', '100%'][step - 1];
                [1, 2, 3].forEach(i => badge(i, i < step ? 'done' : i === step ? 'active' : 'idle'));
                if (step === 2) touched.add('category');
                checkStep(step);
                refreshButtons();
            };
            const fail = (id, msg) => {
                const e = $(id);
                e.textContent = msg;
                e.classList.remove('hidden');
            };

            window.nextStep = from => {
                if (from === 1) {
                    if (!checkStep(1, true)) {
                        const bad = step1Fields.find(id => fieldMsg(id));
                        $(bad || 'reg-pass')?.focus();
                        return;
                    }

                    if (!emailOtpVerified) {
                        if (otpMessage) {
                            otpMessage.textContent = 'Verifikasi email terlebih dahulu.';
                        }

                        otpSection?.classList.remove('hidden');
                        otpEmail?.focus();
                        return;
                    }

                    goToStep(2);
                } else if (from === 2) {
                    if (!checkStep(2, true)) {
                        if (fieldMsg('reg-location')) $('reg-location').focus();
                        return;
                    }
                    goToStep(3);
                }
            };

            const otpEmail = $('reg-email');
            const sendOtpButton = $('btn-send-otp');
            const verifyOtpButton = $('btn-verify-otp');
            const resendOtpButton = $('btn-resend-otp');
            const otpSection = $('otp-section');
            const otpInput = $('reg-otp');
            const otpMessage = $('otp-message');
            const verifiedMessage = $('otp-verified-message');

            let otpCountdownTimer = null;

            function startOtpCountdown(seconds = 60) {
                const resendButton = $('btn-resend-otp');

                if (!resendButton) return;

                clearInterval(otpCountdownTimer);

                let remaining = seconds;
                resendButton.disabled = true;
                resendButton.textContent = `Kirim ulang kode (${remaining} detik)`;

                otpCountdownTimer = setInterval(() => {
                    remaining--;

                    if (remaining <= 0) {
                        clearInterval(otpCountdownTimer);
                        otpCountdownTimer = null;
                        resendButton.disabled = false;
                        resendButton.textContent = 'Kirim ulang kode';
                        return;
                    }

                    resendButton.textContent = `Kirim ulang kode (${remaining} detik)`;
                }, 1000);
            }

            async function sendRegistrationOtp(button) {

                const email = otpEmail.value.trim().toLowerCase();

                if (!otpEmail.checkValidity() || !email) {
                    otpEmail.reportValidity();
                    return;
                }

                button.disabled = true;
                otpSection?.classList.remove('hidden');

                if (otpMessage) {
                    otpMessage.textContent = 'Mengirim kode OTP...';
                }

                try {
                    const response = await fetch(sendOtpButton.dataset.sendUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('register-form')
                                .querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            email
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal mengirim OTP.');
                    }

                    otpSection?.classList.remove('hidden');

                    if (otpMessage) {
                        otpMessage.textContent = result.message;
                    }

                    startOtpCountdown(60);

                    if (otpInput) otpInput.focus();

                } catch (error) {
                    if (otpMessage) otpMessage.textContent = error.message;
                    console.error('Pengiriman OTP gagal:', error);
                } finally {
                    button.disabled = false;
                }
            }

            sendOtpButton?.addEventListener('click', () => {
                sendRegistrationOtp(sendOtpButton);
            });

            resendOtpButton?.addEventListener('click', () => {
                sendRegistrationOtp(resendOtpButton);
            });

            verifyOtpButton?.addEventListener('click', async () => {
                const email = otpEmail.value.trim().toLowerCase();
                const otp = otpInput.value.trim();

                if (!/^\d{6}$/.test(otp)) {
                    if (otpMessage) {
                        otpMessage.textContent = 'Masukkan kode OTP 6 digit.';
                    }
                    return;
                }

                verifyOtpButton.disabled = true;

                try {
                    const response = await fetch(verifyOtpButton.dataset.verifyUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': $('register-form')
                                .querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            email,
                            otp
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Verifikasi OTP gagal.');
                    }

                    emailOtpVerified = true;

                    if (otpMessage) otpMessage.textContent = result.message;
                    verifiedMessage?.classList.remove('hidden');
                    otpSection?.classList.add('hidden');

                    refreshButtons();

                } catch (error) {
                    if (otpMessage) otpMessage.textContent = error.message;
                    console.error('Verifikasi OTP gagal:', error);
                } finally {
                    verifyOtpButton.disabled = false;
                }
            });

            otpEmail?.addEventListener('input', () => {
                emailOtpVerified = false;

                verifiedMessage?.classList.add('hidden');
                otpSection?.classList.add('hidden');

                if (otpInput) otpInput.value = '';

                refreshButtons();
            });

            const ownerName = document.getElementById('reg-owner-name');
            const email = document.getElementById('reg-email');

            // Kirim konteks (nama & email) ke Livewire: debounce + hanya jika berubah.
            let ctxTimer = null,
                lastCtx = null;

            function sendPasswordContext() {
                clearTimeout(ctxTimer);
                const name = ownerName?.value ?? '';
                const mail = email?.value ?? '';
                const key = name + '|' + mail;
                if (key === lastCtx) return; // tidak berubah
                if (lastCtx === null && !name && !mail) { // masih kosong semua, tidak perlu kirim
                    lastCtx = key;
                    return;
                }
                lastCtx = key;
                Livewire.dispatch('registration-context-updated', {
                    name,
                    email: mail
                });
            }

            function updatePasswordContext() {
                clearTimeout(ctxTimer);
                ctxTimer = setTimeout(sendPasswordContext, 600);
            }

            ownerName?.addEventListener('input', updatePasswordContext);
            email?.addEventListener('input', updatePasswordContext);
            // Pastikan konteks terbaru sudah terkirim sebelum password divalidasi
            $('reg-pass')?.addEventListener('focus', sendPasswordContext);
            $('reg-pass')?.addEventListener('keydown', sendPasswordContext, {
                once: false
            });

            /* ---------- Kategori usaha ---------- */
            const cards = [...document.querySelectorAll('.cat-card')];
            const paintCards = () => {
                const cur = $('reg-category').value;
                cards.forEach(c => {
                    const sel = c.dataset.slug === cur && c.dataset.active === '1';
                    c.classList.toggle('border-blue-600', sel);
                    c.classList.toggle('bg-blue-50/50', sel);
                    c.classList.toggle('border-slate-200', !sel);
                    c.classList.toggle('bg-white', !sel);
                    const ck = c.querySelector('.check-icon');
                    ck.classList.toggle('hidden', !sel);
                    ck.classList.toggle('flex', sel);
                });
            };
            cards.forEach(c => c.addEventListener('click', () => {
                if (c.dataset.active !== '1') return;
                $('reg-category').value = c.dataset.slug;
                touched.add('category');
                paintCards();
                checkStep(2);
                refreshButtons();
            }));

            function fillReview() {
                const cur = cards.find(c => c.dataset.slug === $('reg-category').value);
                $('review-business-name').textContent = $('reg-business-name').value;
                $('review-category').textContent = cur ? cur.dataset.name : '-';
                $('review-cat-icon').textContent = cur ? cur.dataset.icon : '';
                $('review-owner').textContent = $('reg-owner-name').value;
                $('review-email').textContent = $('reg-email').value;
                $('review-phone').textContent = $('reg-phone').value;
                $('review-scale').textContent = $('reg-outlet-count').value + ' • ' + $('reg-location').value;
            }

            $('register-form').addEventListener('submit', async e => {
                e.preventDefault();

                // Cegah submit dari Enter di langkah 1/2 & klik ganda
                if (currentStep !== 3 || isSubmitting || Date.now() < lockedUntil) {
                    if (currentStep === 1 || currentStep === 2) nextStep(currentStep);
                    return;
                }
                if (!(checkStep(1, true) && checkStep(2, true) && checkStep(3, true))) {
                    if (!checkStep(1)) goToStep(1);
                    else if (!checkStep(2)) goToStep(2);
                    return;
                }

                const form = $('register-form');
                const button = $('btn-submit');
                const errorBox = $('register-server-errors');
                const errorList = $('register-server-errors-list');
                const step3Error = $('step3-error');

                errorBox.classList.add('hidden');
                errorList.replaceChildren();
                step3Error.classList.add('hidden');

                const originalText = button.textContent;
                isSubmitting = true;
                button.disabled = true;
                button.textContent = 'Membuat toko…';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            const errors = result.errors;

                            const map = {
                                owner_name: 'reg-owner-name',
                                business_name: 'reg-business-name',
                                email: 'reg-email',
                                phone: 'reg-phone',
                                location: 'reg-location'
                            };
                            for (const [field, messages] of Object.entries(errors)) {
                                if (map[field]) {
                                    touched.add(map[field]);
                                    setFieldError(map[field], messages[0]);
                                }
                                for (const message of messages) {
                                    const li = document.createElement('li');
                                    li.textContent = message;
                                    errorList.appendChild(li);
                                }
                            }

                            errorBox.classList.remove('hidden');

                            const fieldsStep1 = [
                                'owner_name',
                                'business_name',
                                'email',
                                'phone',
                                'password'
                            ];

                            const fieldsStep2 = [
                                'category',
                                'outlet_count',
                                'location'
                            ];

                            if (Object.keys(errors).some(key => fieldsStep1.includes(key))) {
                                goToStep(1);
                            } else if (Object.keys(errors).some(key => fieldsStep2.includes(key))) {
                                goToStep(2);
                            } else {
                                goToStep(3);
                            }

                            errorBox.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });

                            return;
                        }

                        if (response.status === 429) {
                            const wait = parseInt(response.headers.get('Retry-After'), 10) || 60;
                            lockedUntil = Date.now() + wait * 1000;
                            setTimeout(refreshButtons, wait * 1000 + 50);
                            throw new Error(`Terlalu banyak percobaan. Coba lagi dalam ${wait} detik.`);
                        }
                        throw new Error(
                            result.message || 'Registrasi gagal. Silakan coba lagi.'
                        );
                    }

                    if (!result.success || !result.registered) {
                        throw new Error('Respons registrasi dari server tidak sesuai.');
                    }

                    $('success-business-name').textContent = result.registered.name;
                    $('success-category').textContent = result.registered.category;
                    $('success-code').textContent = result.registered.code;

                    goToStep('success');

                } catch (error) {
                    console.error('Registrasi gagal:', error);

                    fail(
                        'step3-error',
                        error.message || 'Terjadi kesalahan koneksi. Silakan coba lagi.'
                    );
                } finally {
                    isSubmitting = false;
                    button.textContent = originalText;
                    refreshButtons();
                }
            });

            /* ---------- Inisialisasi ---------- */
            paintCards();
            let step = auth.dataset.initialStep;
            step = step === 'success' ? step : parseInt(step, 10);
            goToStep(step);
            refreshButtons();
            switchLoginMode(auth.dataset.loginMode);
            switchAuthTab(location.hash === '#masuk' ? 'login' : auth.dataset.initialTab);
        })
        ();
    </script>
@endverbatim
