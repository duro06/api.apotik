<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranHutangController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => Carbon::parse(request('from'))->startOfDay() ?? Carbon::now()->startOfMonth()->startOfDay(),
            'to' => Carbon::parse(request('to'))->endOfDay() ?? Carbon::now()->endOfMonth()->endOfDay()
        ];
    }
    public function getHutang()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
            'from' => request('from') ?? Carbon::now()->startOfMonth()->format('Y-m-d'),
            'to' => request('to') ?? Carbon::now()->endOfMonth()->format('Y-m-d')
        ];
        $raw = Penerimaan_h::query()->select(
            'penerimaan_hs.nopenerimaan',
            'penerimaan_hs.noorder',
            'penerimaan_hs.nofaktur',
            'penerimaan_hs.kode_suplier',
            DB::raw('sum(r.jumlah_k*pajak_rupiah) as pajak'),
            DB::raw('sum(r.jumlah_k*diskon_rupiah) as diskon'),
            DB::raw('sum(r.jumlah_k*harga) as nominal'),
            DB::raw('sum(r.jumlah_k*harga_total) as nominal_total'),
            DB::raw('sum(r.subtotal) as subtotal'),
        )
            ->leftJoin('penerimaan_rs as r', 'r.nopenerimaan', '=', 'penerimaan_hs.nopenerimaan')
            ->whereBetween('penerimaan_hs.tgl_penerimaan', [$req['from'], $req['to']])
            ->where('penerimaan_hs.hutang', 'HUTANG')
            ->whereNull('flag_hutang')
            ->with('rincian.barang:nama,kode,satuan_k,satuan_b,isi')
            ->groupBy('penerimaan_hs.nopenerimaan', 'penerimaan_hs.noorder', 'penerimaan_hs.nofaktur', 'penerimaan_hs.kode_suplier');
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        // return new JsonResponse([
        //     'resp' => $resp,
        //     'req' => $req,
        // ]);
        return new JsonResponse($resp);
    }
    public function simpan(Request $request) {}
    public function kunci(Request $request) {}
    public function hapus(Request $request) {}
}
