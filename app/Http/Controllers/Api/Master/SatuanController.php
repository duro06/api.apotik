<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Satuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
{
    public function index()
    {
        $order_by = request('order_by') ?? 'created_at';
        $sort = request('sort') ?? 'asc';
        $raw = Satuan::when(request('q'), function ($q) {
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
        ], [
            'nama.required' => 'Nama wajib diisi.'
        ]);

        if (!$request->kode) {
            DB::select('call kode_satuan(@nomor)');
            $nomor = DB::table('counter')->select('kode_satuan')->first();
            $kode = FormatingHelper::genKodeDinLength($nomor->kode_satuan, 5, 'STN');
        } else {
            $kode = $request->kode;
        }

        $data = Satuan::updateOrCreate(
            [
                'kode' => $kode
            ],
            $request->all()
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Satuan berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Satuan::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Satuan tidak ditemukan'
            ], 410);
        }
        $data->delete();
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Satuan berhasil dihapus'
        ]);
    }
}
