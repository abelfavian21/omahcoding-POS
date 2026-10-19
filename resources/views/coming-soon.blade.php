@extends('layouts.app')
@section('title', $title)
@section('content')
  <div class="card p-8 sm:p-10 text-center max-w-lg mx-auto mt-10 sm:mt-16">
    <div class="w-14 h-14 mx-auto rounded-2xl bg-[#181829] text-lime-accent grid place-items-center mb-4"><span class="material-symbols-outlined" style="font-size:30px">construction</span></div>
    <h1 class="text-2xl font-extrabold tracking-tight mb-2">{{ $title }}</h1>
    <p class="text-slate-500 text-sm">{{ $note ?? 'maintain.' }}</p>
    <a href="{{ route('dashboard') }}" class="btn-dark text-lime-accent mt-6">Kembali ke Dashboard</a>
  </div>
@endsection
