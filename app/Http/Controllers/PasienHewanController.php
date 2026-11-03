<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PDO;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class PasienHewanController extends Controller
{
    /**
     * READ: Menampilkan daftar pasien hewan, form pencarian, filter, dan form tambah data.
     */
    public function index(Request $request)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $cari = trim(strip_tags((string) $request->query('cari', '')));
            $jenisHewanId = $request->query('jenis_hewan_id');

            $sql = "SELECT p.*, j.nama_jenis
                    FROM pasien_hewan p
                    JOIN jenis_hewan j ON p.jenis_hewan_id = j.id
                    WHERE 1=1";
            $params = [];

            // Pencarian pada tiga kolom: kode_pasien, nama_hewan, dan nomor_rekam
            if ($cari !== '') {
                $sql .= " AND (p.kode_pasien LIKE ? OR p.nama_hewan LIKE ? OR p.nomor_rekam LIKE ?)";
                $like = '%' . $cari . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            // Filter berdasarkan jenis_hewan_id
            if ($jenisHewanId !== null && $jenisHewanId !== '') {
                $sql .= " AND p.jenis_hewan_id = ?";
                $params[] = (int) $jenisHewanId;
            }

            $sql .= " ORDER BY p.id ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pasien = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ambil data master jenis_hewan
            $stmtMaster = $pdo->prepare("SELECT * FROM jenis_hewan ORDER BY nama_jenis ASC");
            $stmtMaster->execute();
            $jenisHewan = $stmtMaster->fetchAll(PDO::FETCH_ASSOC);

            return view('pasien.index', compact('pasien', 'jenisHewan', 'cari', 'jenisHewanId'));
        } catch (Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return response('Terjadi kesalahan pada sistem basis data saat memuat data pasien.', 500);
        }
    }

    /**
     * CREATE: Menyimpan data pasien hewan baru.
     */
    public function store(Request $request)
    {
        // Sanitasi input teks menggunakan trim dan strip_tags
        $sanitized = [
            'kode_pasien' => trim(strip_tags((string) $request->input('kode_pasien', ''))),
            'nama_hewan' => trim(strip_tags((string) $request->input('nama_hewan', ''))),
            'nomor_rekam' => trim(strip_tags((string) $request->input('nomor_rekam', ''))),
            'usia_tahun' => $request->input('usia_tahun'),
            'jenis_hewan_id' => $request->input('jenis_hewan_id'),
        ];

        // Validasi server-side
        $validator = Validator::make($sanitized, [
            'kode_pasien' => [
                'required',
                'alpha_num',
                'size:10',
                'unique:pasien_hewan,kode_pasien',
            ],
            'nama_hewan' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'nomor_rekam' => [
                'required',
                'string',
                'min:5',
                'max:30',
                'unique:pasien_hewan,nomor_rekam',
            ],
            'usia_tahun' => [
                'required',
                'integer',
                'between:0,30',
            ],
            'jenis_hewan_id' => [
                'required',
                'integer',
                'exists:jenis_hewan,id',
            ],
        ], [
            'kode_pasien.required' => 'Kode pasien wajib diisi.',
            'kode_pasien.alpha_num' => 'Kode pasien harus alfanumerik (huruf dan angka).',
            'kode_pasien.size' => 'Kode pasien harus tepat 10 karakter.',
            'kode_pasien.unique' => 'Kode pasien sudah terdaftar.',
            'nama_hewan.required' => 'Nama hewan wajib diisi.',
            'nama_hewan.min' => 'Nama hewan minimal 2 karakter.',
            'nama_hewan.max' => 'Nama hewan maksimal 100 karakter.',
            'nomor_rekam.required' => 'Nomor rekam medis wajib diisi.',
            'nomor_rekam.min' => 'Nomor rekam medis minimal 5 karakter.',
            'nomor_rekam.max' => 'Nomor rekam medis maksimal 30 karakter.',
            'nomor_rekam.unique' => 'Nomor rekam medis sudah digunakan pasien lain.',
            'usia_tahun.required' => 'Usia hewan wajib diisi.',
            'usia_tahun.integer' => 'Usia hewan harus berupa bilangan bulat.',
            'usia_tahun.between' => 'Usia hewan harus di antara 0 sampai 30 tahun.',
            'jenis_hewan_id.required' => 'Jenis hewan wajib dipilih.',
            'jenis_hewan_id.exists' => 'Jenis hewan yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('pasien.index')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $pdo = DB::connection()->getPdo();
            $now = date('Y-m-d H:i:s');

            $sql = "INSERT INTO pasien_hewan (kode_pasien, nama_hewan, nomor_rekam, usia_tahun, jenis_hewan_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $sanitized['kode_pasien'],
                $sanitized['nama_hewan'],
                $sanitized['nomor_rekam'],
                $sanitized['usia_tahun'],
                $sanitized['jenis_hewan_id'],
                $now,
                $now,
            ]);

            return redirect()->route('pasien.index')
                ->with('success', 'Data pasien hewan berhasil disimpan.');
        } catch (Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->route('pasien.index')
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan data.');
        }
    }

    /**
     * EDIT: Menampilkan form edit untuk data pasien yang dipilih.
     */
    public function edit($id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("SELECT * FROM pasien_hewan WHERE id = ?");
            $stmt->execute([$id]);
            $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pasien) {
                abort(404, 'Data pasien tidak ditemukan.');
            }

            $stmtMaster = $pdo->prepare("SELECT * FROM jenis_hewan ORDER BY nama_jenis ASC");
            $stmtMaster->execute();
            $jenisHewan = $stmtMaster->fetchAll(PDO::FETCH_ASSOC);

            return view('pasien.edit', compact('pasien', 'jenisHewan'));
        } catch (Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return response('Terjadi kesalahan saat memuat formulir edit.', 500);
        }
    }

    /**
     * UPDATE: Memperbarui data pasien hewan di basis data.
     */
    public function update(Request $request, $id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmtCheck = $pdo->prepare("SELECT id FROM pasien_hewan WHERE id = ?");
            $stmtCheck->execute([$id]);
            if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
                abort(404, 'Data pasien tidak ditemukan.');
            }

            // Sanitasi input teks
            $sanitized = [
                'kode_pasien' => trim(strip_tags((string) $request->input('kode_pasien', ''))),
                'nama_hewan' => trim(strip_tags((string) $request->input('nama_hewan', ''))),
                'nomor_rekam' => trim(strip_tags((string) $request->input('nomor_rekam', ''))),
                'usia_tahun' => $request->input('usia_tahun'),
                'jenis_hewan_id' => $request->input('jenis_hewan_id'),
            ];

            // Validasi server-side dengan Rule::unique()->ignore($id)
            $validator = Validator::make($sanitized, [
                'kode_pasien' => [
                    'required',
                    'alpha_num',
                    'size:10',
                    Rule::unique('pasien_hewan', 'kode_pasien')->ignore($id),
                ],
                'nama_hewan' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                ],
                'nomor_rekam' => [
                    'required',
                    'string',
                    'min:5',
                    'max:30',
                    Rule::unique('pasien_hewan', 'nomor_rekam')->ignore($id),
                ],
                'usia_tahun' => [
                    'required',
                    'integer',
                    'between:0,30',
                ],
                'jenis_hewan_id' => [
                    'required',
                    'integer',
                    'exists:jenis_hewan,id',
                ],
            ], [
                'kode_pasien.required' => 'Kode pasien wajib diisi.',
                'kode_pasien.alpha_num' => 'Kode pasien harus alfanumerik (huruf dan angka).',
                'kode_pasien.size' => 'Kode pasien harus tepat 10 karakter.',
                'kode_pasien.unique' => 'Kode pasien sudah digunakan pasien lain.',
                'nama_hewan.required' => 'Nama hewan wajib diisi.',
                'nama_hewan.min' => 'Nama hewan minimal 2 karakter.',
                'nama_hewan.max' => 'Nama hewan maksimal 100 karakter.',
                'nomor_rekam.required' => 'Nomor rekam medis wajib diisi.',
                'nomor_rekam.min' => 'Nomor rekam medis minimal 5 karakter.',
                'nomor_rekam.max' => 'Nomor rekam medis maksimal 30 karakter.',
                'nomor_rekam.unique' => 'Nomor rekam medis sudah digunakan pasien lain.',
                'usia_tahun.required' => 'Usia hewan wajib diisi.',
                'usia_tahun.integer' => 'Usia hewan harus berupa bilangan bulat.',
                'usia_tahun.between' => 'Usia hewan harus di antara 0 sampai 30 tahun.',
                'jenis_hewan_id.required' => 'Jenis hewan wajib dipilih.',
                'jenis_hewan_id.exists' => 'Jenis hewan yang dipilih tidak valid.',
            ]);

            if ($validator->fails()) {
                return redirect()->route('pasien.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }

            $now = date('Y-m-d H:i:s');
            $sql = "UPDATE pasien_hewan
                    SET kode_pasien = ?,
                        nama_hewan = ?,
                        nomor_rekam = ?,
                        usia_tahun = ?,
                        jenis_hewan_id = ?,
                        updated_at = ?
                    WHERE id = ?";
            $stmtUpdate = $pdo->prepare($sql);
            $stmtUpdate->execute([
                $sanitized['kode_pasien'],
                $sanitized['nama_hewan'],
                $sanitized['nomor_rekam'],
                $sanitized['usia_tahun'],
                $sanitized['jenis_hewan_id'],
                $now,
                $id,
            ]);

            return redirect()->route('pasien.index')
                ->with('success', 'Data pasien hewan berhasil diperbarui.');
        } catch (Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->route('pasien.edit', $id)
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem saat memperbarui data.');
        }
    }

    /**
     * DELETE: Menghapus data pasien hewan terpilih.
     */
    public function destroy($id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("DELETE FROM pasien_hewan WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                abort(404, 'Data pasien tidak ditemukan.');
            }

            return redirect()->route('pasien.index')
                ->with('success', 'Data pasien hewan berhasil dihapus.');
        } catch (Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }
            report($e);
            return redirect()->route('pasien.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }
}
