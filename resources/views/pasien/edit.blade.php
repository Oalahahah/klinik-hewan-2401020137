<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Pasien Hewan</title>
</head>
<body>
    <h1>Edit Data Pasien Hewan</h1>
    <p><a href="{{ route('pasien.index') }}">Kembali ke Daftar Pasien</a></p>
    <hr>

    {{-- Feedback Pesan Kesalahan Sistem --}}
    @if (session('error'))
        <p><strong>Error:</strong> {{ session('error') }}</p>
    @endif

    {{-- Pesan Validasi Server-Side --}}
    @if ($errors->any())
        <p><strong>Terjadi kesalahan validasi data:</strong></p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- Form Edit Pasien Hewan --}}
    <form method="POST" action="{{ route('pasien.update', $pasien['id']) }}">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_pasien">Kode Pasien (tepat 10 karakter alfanumerik):</label><br>
            <input type="text" id="kode_pasien" name="kode_pasien" value="{{ old('kode_pasien', $pasien['kode_pasien']) }}" maxlength="10" required>
        </p>
        <p>
            <label for="nama_hewan">Nama Hewan (2-100 karakter):</label><br>
            <input type="text" id="nama_hewan" name="nama_hewan" value="{{ old('nama_hewan', $pasien['nama_hewan']) }}" maxlength="100" required>
        </p>
        <p>
            <label for="nomor_rekam">Nomor Rekam Medis (5-30 karakter):</label><br>
            <input type="text" id="nomor_rekam" name="nomor_rekam" value="{{ old('nomor_rekam', $pasien['nomor_rekam']) }}" maxlength="30" required>
        </p>
        <p>
            <label for="usia_tahun">Usia (Tahun, 0-30):</label><br>
            <input type="number" id="usia_tahun" name="usia_tahun" value="{{ old('usia_tahun', $pasien['usia_tahun']) }}" min="0" max="30" required>
        </p>
        <p>
            <label for="jenis_hewan_id">Jenis Hewan:</label><br>
            <select id="jenis_hewan_id" name="jenis_hewan_id" required>
                <option value="">-- Pilih Jenis Hewan --</option>
                @foreach ($jenisHewan as $j)
                    <option value="{{ $j['id'] }}" {{ (string) old('jenis_hewan_id', $pasien['jenis_hewan_id']) === (string) $j['id'] ? 'selected' : '' }}>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Perbarui Pasien</button>
            <a href="{{ route('pasien.index') }}">Batal</a>
        </p>
    </form>
</body>
</html>
