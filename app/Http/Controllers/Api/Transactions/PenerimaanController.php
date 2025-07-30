<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
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
            'kode_supplier' => request('kode_supplier'),
            'per_page' => request('per_page', 10),
        ];

        $query = Penerimaan_h::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('nopenerimaan', 'like', '%' . request('q') . '%')
                        ->orWhere('noorder', 'like', '%' . request('q') . '%')
                        ->orWhere('kode_user', 'like', '%' . request('q') . '%');
                });
            })
            ->when($req['kode_supplier'], function ($q) use ($req) {
                $q->where('kode_supplier', $req['kode_supplier']);
            })
            ->with([
                'supplier',
            ])
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
            'jumlah_b' => 'required',
            'jumlah_k' => 'required',
            'harga' => 'required',
            'diskon_persen' => 'required',
            'isi' => 'required',
            'satuan_k' => 'required',
            'satuan_b' => 'required',
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
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
            'jumlah_b.required' => 'Jumlah Satuan Besar Harus Di isi.',
            'jumlah_k.required' => 'Jumlah Satuan Kecil Harus Di isi.',
            'harga.required' => 'Harga Harus Di isi.',
            'diskon_persen.required' => 'Diskon Harus Di isi.',
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

            $nopenerimaan = FormatingHelper::genKodeBarang($nomor->nopenerimaan, 'PN');
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

            // $record = OrderRecord::updateOrCreate(
            //     [
            //         'nomor_order' => $nomor_order,
            //         'kode_barang' => $validated['kode_barang'],
            //     ],
            //     [
            //         'kode_user' => $user->kode,
            //         'satuan_b' => $validated['satuan_b'] ?? null,
            //         'satuan_k' => $validated['satuan_k'] ?? null,
            //         'isi' => $validated['isi'] ?? 1,
            //     ]
            // );

            // if (!$record) {
            //     throw new \Exception('Gagal menyimpan Rincian .');
            // }

            DB::commit();
            // $orderHeader->load([
            //     'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
            //     'supplier',
            // ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'header' => $penerimaanHeader,
                ],
                'message' => 'Data Orders berhasil disimpan'
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
}
