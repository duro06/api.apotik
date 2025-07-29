<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\Penerimaan_h;
use Illuminate\Http\JsonResponse;

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
}
