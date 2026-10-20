<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('name', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%")->orWhere('email', 'like', "%$s%")->orWhere('code', 'like', "%$s%")))
            ->when($request->tier, fn ($q, $t) => $q->where('tier', $t))
            ->when($request->filter === 'active', fn ($q) => $q->where('last_visit_at', '>=', now()->subWeek()))
            ->when($request->filter === 'inactive', fn ($q) => $q->where(fn ($w) => $w->whereNull('last_visit_at')->orWhere('last_visit_at', '<', now()->subDays(30))));

        $customers = $query->orderByDesc('last_visit_at')->paginate(10)->withQueryString();

        $selected = $request->selected
            ? Customer::find($request->selected)
            : $customers->first();
        $recent = $selected ? $selected->orders()->with('items')->latest('paid_at')->limit(3)->get() : collect();

        $total = Customer::count();
        $birthdays = Customer::whereNotNull('birthday')->get()->filter(function ($c) {
            $next = $c->birthday->copy()->year(now()->year);
            if ($next->isPast() && ! $next->isToday()) $next->addYear();
            return today()->diffInDays($next) <= 7;
        })->count();

        return view('customers.index', [
            'customers' => $customers,
            'selected' => $selected,
            'recent' => $recent,
            'stats' => [
                'total' => $total,
                'newWeek' => Customer::where('created_at', '>=', now()->subWeek())->count(),
                'vip' => Customer::whereIn('tier', ['gold', 'vip'])->count(),
                'ltv' => (float) Customer::avg('total_spent'),
                'repeat' => $total ? round(Customer::where('visits', '>', 1)->count() / $total * 100, 1) : 0,
            ],
            'tierCounts' => Customer::selectRaw('tier, COUNT(*) as c')->groupBy('tier')->pluck('c', 'tier'),
            'birthdays' => $birthdays,
        ]);
    }

    public function create()
    {
        return view('customers.form', ['customer' => new Customer(['tier' => 'bronze'])]);
    }

    public function store(Request $request)
    {
        $customer = Customer::create($this->validated($request) + ['tier' => 'bronze']);

        // Jika dibuat dari halaman kasir (tahap 2) kembali ke sana
        if ($request->input('return_to') === 'pos') {
            return redirect()->route('pos', ['customer' => $customer->id]);
        }

        return redirect()->route('customers.index', ['selected' => $customer->id])
            ->with('success', "Pelanggan {$customer->name} ditambahkan.");
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer));

        return redirect()->route('customers.index', ['selected' => $customer->id])->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete(); // orders.customer_id = null, riwayat transaksi tetap ada

        return redirect()->route('customers.index')->with('success', 'Pelanggan dihapus.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'allergy' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // tier = benefit finansial: hanya admin yang boleh mengubah (kasir tidak bisa menaikkan tier teman/diri sendiri)
        if ($request->user()->isAdmin()) {
            $rules['tier'] = ['required', 'in:bronze,silver,gold,vip'];
        }

        return $request->validate($rules);
    }
}
