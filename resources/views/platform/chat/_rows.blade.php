@php $roles = ['visitor' => 'Pengunjung', 'ai' => 'AI', 'admin' => 'Admin']; @endphp
@forelse ($conversations as $c)
  @php
    $last = $c->latestMessage;
    [$stLabel, $stClass] = match ($c->status) {
      'ai' => ['AI', 'pill-green'],
      'human' => ['Admin', 'pill-amber'],
      default => ['Ditutup', ''],
    };
  @endphp
  <a href="{{ route('platform.chat.show', $c) }}" class="flex items-start gap-4 px-5 py-4 border-b border-card-border last:border-b-0 hover:bg-black/[.03] dark:hover:bg-white/5">
    <span class="material-symbols-outlined mt-0.5" aria-hidden="true">chat</span>
    <div class="min-w-0 flex-1">
      <div class="flex flex-wrap items-center gap-2">
        <span class="font-bold text-sm">Pengunjung #{{ $c->id }}</span>
        <span class="pill {{ $stClass }}">{{ $stLabel }}</span>
        @if ($c->needs_admin)<span class="pill pill-red">Perlu admin</span>@endif
      </div>
      <p class="text-sm text-slate-500 truncate mt-1">
        @if ($last)<span class="font-semibold">{{ $roles[$last->role] ?? $last->role }}:</span> {{ \Illuminate\Support\Str::limit($last->body, 120) }}@else Belum ada pesan @endif
      </p>
    </div>
    <div class="text-xs text-slate-500 shrink-0 text-right">
      <div>{{ $c->last_active_at?->diffForHumans() ?? '-' }}</div>
      <div>{{ $c->messages_count }} pesan</div>
    </div>
  </a>
@empty
  <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada percakapan.</p>
@endforelse
