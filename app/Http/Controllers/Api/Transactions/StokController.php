<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Stok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = Stok::query()
            ->leftjoin('barangs', 'stoks.kode_barang', '=', 'barangs.kode')
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('stoks.nopenerimaan', 'like', '%' . request('q') . '%')
                        ->orWhere('stoks.noorder', 'like', '%' . request('q') . '%')
                        ->orWhere('barangs.nama', 'like', '%' . request('q') . '%');
                });
            })
            ->with([
                'barang'
            ])
            ->where('jumlah_k', '>', 0)
            ->select('stoks.*')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
}
