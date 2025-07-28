<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Transactions\OrderHeader;
use App\Models\Transactions\OrderRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // get list of orders (header & records)
    public function index()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'kode_supplier' => request('kode_supplier'),
            'per_page' => request('per_page', 10),
        ];


        $query = OrderHeader::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('nomor_order', 'like', '%' . request('q') . '%')
                        ->orWhere('kode_user', 'like', '%' . request('q') . '%');
                });
            })
            ->when($req['kode_supplier'], function ($q) use ($req) {
                $q->where('kode_supplier', $req['kode_supplier']);
            })
            ->with([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'supplier',
            ])
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $query)->count();
        $data = $query->simplePaginate($req['per_page']);

        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function indexOld()
    {
        $req = [
            'order_by' => request('order_by', 'created_at'),
            'sort' => request('sort', 'asc'),
            'page' => request('page', 1),
            'per_page' => request('per_page', 10),
        ];

        $query = OrderHeader::query()
            ->when(request('q'), function ($q) {
                $q->where(function ($query) {
                    $query->where('nomor_order', 'like', '%' . request('q') . '%')
                        ->orWhere('kode_user', 'like', '%' . request('q') . '%')
                        ->orWhere('kode_supplier', 'like', '%' . request('q') . '%');
                });
            })
            ->orderBy($req['order_by'], $req['sort']);

        $headers = $query->simplePaginate($req['per_page']);
        $orderNumbers = $headers->pluck('nomor_order');

        $records = OrderRecord::whereIn('nomor_order', $orderNumbers)
            ->get()
            ->groupBy('nomor_order');

        $transformedData = $headers->getCollection()->map(function ($header) use ($records) {
            return [
                'header' => $header,
                'records' => $records->get($header->nomor_order, collect())
            ];
        });

        $headers->setCollection($transformedData);

        $resp = ResponseHelper::responseGetSimplePaginate(
            $headers,
            $req,
            $query->toBase()->getCountForPagination()
        );

        return new JsonResponse($resp);
    }

    // get list of order headers
    public function header_get_list()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = OrderHeader::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nomor_order', 'like', '%' . request('q') . '%')
                ->orWhere('kode_user', 'like', '%' . request('q') . '%')
                ->orWhere('kode_supplier', 'like', '%' . request('q') . '%');
        })
            ->orderBy($req['order_by'], $req['sort'])->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);
        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    // get list of order records
    public function record_get_list()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = OrderRecord::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nomor_order', 'like', '%' . request('q') . '%')
                ->orWhere('kode_barang', 'like', '%' . request('q') . '%');
        })
            ->orderBy($req['order_by'], $req['sort'])->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);
        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    // Store a new order (header & records)
    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'nullable',
            'tgl_order' => 'nullable',
            'kode_supplier' => 'required',
            'kode_barang' => 'required',
            'satuan_k' => 'nullable',
            'satuan_b' => 'nullable',
            'isi' => 'required',
        ], [
            'kode_supplier.required' => 'Kode Supplier Harus Di isi.',
            'items.required' => 'Minimal satu barang harus dipilih.',
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
        ]);
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Apakah Anda belum login?', 401);
        }
        if (!$validated['nomor_order']) {
            DB::select('call nomor_order(@nomor)');
            $nomor = DB::table('counter')->select('nomor_order')->first();
            $nomor_order = FormatingHelper::genKodeBarang($nomor->nomor_order, 'TRX');
        } else {
            $nomor_order = $request->nomor_order;
        }
        try {
            DB::beginTransaction();
            $orderHeader = OrderHeader::updateOrCreate(
                [
                    'nomor_order' => $nomor_order,
                ],
                [
                    'kode_user' => $user->kode,
                    'kode_supplier' => $validated['kode_supplier'],
                    'tgl_order' => $validated['tgl_order'] ?? now(),
                    'flag' => '1', // Default flag for draft
                ]
            );
            if (!$orderHeader) {
                throw new \Exception('Transaksi Gagal Disimpan.');
            }

            $record = OrderRecord::updateOrCreate(
                [
                    'nomor_order' => $nomor_order,
                    'kode_barang' => $validated['kode_barang'],
                ],
                [
                    'kode_user' => $user->kode,
                    'satuan_b' => $validated['satuan_b'] ?? null,
                    'satuan_k' => $validated['satuan_k'] ?? null,
                    'isi' => $validated['isi'] ?? 1,
                    'flag' => '1'
                ]
            );

            if (!$record) {
                throw new \Exception('Gagal menyimpan Rincian .');
            }

            DB::commit();
            $orderHeader->load([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan',
                'supplier',
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'header' => $orderHeader,
                ],
                'message' => 'Data Orders berhasil disimpan'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // return new JsonResponse([
            //     'success' => false,
            //     'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            //     'file' => $e->getFile(),
            //     'line' => $e->getLine(),
            //     'user' => Auth::user(),
            //     'trace' => $e->getTrace(),

            // ], 410);
            return response()->jsone([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'nullable',
            'tgl_order' => 'nullable',
            'kode_user' => 'required',
            'kode_supplier' => 'required',
            'items' => 'required|array|min:1', // Minimal satu item harus ada
            'items.*.kode_barang' => 'required',
            'items.*.satuan_k' => 'nullable',
            'items.*.satuan_b' => 'nullable',
            'items.*.isi' => 'nullable',
        ], [
            'kode_user.required' => 'Kode User Harus Di isi.',
            'kode_supplier.required' => 'Kode Supplier Harus Di isi.',
            'items.required' => 'Minimal satu barang harus dipilih.',
            'items.*.kode_barang.required' => 'Kode Barang Harus Di isi.'
        ]);

        // Generate nomor order jika tidak ada
        if (!$request->nomor_order) {
            DB::select('call nomor_order(@nomor)');
            $nomor = DB::table('counter')->select('nomor_order')->first();
            $nomor_order = FormatingHelper::genKodeBarang($nomor->nomor_order, 'TRX');
        } else {
            $nomor_order = $request->nomor_order;
        }

        DB::beginTransaction();
        try {
            // Buat order header
            $orderHeader = OrderHeader::updateOrCreate(
                [
                    'nomor_order' => $nomor_order,
                ],
                [
                    'kode_user' => $validated['kode_user'],
                    'kode_supplier' => $validated['kode_supplier'],
                    'tgl_order' => $validated['tgl_order'] ?? now(),
                    'flag' => '1', // Default flag for draft
                ]
            );

            // Buat order records untuk setiap item
            $orderRecords = [];
            foreach ($validated['items'] as $item) {
                $orderRecords[] = OrderRecord::updateOrCreate(
                    [
                        'nomor_order' => $nomor_order,
                        'kode_barang' => $item['kode_barang'],
                    ],
                    [
                        'kode_user' => $validated['kode_user'],
                        'satuan_b' => $item['satuan_b'] ?? null,
                        'satuan_k' => $item['satuan_k'] ?? null,
                        'isi' => $item['isi'] ?? null,
                        'flag' => '1'
                    ]
                );
            }

            DB::commit();
            $orderHeader->load([
                'orderRecords.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ]);

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'header' => $orderHeader,
                    // 'records' => $orderRecords
                ],
                'message' => 'Data Orders berhasil disimpan'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    // store a new order header
    public function header_store(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'nullable',
            'tgl_order' => 'nullable',
            'kode_user' => 'required',
            'kode_supplier' => 'required',
        ], [
            'kode_user.required' => 'Kode User Harus Di isi.',
            'kode_supplier.required' => 'Kode Supplier Harus Di isi.'
        ]);

        // Generate nomor order if not provided
        if (!$request->nomor_order) {
            DB::select('call nomor_order(@nomor)');
            $nomor = DB::table('counter')->select('nomor_order')->first();
            $validated['nomor_order'] = FormatingHelper::genKodeBarang($nomor->nomor_order, 'TRX');
        }

        $orderHeader = OrderHeader::updateOrCreate(
            [
                'nomor_order' => $validated['nomor_order'],
            ],
            $validated
        );

        return new JsonResponse([
            'data' => $orderHeader,
            'message' => 'Data header berhasil disimpan'
        ]);
    }

    // store a new order record
    public function record_store(Request $request)
    {
        $validated = $request->validate([
            'nomor_order' => 'required',
            'kode_barang' => 'required',
            'kode_user' => 'required',
            'satuan_k' => 'nullable',
            'satuan_b' => 'nullable',
            'isi' => 'nullable',
        ], [
            'kode_user.required' => 'Kode User Harus Di isi.',
            'nomor_order.required' => 'Nomor Order Harus Di isi.',
            'kode_barang.required' => 'Kode Barang Harus Di isi.'
        ]);

        $orderRecord = OrderRecord::updateOrCreate(
            [
                'kode_user' => $validated['kode_user'],
                'nomor_order' => $validated['nomor_order'],
                'kode_barang' => $validated['kode_barang'],
            ],
            $validated
        );

        return new JsonResponse([
            'data' => $orderRecord,
            'message' => 'Data record berhasil disimpan'
        ]);
    }

    // Delete an order (header & records) kurang cek status jika status > 1 tidak boleh hapus
    public function hapus(Request $request)
    {
        $nomor_order = $request->nomor_order;

        if (!$nomor_order) {
            return new JsonResponse([
                'message' => 'Nomor order tidak ditemukan'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Hapus order records
            OrderRecord::where('nomor_order', $nomor_order)->delete();

            // Hapus order header
            $header = OrderHeader::where('nomor_order', $nomor_order)->first();
            if ($header) {
                $header->delete();
            }

            DB::commit();

            return new JsonResponse([
                'message' => 'Data order berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 410);
        }
    }

    // Delete an order header
    public function header_hapus(Request $request)
    {
        $nomor_order = $request->nomor_order;

        if (!$nomor_order) {
            return new JsonResponse([
                'message' => 'Nomor order harus diisi'
            ], 410);
        }

        $header = OrderHeader::where('nomor_order', $nomor_order)->first();
        if (!$header) {
            return new JsonResponse([
                'message' => 'Data header tidak ditemukan'
            ], 410);
        }

        $header->delete();

        return new JsonResponse([
            'data' => $header,
            'message' => 'Data header berhasil dihapus'
        ]);
    }

    // Delete an order record
    public function record_hapus(Request $request)
    {
        $nomor_order = $request->nomor_order;
        $kode_barang = $request->kode_barang;

        if (!$nomor_order || !$kode_barang) {
            return new JsonResponse([
                'message' => 'Nomor order dan kode barang harus diisi'
            ], 410);
        }

        $record = OrderRecord::where('nomor_order', $nomor_order)
            ->where('kode_barang', $kode_barang)
            ->first();

        if (!$record) {
            return new JsonResponse([
                'message' => 'Data record tidak ditemukan'
            ], 410);
        }

        $record->delete();

        return new JsonResponse([
            'data' => $record,
            'message' => 'Data record berhasil dihapus'
        ]);
    }
}
