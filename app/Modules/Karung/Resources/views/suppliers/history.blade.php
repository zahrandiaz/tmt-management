{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Riwayat Transaksi: {{ $supplier->name }}
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        <div class="container-fluid">
            <x-transaction-history-card
                :title="'Riwayat Transaksi: ' . $supplier->name"
                :back-url="route('karung.suppliers.index')"
                back-text="Kembali ke Daftar Supplier"
                :description="'Menampilkan semua riwayat transaksi pembelian (yang telah selesai) dari supplier ' . $supplier->name . '.'"
            >
                <x-slot name="headers">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Pembelian</th>
                        <th>No. Referensi</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </x-slot>

                {{-- Perulangan kini ada di sini --}}
                @forelse ($purchases as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('d-m-Y H:i') }}</td>
                        <td>{{ $transaction->purchase_code }}</td>
                        <td>{{ $transaction->purchase_reference_no ?: '-' }}</td>
                        <td class="text-end">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('karung.purchases.show', $transaction->id) }}" class="btn btn-info btn-sm text-white" title="Lihat Detail Transaksi">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada riwayat transaksi untuk supplier ini.</td>
                    </tr>
                @endforelse

            </x-transaction-history-card>

            @if ($purchases->hasPages())
                <div class="mt-3">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </x-module-layout>
</x-app-layout>