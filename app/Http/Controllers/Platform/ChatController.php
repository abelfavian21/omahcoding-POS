<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\PlatformAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Inbox chat landing page untuk admin platform: lihat, ambil alih, balas, kembalikan ke AI, tutup. */
class ChatController extends Controller
{
    private const FILTERS = ['all', 'needs', 'ai', 'human', 'closed'];

    public function index(Request $request)
    {
        return view('platform.chat.index', [
            'conversations' => $this->list($request),
            'filter' => $this->filter($request),
            'needsCount' => ChatConversation::where('needs_admin', true)->where('status', '!=', ChatConversation::CLOSED)->count(),
        ]);
    }

    /** Polling inbox: potongan HTML baris (di-escape Blade). JSON supaya sesi habis terdeteksi (401), bukan halaman login. */
    public function poll(Request $request): JsonResponse
    {
        return response()->json(['html' => view('platform.chat._rows', ['conversations' => $this->list($request)])->render()]);
    }

    public function show(ChatConversation $conversation)
    {
        $conversation->load('handler');
        $messages = $conversation->messages()->with('admin:id,name')->orderBy('id')->get();

        return view('platform.chat.show', compact('conversation', 'messages'));
    }

    /** Polling detail: pesan baru + status (halaman reload sendiri bila status berubah). */
    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $after = max(0, (int) $request->query('after', 0));

        $rows = $conversation->messages()->with('admin:id,name')->where('id', '>', $after)->orderBy('id')->limit(100)->get()
            ->map(fn ($m) => $m->toWidget() + ['admin' => $m->admin?->name])->all();

        return response()->json(['status' => $conversation->status, 'messages' => $rows]);
    }

    public function takeover(ChatConversation $conversation): RedirectResponse
    {
        // UPDATE bersyarat = atomik: dua admin menekan bersamaan, hanya satu yang menang.
        $ok = ChatConversation::whereKey($conversation->id)->where('status', ChatConversation::AI)
            ->update(['status' => ChatConversation::HUMAN, 'handled_by' => auth('platform')->id(), 'needs_admin' => false]);

        if (! $ok) {
            return $this->back($conversation)->withErrors(['conversation' => 'Percakapan ini tidak bisa diambil alih (sudah ditangani atau ditutup).']);
        }

        PlatformAuditLog::record('chat.takeover', 'chat#'.$conversation->id, ['conversation_id' => $conversation->id]);

        return $this->back($conversation)->with('success', 'Anda mengambil alih percakapan. AI berhenti membalas.');
    }

    public function release(ChatConversation $conversation): RedirectResponse
    {
        $ok = ChatConversation::whereKey($conversation->id)->where('status', ChatConversation::HUMAN)
            ->update(['status' => ChatConversation::AI, 'handled_by' => null, 'needs_admin' => false]);

        if (! $ok) {
            return $this->back($conversation)->withErrors(['conversation' => 'Percakapan ini tidak sedang ditangani admin.']);
        }

        PlatformAuditLog::record('chat.release', 'chat#'.$conversation->id, ['conversation_id' => $conversation->id]);

        return $this->back($conversation)->with('success', 'Percakapan dikembalikan ke AI.');
    }

    public function close(ChatConversation $conversation): RedirectResponse
    {
        $ok = ChatConversation::whereKey($conversation->id)->where('status', '!=', ChatConversation::CLOSED)
            ->update(['status' => ChatConversation::CLOSED, 'needs_admin' => false]);

        if (! $ok) {
            return $this->back($conversation)->withErrors(['conversation' => 'Percakapan sudah ditutup.']);
        }

        PlatformAuditLog::record('chat.close', 'chat#'.$conversation->id, ['conversation_id' => $conversation->id]);

        return $this->back($conversation)->with('success', 'Percakapan ditutup.');
    }

    public function reply(Request $request, ChatConversation $conversation): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:'.(int) config('chat.admin_message_max_length')]]);

        $body = ChatMessage::sanitize((string) $request->input('message'));
        if ($body === '') {
            return $this->back($conversation)->withErrors(['message' => 'Balasan tidak boleh kosong.'])->withInput();
        }
        if ($conversation->status !== ChatConversation::HUMAN) {
            return $this->back($conversation)->withErrors(['message' => 'Ambil alih percakapan dulu sebelum membalas.'])->withInput();
        }

        $conversation->addMessage('admin', $body, auth('platform')->id());

        // isi pesan tidak masuk audit (data pengunjung); cukup panjangnya
        PlatformAuditLog::record('chat.reply', 'chat#'.$conversation->id, ['conversation_id' => $conversation->id, 'length' => mb_strlen($body)]);

        return $this->back($conversation);
    }

    private function list(Request $request)
    {
        $filter = $this->filter($request);
        $q = ChatConversation::query()->with('latestMessage')->withCount('messages');

        if ($filter === 'needs') {
            $q->where('needs_admin', true)->where('status', '!=', ChatConversation::CLOSED);
        } elseif ($filter !== 'all') {
            $q->where('status', $filter);
        }

        return $q->orderByDesc('needs_admin')->orderByDesc('last_active_at')->orderByDesc('id')->paginate(25)->withQueryString();
    }

    private function filter(Request $request): string
    {
        $f = $request->query('status', 'all');

        return is_string($f) && in_array($f, self::FILTERS, true) ? $f : 'all';
    }

    private function back(ChatConversation $conversation): RedirectResponse
    {
        return redirect()->route('platform.chat.show', $conversation);
    }
}
