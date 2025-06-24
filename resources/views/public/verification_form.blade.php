<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Struk Manual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .verification-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h3 class="text-center mb-4">Verifikasi Keaslian Struk</h3>
            <p class="text-center text-muted mb-4">Masukkan 8 karakter kode unik yang tertera di bagian bawah struk Anda.</p>

            <form action="{{ route('receipt.verify.by_code') }}" method="POST">
                @csrf

                {{-- Tampilkan error dari session jika ada --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                {{-- Tampilkan error validasi jika ada --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                <div class="mb-3">
                    <label for="verification_code" class="form-label visually-hidden">Kode Verifikasi</label>
                    <input type="text" 
                           class="form-control form-control-lg text-center" 
                           name="verification_code" 
                           id="verification_code"
                           placeholder="XXXXXXXX"
                           maxlength="8" 
                           required
                           style="text-transform:uppercase; letter-spacing: 0.2em;">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>