<div class="header">
    {{-- Menampilkan informasi toko dari settings --}}
    <h2>{{ $settings['store_name'] ?? config('app.name', 'TMT Management') }}</h2>
    <p>{{ $settings['store_address'] ?? '' }}</p>
    <p>Telp: {{ $settings['store_phone'] ?? '' }}</p>
</div>

<hr style="border: 0.5px solid black;">

<div class="report-title">
    <h1>{{ $title }}</h1>
    {{-- Tampilkan periode tanggal jika ada --}}
    @if(isset($startDate) && isset($endDate))
    <p style="text-align:center; margin-top:0;">
        Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua Waktu' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua Waktu' }}
    </p>
    @else
    <p style="text-align:center; margin-top:0;">
        Dicetak pada: {{ now()->format('d M Y, H:i') }}
    </p>
    @endif
</div>