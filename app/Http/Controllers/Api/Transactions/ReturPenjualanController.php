<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\ReturPenjualan_h;
use App\Models\Transactions\ReturPenjualan_r;
use App\Models\Transactions\Penerimaan_h;
use Date;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturPenjualanController extends Controller
{
    /*
     * FLAG
     * null = Belum Terkunci
     * 1 = Terkunci
     */

    public function index(Request $request)
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = ReturPenjualan_h::query()
            ->select('retur_penjualan_hs.*')
            ->leftJoin('penerimaan_hs', 'retur_penjualan_hs.nopenerimaan', '=', 'penerimaan_hs.nopenerimaan')
            ->leftJoin('suppliers', 'retur_penjualan_hs.kode_supplier', '=', 'suppliers.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('retur_penjualan_hs.noretur', 'like', '%' . request('q') . '%')
                        ->orWhere('retur_penjualan_hs.nopenerimaan', 'like', '%' . request('q') . '%')
                        ->orWhere('retur_penjualan_hs.nofaktur', 'LIKE', '%' . request('q') . '%')
                        ->orWhere('suppliers.nama', 'LIKE', '%' . request('q') . '%');
                });
            })
            ->with([
                'returPenjualan_r.master_barang:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'penerimaan_h.rincian',
                'supplier',
            ])
            ->orderBy('retur_penjualan_hs.' . $req['order_by'], $req['sort']);

        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'noretur' => 'nullable',
            'nopenerimaan' => 'required',
            'nofaktur' => 'required',
            'tgl_retur' => 'nullable',
            'kode_supplier' => 'required',
            'kode_barang' => 'required',
            'nobatch' => 'required',
            'jumlah_k' => 'required',
            'satuan_k' => 'required',
            'harga' => 'required',
        ], [
            'nopenerimaan.required' => 'Nomor Penerimaan harus diisi.',
            'nofaktur.required' => 'Nomor Faktur harus diisi.',
            'kode_supplier.required' => 'Kode Supplier harus diisi.',
            'kode_barang.required' => 'Kode Barang harus diisi.',
            'nobatch.required' => 'Nomor Batch harus diisi.',
            'jumlah_k.required' => 'Jumlah harus diisi.',
            'satuan_k.required' => 'Satuan harus diisi.',
            'harga.required' => 'Harga harus diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        if (!$validated['noretur']) {
            DB::select('call noretur(@nomor)');
            $nomor = DB::table('counter')->select('noretur')->first();
            $nomor_retur = FormatingHelper::genKodeBarang($nomor->noretur, 'RPJ');
        } else {
            $nomor_retur = $request->noretur;

            $existingHeader = ReturPenjualan_h::where('noretur', $nomor_retur)->first();
            if ($existingHeader && $existingHeader->flag == '1') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data Retur Sudah Terkunci'
                ], 410);
            }
        }

        $checkPenerimaan = Penerimaan_h::where('nopenerimaan', $validated['nopenerimaan'])
            ->with(['rincian', 'suplier'])
            ->first();

        if (!$checkPenerimaan) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Penerimaan Tidak Di Temukan.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            $ReturPenjualanHeader = ReturPenjualan_h::updateOrCreate(
                [
                    'noretur' => $nomor_retur,
                ],
                [
                    'kode_user' => $user->kode,
                    'nopenerimaan' => $validated['nopenerimaan'],
                    'nofaktur' => $validated['nofaktur'],
                    'kode_supplier' => $validated['kode_supplier'],
                    'tgl_retur' => $validated['tgl_retur'] ?? now(),
                ]
            );

            if (!$ReturPenjualanHeader) {
                throw new \Exception('Retur Penjualan Header Gagal Disimpan.');
            }

            $ReturPenjualanRinci = ReturPenjualan_r::updateOrCreate(
                [
                    'noretur' => $nomor_retur,
                    'kode_barang' => $validated['kode_barang'],
                ],
                [
                    'nobatch' => $validated['nobatch'],
                    'jumlah_k' => $validated['jumlah_k'],
                    'satuan_k' => $validated['satuan_k'],
                    'harga' => $validated['harga'],
                    'kode_user' => $user->kode,
                    'returpenjualan_h_id' => $ReturPenjualanHeader->id,
                    'returpenjualan_h_noretur' => $ReturPenjualanHeader->noretur,
                ]
            );

            if (!$ReturPenjualanRinci) {
                throw new \Exception('Retur Penjualan Rinci Gagal Disimpan.');
            }

            DB::commit();

            $ReturPenjualanHeaderResult = ReturPenjualan_h::with([
                'returPenjualan_r.master_barang:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'penerimaan_h',
                'supplier',
            ])->find($ReturPenjualanHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $ReturPenjualanHeaderResult,
                'message' => 'Data Retur Penjualan berhasil disimpan'
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }

    // Lock Retur Penjualan Hanya untuk Data Draft
    public function lock_retur_penjualan(Request $request)
    {
        $validated = $request->validate([
            'noretur' => 'required',
        ], [
            'noretur.required' => 'Nomor Retur harus diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        // Jika flag awalnya bukan null = tidak boleh dikunci
        if ($existingHeader && $existingHeader->flag != null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini bukan data Draft'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Update header
            $returHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])
                ->update(['flag' => '1']); // Lock Table

            if (!$returHeader) {
                throw new \Exception('Gagal mengunci data retur.');
            }

            DB::commit();

            $returData = ReturPenjualan_h::with([
                'returPenjualan_r.master_barang:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'penerimaan_h',
                'supplier',
            ])->find($existingHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $returData,
                'message' => 'Data Retur Penjualan berhasil dikunci'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal mengunci data: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }

    // Open Lock Retur Penjualan
    public function open_lock_retur_penjualan(Request $request)
    {
        $validated = $request->validate([
            'noretur' => 'required',
        ], [
            'noretur.required' => 'Nomor Retur harus diisi.',
        ]);

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        if ($existingHeader && $existingHeader->flag == null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini belum terkunci.'
            ], 410);
        }

        // Jika flag bukan 1 = tidak boleh dibuka
        if ($existingHeader && $existingHeader->flag != '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini bukan data Retur Terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Update header
            $returHeader = ReturPenjualan_h::where('noretur', $validated['noretur'])
                ->update(['flag' => null]); // Unlock Table

            if (!$returHeader) {
                throw new \Exception('Gagal membuka kunci data retur.');
            }

            DB::commit();

            $returData = ReturPenjualan_h::with([
                'returPenjualan_r.master_barang:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'penerimaan_h',
                'supplier',
            ])->find($existingHeader->id);

            return new JsonResponse([
                'success' => true,
                'data' => $returData,
                'message' => 'Kunci Data Retur Penjualan berhasil dibuka'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal membuka kunci data: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }

    public function hapus(Request $request)
    {
        $noretur = $request->noretur;

        if (!$noretur) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Nomor retur tidak ditemukan'
            ], 400);
        }

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = ReturPenjualan_h::where('noretur', $noretur)->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        // Tidak boleh hapus jika flag = 1 (sudah terkunci)
        if ($existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur ini sudah terkunci dan tidak dapat dihapus.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Hapus rincian retur
            ReturPenjualan_r::where('noretur', $noretur)->delete();

            // Hapus header retur
            $existingHeader->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data retur penjualan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }
    public function hapus_rincian_tidak_dikunci(Request $request)
    {
        $noretur = $request->noretur;
        $kode_barang = $request->kode_barang;

        if (!$noretur || !$kode_barang) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Nomor retur dan kode barang harus diisi'
            ], 400);
        }

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }

        $existingHeader = ReturPenjualan_h::where('noretur', $noretur)->first();
        if (!$existingHeader) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur Tidak Ditemukan.'
            ], 410);
        }

        // Cek apakah header sudah terkunci
        if ($existingHeader && $existingHeader->flag == '1') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data Retur ini sudah terkunci dan tidak dapat diubah.'
            ], 410);
        }

        try {
            DB::beginTransaction();

            // Cari rincian yang akan dihapus
            $rincian = ReturPenjualan_r::where('noretur', $noretur)
                ->where('kode_barang', $kode_barang)
                ->first();

            if (!$rincian) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data rincian retur tidak ditemukan'
                ], 410);
            }

            // Hapus rincian
            $rincian->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'data' => $rincian,
                'message' => 'Data rincian retur berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data rincian: ' . $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 410);
        }
    }
}
