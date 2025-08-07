<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use App\Models\Transactions\Penerimaan_r;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    // get list of penerimaan (header & records)
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = Penerimaan_h::query()
            ->join('suppliers', 'penerimaan_hs.kode_suplier', '=', 'suppliers.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('penerimaan_hs.nopenerimaan', 'like', '%' . request('q') . '%')
                            ->orWhere('penerimaan_hs.noorder', 'like', '%' . request('q') . '%')
                            ->orWhere('suppliers.nama', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'rincian' => function ($query) {
                    $query->with(['barang']);
                },
                'suplier',
            ])
            ->select('penerimaan_hs.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function simpan(Request $request)
    {
        //return new JsonResponse($request->all());
        $validated = $request->validate([
            'nopenerimaan' => 'nullable',
            'noorder' => 'required',
            'tgl_penerimaan' => 'required',
            'nofaktur' => 'required',
            'tgl_faktur' => 'required',
            'kode_suplier' => 'required',
            'jenispajak' => 'required',
            'pajak' => 'nullable',
            'kode_barang' => 'required',
            'nobatch' => 'required',
            'tgl_exprd' => 'required',
            'jumlah_b' => 'required',
            'jumlah_k' => 'required',
            'harga' => 'required',
            'diskon_persen' => 'nullable',
            'isi' => 'required',
            'satuan_k' => 'required',
            'satuan_b' => 'required',
            'pajak_rupiah' => 'nullable',
            'diskon_rupiah' => 'nullable',
            'flag' => 'nullable',
        ], [
            'noorder.required' => 'No. Order Harus Di isi.',
            'tgl_penerimaan.required' => 'Tgl Penerimaan Harus Di isi.',
            'nofaktur.required' => 'No. Faktur Harus Di isi.',
            'tgl_faktur.required' => 'Tgl Faktur Harus Di isi.',
            'kode_suplier.required' => 'Kode Supplier Harus Di isi.',
            'jenispajak.required' => 'Jenis Pajak Harus Di isi.',
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'nobatch.required' => 'No. Batch Harus Di isi.',
            'tgl_exprd.required' => 'Tanggal Expired Harus Di isi.',
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
            'jumlah_b.required' => 'Jumlah Satuan Besar Harus Di isi.',
            'jumlah_k.required' => 'Jumlah Satuan Kecil Harus Di isi.',
            'harga.required' => 'Harga Harus Di isi.',
            'satuan_k.required' => 'Satuan Kecil Harus Di isi.',
            'satuan_b.required' => 'Satuan Besar Harus Di isi.',
        ]);

        // $user = Auth::user();
        // if (!$user) {
        //     throw new \Exception('Apakah Anda belum login?', 401);
        // }
        if (!$validated['nopenerimaan']) {
            DB::select('call nopenerimaan(@nomor)');
            $nomor = DB::table('counter')->select('nopenerimaan')->first();

            $nopenerimaan = FormatingHelper::notrans($nomor->nopenerimaan, 'PN');
        } else {
            $nopenerimaan = $request->nopenerimaan;

            // Cek apakah order sudah ada dan sudah terkunci
            $existingHeader = Penerimaan_h::where('nopenerimaan', $nopenerimaan)->first();
            if ($existingHeader && $existingHeader->flag == '1') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Data Order Sudah Terkunci'
                ], 403);
            }
        }

        try {
            DB::beginTransaction();
            $penerimaanHeader = Penerimaan_h::updateOrCreate(
                [
                    'nopenerimaan' => $nopenerimaan,
                ],
                [
                    'noorder' => $validated['noorder'],
                    'tgl_penerimaan' => $validated['tgl_penerimaan'],
                    'nofaktur' => $validated['nofaktur'],
                    'tgl_faktur' => $validated['tgl_faktur'],
                    'jenispajak' => $validated['jenispajak'],
                    'kode_user' => $user ?? 'sasa',
                    'pajak' => $validated['pajak'],
                    'kode_suplier' => $validated['kode_suplier'],
                ]
            );
            if (!$penerimaanHeader) {
                throw new \Exception('Transaksi Gagal Disimpan.');
            }
            // Buat penerimaan records untuk setiap item
            $pajak_rupiah = 0;
            $diskon_rupiah = 0;
            if($validated['jenispajak'] === 'Exclude'){
                $pajak_rupiah = $validated['harga'] * ($validated['pajak'] / 100);
            }
            if (isset($validated['diskon_persen'])) {
                $diskon_rupiah = $validated['harga'] * ($validated['diskon_persen'] / 100);
            }
            $harga_total = $validated['harga'] + $pajak_rupiah - $diskon_rupiah;
            $subtotal = $harga_total * $validated['jumlah_k'];
            $penerimaanrinci = Penerimaan_r::create(
                [
                    'nopenerimaan' => $nopenerimaan,
                    'noorder' => $validated['noorder'],
                    'kode_barang' => $validated['kode_barang'],
                    'nobatch' => $validated['nobatch'],
                    'tgl_exprd' => $validated['tgl_exprd'],
                    'isi' => $validated['isi'],
                    'satuan_b' => $validated['satuan_b'],
                    'satuan_k' => $validated['satuan_k'],
                    'jumlah_b' => $validated['jumlah_b'],
                    'jumlah_k' => $validated['jumlah_k'],
                    'harga' => $validated['harga'],
                    'pajak_rupiah' => $pajak_rupiah,
                    'diskon_persen' => $validated['diskon_persen'],
                    'diskon_rupiah' => $diskon_rupiah,
                    'harga_total' => $harga_total,
                    'subtotal' =>  $subtotal,
                    'kode_user' => ''
                ]
            );

            if (!$penerimaanrinci) {
                throw new \Exception('Gagal menyimpan Rincian .');
            }

            DB::commit();
            $penerimaanHeader->load([
                'rincian' => function ($query) {
                    $query->with(['barang']);
                },
                'suplier',
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'header' => $penerimaanHeader,
                ],
                'message' => 'Data berhasil disimpan'
            ], 201);
        } catch (\Exception $e) {
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

    public function hapus(Request $request)
    {
        $cek = Penerimaan_h::where('nopenerimaan', $request->nopenerimaan)->where('flag', '1')->count();
        if ($cek > 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Data ini sudah terkunci.'
            ], 410);
        }

        try {
            DB::beginTransaction();
            // Hapus order records
            Penerimaan_r::where('id', $request->id)->delete();

            DB::commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data order berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 410);
        }
    }
}
