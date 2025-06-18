<div class="row mb-3">
    <div class="col-md-6">
        <label for="date" class="form-label">Tanggal Biaya <span class="text-danger">*</span></label>
        {{-- [PERBAIKAN] Menambahkan format 'Y-m-d' pada tanggal --}}
        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', isset($operationalExpense) ? $operationalExpense->date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="amount" class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
        <input type="number" step="any" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $operationalExpense->amount ?? '') }}" required>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <label for="category" class="form-label">Kategori Biaya <span class="text-danger">*</span></label>
        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach($categories as $category)
            <option value="{{ $category }}" {{ old('category', $operationalExpense->category ?? '') == $category ? 'selected' : '' }}>{{ $category }}</option>
            @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
     <div class="col-md-6">
        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $operationalExpense->description ?? '') }}" required>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mb-3">
    <label for="notes" class="form-label">Catatan (Opsional)</label>
    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $operationalExpense->notes ?? '') }}</textarea>
</div>
<div class="d-flex justify-content-end">
    <a href="{{ route('karung.operational-expenses.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
    <button type="submit" class="btn btn-primary">Simpan</button>
</div>