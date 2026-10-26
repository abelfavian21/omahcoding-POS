@extends('layouts.platform')
@section('title', 'Chat #'.$conversation->id)
@section('content')
@php
  $roles = ['visitor' => 'Pengunjung', 'ai' => 'AI', 'admin' => 'Admin'];
  $bubble = [
    'visitor' => 'self-start bg-slate-100 dark:bg-white/10',
    'ai' => 'self-end border border-card-border',
    'admin' => 'self-end bg-lime-accent text-slate-950',
  ];
  [$stLabel, $stClass] = match ($conversation->status) {
    'ai' => ['AI membalas', 'pill-green'],
    'human' => ['Admin menangani', 'pill-amber'],
    default => ['Ditutup', ''],
  };
  $btn = 'inline-flex items-center justify-center h-11 px-5 rounded-2xl border border-card-border text-sm font-bold hover:bg-black/5 dark:hover:bg-white/10';
@endphp

<a href="{{ route('platform.chat.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 mb-3">
  <span class="material-symbols-outlined" style="font-size:18px" aria-hidden="true">arrow_back</span> Semua chat
</a>

<div class="card p-5 mb-5">
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div class="min-w-0">
      <h1 class="text-2xl font-extrabold tracking-tight">Pengunjung #{{ $conversation->id }}</h1>
      <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm">
        <span class="pill {{ $stClass }}">{{ $stLabel }}</span>
        @if ($conversation->needs_admin)<span class="pill pill-red">Perlu admin</span>@endif
        <span class="text-slate-500">IP {{ $conversation->ip ?? '-' }}</span>
        <span class="text-slate-500">Mulai {{ $conversation->created_at?->format('d M Y H:i') }}</span>
        @if ($conversation->handler)<span class="text-slate-500">Ditangani: {{ $conversation->handler->name }}</span>@endif
      </div>
      <p class="mt-1 text-xs text-slate-500 truncate max-w-xl">{{ $conversation->user_agent }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
      @if ($conversation->status === 'ai')
        <form method="POST" action="{{ route('platform.chat.takeover', $conversation) }}">@csrf<button class="btn-primary">Ambil alih</button></form>
      @endif
      @if ($conversation->status === 'human')
        <form method="POST" action="{{ route('platform.chat.release', $conversation) }}">@csrf<button class="{{ $btn }}">Kembalikan ke AI</button></form>
      @endif
      @if ($conversation->status !== 'closed')
        <form method="POST" action="{{ route('platform.chat.close', $conversation) }}" onsubmit="return confirm('Tutup percakapan ini? Pengunjung tidak bisa membalas lagi.')">@csrf<button class="{{ $btn }} text-red-600">Tutup</button></form>
      @endif
    </div>
  </div>
</div>

<div id="chat-log" class="card p-4 sm:p-5 flex flex-col gap-3 max-h-[60vh] overflow-y-auto" role="log" aria-live="polite"
     data-poll-url="{{ route('platform.chat.messages', $conversation) }}" data-last-id="{{ $messages->last()?->id ?? 0 }}" data-status="{{ $conversation->status }}">
  @forelse ($messages as $m)
    <div class="max-w-[85%] {{ $bubble[$m->role] ?? '' }} rounded-2xl px-4 py-2.5 text-sm">
      <div class="text-xs font-bold opacity-70 mb-0.5">{{ $roles[$m->role] ?? $m->role }}@if ($m->role === 'admin' && $m->admin) · {{ $m->admin->name }}@endif · {{ $m->created_at?->format('H:i') }}</div>
      <div class="whitespace-pre-wrap break-words">{{ $m->body }}</div>
    </div>
  @empty
    <p class="text-sm text-slate-500" id="chat-empty">Belum ada pesan.</p>
  @endforelse
</div>

@if ($conversation->status === 'human')
  <form method="POST" action="{{ route('platform.chat.reply', $conversation) }}" class="card p-4 mt-4">
    @csrf
    <label for="reply" class="label">Balasan admin</label>
    <textarea id="reply" name="message" rows="3" maxlength="{{ (int) config('chat.admin_message_max_length') }}" required class="input mt-2 w-full" placeholder="Tulis balasan...">{{ old('message') }}</textarea>
    <div class="mt-3 flex justify-end"><button class="btn-primary">Kirim balasan</button></div>
  </form>
@elseif ($conversation->status === 'ai')
  <p class="mt-4 text-sm text-slate-500">AI masih membalas pengunjung. Tekan "Ambil alih" untuk membalas sendiri.</p>
@else
  <p class="mt-4 text-sm text-slate-500">Percakapan ini sudah ditutup.</p>
@endif

<script>
(() => {
  const log = document.getElementById('chat-log');
  if (!log) return;
  const labels = { visitor: 'Pengunjung', ai: 'AI', admin: 'Admin' };
  const cls = {
    visitor: 'self-start bg-slate-100 dark:bg-white/10',
    ai: 'self-end border border-card-border',
    admin: 'self-end bg-lime-accent text-slate-950',
  };
  let last = parseInt(log.dataset.lastId, 10) || 0;
  const status = log.dataset.status;
  let busy = false;

  log.scrollTop = log.scrollHeight;

  function add(m) {
    const wrap = document.createElement('div');
    wrap.className = 'max-w-[85%] rounded-2xl px-4 py-2.5 text-sm ' + (cls[m.role] || '');
    const head = document.createElement('div');
    head.className = 'text-xs font-bold opacity-70 mb-0.5';
    const t = m.at ? new Date(m.at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';
    head.textContent = (labels[m.role] || m.role) + (m.role === 'admin' && m.admin ? ' · ' + m.admin : '') + (t ? ' · ' + t : '');
    const body = document.createElement('div');
    body.className = 'whitespace-pre-wrap break-words';
    body.textContent = m.body;   // textContent: tidak pernah menafsirkan HTML
    wrap.append(head, body);
    log.appendChild(wrap);
  }

  async function tick() {
    if (document.hidden || busy) return;
    busy = true;
    try {
      const r = await fetch(log.dataset.pollUrl + '?after=' + last, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      if (r.status === 401 || r.status === 419) { location.reload(); return; }
      if (!r.ok) return;
      const d = await r.json();
      const atBottom = log.scrollHeight - log.scrollTop - log.clientHeight < 80;
      const fresh = (d.messages || []).filter(m => m.id > last);
      if (fresh.length) document.getElementById('chat-empty')?.remove();
      fresh.forEach(m => { add(m); last = m.id; });
      if (fresh.length && atBottom) log.scrollTop = log.scrollHeight;
      if (d.status !== status) location.reload();   // status berubah (admin lain): tombol ikut menyesuaikan
    } catch (e) { /* coba lagi di putaran berikut */ } finally { busy = false; }
  }
  setInterval(tick, 4000);
})();
</script>
@endsection
