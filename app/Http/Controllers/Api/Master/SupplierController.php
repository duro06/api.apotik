<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $order_by = request('order_by') ?? 'created_at';
        $sort = request('sort') ?? 'asc';
        $raw = Supplier::when(request('q'), function ($q) {
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
            DB::select('call kode_supplier(@nomor)');
            $nomor = DB::table('counter')->select('kode_supplier')->first();
            $kode = FormatingHelper::genKodeDinLength($nomor->kode_supplier, 5, 'PBF');
        } else {
            $kode = $request->kode;
        }

        $data = Supplier::updateOrCreate(
            [
                'kode' => $kode
            ],
            $request->all()
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Supplier berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Supplier::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Supplier tidak ditemukan'
            ], 410);
        }
        $data->delete();
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Supplier berhasil dihapus'
        ]);
    }
}
