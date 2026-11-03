<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Pasien Klinik Hewan</title>
</head>
<body>
    <h1>Sistem Pasien Klinik Hewan</h1>
    <p>NIM: 2401020137 | Praktikum Pemrograman Web - Paket 2 Tipe D</p>
    <hr>

    {{-- Feedback Pesan Berhasil --}}
    @if (session('success'))
        <p><strong>Sukses:</strong> {{ session('success') }}</p>
    @endif

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
    <hr>

    {{-- Form Pencarian dan Filter --}}
    <h2>Pencarian dan Filter Pasien</h2>
    <form method="GET" action="{{ route('pasien.index') }}">
        <p>
            <label for="cari">Pencarian (Kode Pasien, Nama Hewan, No. Rekam):</label><br>
            <input type="text" id="cari" name="cari" value="{{ $cari }}" placeholder="Masukkan kata kunci pencarian">
        </p>
        <p>
            <label for="jenis_hewan_id">Filter Jenis Hewan:</label><br>
            <select id="jenis_hewan_id" name="jenis_hewan_id">
                <option value="">-- Semua Jenis Hewan --</option>
                @foreach ($jenisHewan as $j)
                    <option value="{{ $j['id'] }}" {{ (string)$jenisHewanId === (string)$j['id'] ? 'selected' : '' }}>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Cari & Filter</button>
            <a href="{{ route('pasien.index') }}">Reset</a>
        </p>
    </form>
    <hr>

    {{-- Form Tambah Pasien Baru --}}
    <h2>Form Tambah Pasien Hewan</h2>
    <form method="POST" action="{{ route('pasien.store') }}">
        @csrf
        <p>
            <label for="kode_pasien">Kode Pasien (tepat 10 karakter alfanumerik):</label><br>
            <input type="text" id="kode_pasien" name="kode_pasien" value="{{ old('kode_pasien') }}" maxlength="10" required>
        </p>
        <p>
            <label for="nama_hewan">Nama Hewan (2-100 karakter):</label><br>
            <input type="text" id="nama_hewan" name="nama_hewan" value="{{ old('nama_hewan') }}" maxlength="100" required>
        </p>
        <p>
            <label for="nomor_rekam">Nomor Rekam Medis (5-30 karakter):</label><br>
            <input type="text" id="nomor_rekam" name="nomor_rekam" value="{{ old('nomor_rekam') }}" maxlength="30" required>
        </p>
        <p>
            <label for="usia_tahun">Usia (Tahun, 0-30):</label><br>
            <input type="number" id="usia_tahun" name="usia_tahun" value="{{ old('usia_tahun') }}" min="0" max="30" required>
        </p>
        <p>
            <label for="jenis_hewan_id_input">Jenis Hewan:</label><br>
            <select id="jenis_hewan_id_input" name="jenis_hewan_id" required>
                <option value="">-- Pilih Jenis Hewan --</option>
                @foreach ($jenisHewan as $j)
                    <option value="{{ $j['id'] }}" {{ (string) old('jenis_hewan_id') === (string) $j['id'] ? 'selected' : '' }}>
                        {{ $j['nama_jenis'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Simpan Pasien</button>
        </p>
    </form>
    <hr>

    {{-- Tabel Data Pasien Hewan --}}
    <h2>Daftar Pasien Hewan</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Pasien</th>
                <th>Nama Hewan</th>
                <th>No. Rekam Medis</th>
                <th>Usia (Tahun)</th>
                <th>Status Usia</th>
                <th>Jenis Hewan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @if (count($pasien) > 0)
                @foreach ($pasien as $index => $row)
                    @php
                        $status = statusUsiaHewan($row['usia_tahun']);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['kode_pasien'] }}</td>
                        <td>{{ $row['nama_hewan'] }}</td>
                        <td>{{ $row['nomor_rekam'] }}</td>
                        <td>{{ $row['usia_tahun'] }}</td>
                        <td>
                            @if ($status === 'Dewasa')
                                Dewasa
                            @else
                                Muda
                            @endif
                        </td>
                        <td>{{ $row['nama_jenis'] }}</td>
                        <td>
                            <a href="{{ route('pasien.edit', $row['id']) }}">Edit</a>
                            <form method="POST" action="{{ route('pasien.destroy', $row['id']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8">Tidak ada data pasien yang ditemukan.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
