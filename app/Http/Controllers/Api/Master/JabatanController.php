<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\Formating\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Jabatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{
    public function index()
    {
        $raw = Jabatan::when(request('q'), function ($q) {
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

        if (!$request->kode) {
            DB::select('call kode_jabatan(@nomor)');
            $nomor = DB::table('counter')->select('kode_jabatan')->first();
            $kode = FormatingHelper::genKodeDinLength($nomor->kode_jabatan, 4, 'JBT');
        } else {
            $kode = $request->kode;
        }

        $data = Jabatan::updateOrCreate(
            [
                'kode' => $kode
            ],
            $request->all()
        );
        return new JsonResponse([
            'data' => $data,
            'message' => 'Data Jabatan berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $jabatan = Jabatan::find($request->id);
        if (!$jabatan) {
            return new JsonResponse([
                'message' => 'Data Jabatan tidak ditemukan'
            ], 410);
        }
        $jabatan->delete();
        return new JsonResponse([
            'data' => $jabatan,
            'message' => 'Data Jabatan berhasil dihapus'
        ]);
    }
}
