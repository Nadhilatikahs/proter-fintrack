@component('mail::message')
# Halo, {{ $user->name }}

{!! nl2br(e($reminder->message)) !!}

@isset($data['category'])
**Kategori:** {{ $data['category'] }}
@endisset

@isset($data['limit_amount'])
**Budget:** Rp {{ number_format($data['limit_amount'], 0, ',', '.') }}
@endisset

@isset($data['spent'])
**Terpakai:** Rp {{ number_format($data['spent'], 0, ',', '.') }}
@endisset

@isset($data['remaining'])
**Sisa:** Rp {{ number_format($data['remaining'], 0, ',', '.') }}
@endisset

@isset($data['progress_percent'])
**Progress goal:** {{ $data['progress_percent'] }}%
@endisset

@isset($data['target_date'])
**Target date:** {{ $data['target_date'] }}
@endisset

---

Silakan buka aplikasi **Fintrack** untuk melihat detail lengkap transaksi, budget, dan goals kamu.

Salam hangat,
**Tim Fintrack**
@endcomponent
