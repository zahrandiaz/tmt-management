{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Transaksi: {{ $customer->name }}
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <x-transaction-history-card
                :title="'Riwayat Transaksi: ' . $customer->name"
                :back-url="route('karung.customers.index')"
                back-text="Kembali ke Daftar Pelanggan"
                :description="'Menampilkan semua riwayat transaksi penjualan (yang telah selesai) untuk pelanggan ' . $customer->name . '.'"
            >
                <x-slot name="headers">
                    <tr>
                        <th>Tanggal</th>
                        <th>No. Invoice</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </x-slot>

                {{-- Perulangan kini ada di sini --}}
                @forelse ($sales as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('d-m-Y H:i') }}</td>
                        <td>{{ $transaction->invoice_number }}</td>
                        <td class="text-end">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('karung.sales.show', $transaction->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail Transaksi">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada riwayat transaksi untuk pelanggan ini.</td>
                    </tr>
                @endforelse

            </x-transaction-history-card>
            
            @if ($sales->hasPages())
                <div class="mt-3">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </x-module-layout>
</x-app-layout>