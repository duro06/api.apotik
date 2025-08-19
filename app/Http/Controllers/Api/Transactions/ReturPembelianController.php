<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use App\Models\Transactions\ReturPembelian_h;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturPembelianController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = ReturPembelian_h::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('noretur', 'like', '%' . request('q') . '%')
                        ->orWhere('nopenerimaan', 'like', '%' . request('q') . '%')
                        ->orWhere('nofaktur', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'rincian' => function ($query) {
                    $query->with(['barang']);
                },
                'suplier',
            ])
            ->select('retur_pembelian_hs.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function getpenerimaan()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = Penerimaan_h::query()
            ->where('nopenerimaan', request('nopenerimaan'))
            ->with([
                'rincian' => function ($query) {
                    $query->with(['barang']);
                },
                'suplier',
            ])
            ->orderBy('penerimaan_hs.' . $req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
}
