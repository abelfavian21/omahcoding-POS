@extends('layouts.platform')
@section('title', 'Chat pengunjung')
@section('content')
@php $tabs = ['all' => 'Semua', 'needs' => 'Perlu admin', 'ai' => 'AI', 'human' => 'Admin', 'closed' => 'Ditutup']; @endphp

<div class="mb-5">
  <h1 class="text-2xl font-extrabold tracking-tight">Chat pengunjung</h1>
  <p class="text-sm text-slate-500">Percakapan dari landing page. Daftar diperbarui otomatis tiap 5 detik.</p>
</div>

<nav class="flex gap-2 overflow-x-auto no-scrollbar mb-4" aria-label="Filter status">
  @foreach ($tabs as $key => $label)
    <a href="{{ route('platform.chat.index', ['status' => $key]) }}" class="chip {{ $filter === $key ? 'chip-on' : '' }}">{{ $label }}@if ($key === 'needs' && $needsCount) ({{ $needsCount }})@endif</a>
  @endforeach
</nav>

<div id="chat-rows" class="card overflow-hidden" data-poll-url="{{ route('platform.chat.poll', request()->query()) }}">
  @include('platform.chat._rows')
</div>
<div class="mt-4">{{ $conversations->links() }}</div>

<script>
(() => {
  const box = document.getElementById('chat-rows');
  if (!box) return;
  let busy = false;
  async function tick() {
    if (document.hidden || busy) return;
    busy = true;
    try {
      const r = await fetch(box.dataset.pollUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      if (r.status === 401 || r.status === 419) { location.reload(); return; }
      if (!r.ok) return;
      const d = await r.json();
      if (typeof d.html === 'string') box.innerHTML = d.html;   // HTML dari Blade (sudah di-escape server)
    } catch (e) { /* abaikan, coba lagi di putaran berikut */ } finally { busy = false; }
  }
  setInterval(tick, 5000);
})();
</script>
@endsection
