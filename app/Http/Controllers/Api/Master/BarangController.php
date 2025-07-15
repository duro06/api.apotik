<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    //
    public function index()
    {
        $order_by = request('order_by') ?? 'created_at';
        $sort = request('sort') ?? 'asc';
        $raw = Barang::when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->orderBy($order_by, $sort)
            // ->paginate(request('per_page'));
            ->simplePaginate(request('per_page'));
        $resp = ResponseHelper::responseGetSimplePaginate($raw);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        // return new JsonResponse($request->all());
        $request->validate([
            'nama' => 'required'
        ]);

        if (!$request->kode) {
            DB::select('call kode_barang(@nomor)');
            $nomor = DB::table('counter')->select('kode_barang')->first();
            $kode = FormatingHelper::genKodeBarang($nomor->kode_barang, 'BRG');
        } else {
            $kode = $request->kode;
        }

        $barang = Barang::updateOrCreate(
            [
                'kode' => $kode
            ],
            // [
            //     'nama' => $request->nama,
            //     'satuan_k,' => $request->satuan_k,
            //     'satuan_b,' => $request->satuan_b,
            //     'isi,' => $request->isi,
            //     'kandungan,' => $request->kandungan,
            //     'harga_jual_resep,' => $request->harga_jual_resep,
            //     'harga_jual_umum,' => $request->harga_jual_umum,
            // ]
            $request->all()
        );
        return new JsonResponse([
            'data' => $barang,
            'message' => 'Data barang berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $barang = Barang::find($request->id);
        if (!$barang) {
            return new JsonResponse([
                'message' => 'Data barang tidak ditemukan'
            ], 410);
        }
        $barang->delete();
        return new JsonResponse([
            'data' => $barang,
            'message' => 'Data barang berhasil dihapus'
        ]);
    }
}
