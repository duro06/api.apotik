<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{

    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'query' => request('q') ?? '',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];

        $raw = Pelanggan::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->orderBy($req['order_by'], $req['sort'])->orderBy($req['order_by'], $req['sort']);
        $data = $raw->simplePaginate(request('per_page'));

        $totalCount = (clone $raw)->count();

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
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
            DB::select('call kode_pelanggan(@nomor)');
            $nomor = DB::table('counter')->select('kode_pelanggan')->first();
            $kode = FormatingHelper::genKodeDinLength($nomor->kode_pelanggan, 5, 'PLG');
        } else {
            $kode = $request->kode;
        }

        $data = Pelanggan::updateOrCreate(
            [
                'kode' => $kode
            ],
            $request->all()
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Pelanggan berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $data = Pelanggan::find($request->id);
        if (!$data) {
            return new JsonResponse([
                'message' => 'Data Pelanggan tidak ditemukan'
            ], 410);
        }
        $data->delete();
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Pelanggan berhasil dihapus'
        ]);
    }
}
