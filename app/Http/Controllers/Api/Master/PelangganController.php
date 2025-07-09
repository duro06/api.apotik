<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{

    public function index()
    {
        $raw = Pelanggan::when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->paginate(request('per_page'));
        $data = collect($raw)['data'];
        $meta = collect($raw)->except('data');
        return new JsonResponse([
            'data' => $data,
            'meta' => $meta
        ]);
    }

    public function store(Request $request)
    {
        // return new JsonResponse($request->all());
        $request->validate([
            'nama' => 'required'
        ]);

        if (!!$request->kode) {
            $kode = $request->kode;
        } else {
            DB::select('call kode_pelanggan(@nomor)');
            $nomor = DB::table('counter')->select('kode_pelanggan')->first();
            $kode = FormatingHelper::genKodeDinLength($nomor->kode_pelanggan, 5, 'PLG');
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
        $jabatan = Pelanggan::find($request->id);
        if (!$jabatan) {
            return new JsonResponse([
                'message' => 'Data Pelanggan tidak ditemukan'
            ], 410);
        }
        $jabatan->delete();
        return new JsonResponse([
            'data' => $jabatan,
            'message' => 'Data Pelanggan berhasil dihapus'
        ]);
    }
}
