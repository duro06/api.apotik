<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Master\Barang;
use App\Models\Transactions\PenjualanH;
use App\Models\Transactions\PenjualanR;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    //
    public function getListObat(): JsonResponse
    {
        $req = [
            'order_by' => request('order_by') ?? 'nama',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        // ini masih kurang with stok
        $data = Barang::when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->orderBy($req['order_by'], $req['sort'])
            ->limit($req['per_page'])->get();

        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function index(): JsonResponse
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = PenjualanH::query();
        $raw->when(request('q'), function ($q) {
            $q->where('nama', 'like', '%' . request('q') . '%')
                ->orWhere('kode', 'like', '%' . request('q') . '%');
        })
            ->with([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ])
            ->orderBy($req['order_by'], $req['sort'])->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function simpan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nopenjualan' => 'nullable',
            'tgl_penjualan' => 'nullable',
            'kode_pelanggan' => 'nullable',
            'kode_dokter' => 'nullable',

            'kode_barang' => 'required',
            'jumlah_k' => 'required',
            'satuan_k' => 'nullable',
            'satuan_b' => 'nullable',
            'isi' => 'required',
            'harga_jual' => 'required', // ini dari master
            'harga_beli' => 'required', // ini dari master
            'id_penerimaan_rinci' => 'required', // ini dari stok
            'nopenerimaan' => 'required', // ini dari stok
            'nobatch' => 'required', // ini dari stok
            'tgl_exprd' => 'required', // ini dari stok
            'id_stok' => 'required', // ini dari stok
        ], [
            'kode_barang.required' => 'Kode Barang Harus Di isi.',
            'jumlah_k.required' => 'Jumalah Barang Harus Di isi.',
            'isi.required' => 'Isi per Satuan Besar Barang Harus Di isi.',
            'harga_jual.required' => 'Harga Jual Harus Di isi.',
            'harga_beli.required' => 'Harga Beli Harus Di isi.',
            'id_penerimaan_rinci.required' => 'id Rincian Penerimaan belum di ikutkan, silahkan kontak penyedia IT',
            'nopenerimaan.required' => 'Nomor Penerimaan belum di ikutkan, silahkan kontak penyedia IT',
            'nobatch.required' => 'Nomor Batch belum di ikutkan, silahkan kontak penyedia IT',
            'tgl_exprd.required' => 'Tanggal Expired Obat di ikutkan, silahkan kontak penyedia IT',
            'id_stok.required' => 'id Stok belum di ikutkan, silahkan kontak penyedia IT',
        ]);
        try {
            DB::beginTransaction();
            $user = Auth::user();
            if (!$validated['nopenjualan']) {
                DB::select('call nopenjualan(@nomor)');
                $nomor = DB::table('counter')->select('nopenjualan')->first();
                $nopenjualan = FormatingHelper::genKodeBarang($nomor->nopenjualan, 'TRX');
            } else {
                $nopenjualan = $request->nopenjualan;
            }
            $jumlahB = floor($validated['jumlah_k'] / $validated['isi']);
            $subtotal = $validated['jumlah_k'] * $validated['harga_jual'];
            $data = PenjualanH::updateOrCreate([
                'nopenjualan' => $nopenjualan
            ], [
                'tgl_penjualan' => $validated['tgl_penjualan'] ?? Carbon::now(),
                'kode_pelanggan' => $validated['kode_pelanggan'],
                'kode_dokter' => $validated['kode_dokter'],
                'kode_user' => $user->kode,
                'cara_bayar' => '',
            ]);
            if (!$data) {
                throw new \Exception('Data Penjualan Gagal Disimpan.');
            }
            $rinci = PenjualanR::updateOrCreate([
                'nopenjualan' => $nopenjualan,
                'kode_barang' => $validated['kode_barang'],
                'id_penerimaan_rinci' => $validated['id_penerimaan_rinci'],
                'id_stok' => $validated['id_stok'],
                'jumlah_k' => $validated['jumlah_k'],
            ], [
                'jumlah_b' => $jumlahB,
                'nopenerimaan' => $validated['nopenerimaan'],
                'nobatch' => $validated['nobatch'],
                'isi' => $validated['isi'],
                'satuan_k' => $validated['satuan_k'],
                'satuan_b' => $validated['satuan_b'],
                'tgl_exprd' => $validated['tgl_exprd'],
                'harga_jual' => $validated['harga_jual'],
                'harga_beli' => $validated['harga_beli'],
                'subtotal' => $subtotal,
                'kode_user' => $user->kode,
            ]);
            if (!$rinci) {
                throw new \Exception('Data Penjualan Gagal Disimpan.');
            }
            DB::commit();
            $data->load([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ]);
            return new JsonResponse([
                'message' => 'Data berhasil disimpan',
                'data' => $data,
                'rinci' => $rinci,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function bayar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required',
            'diskon' => 'nullable',
            'jumlah_bayar' => 'required',
            'kembali' => 'nullable',
            'cara_bayar' => 'required',
        ], [
            'cara_bayar.required' => 'Cara Bayar Harus Di isi.',
            'id.required' => 'Id Header Penjualan Harus Di isi.',
            'jumlah_bayar.required' => 'Jumlah Bayar Harus Di isi.',
        ]);
        $user = Auth::user();
        try {
            DB::beginTransaction();
            $data = PenjualanH::find($validated['id']);
            if (!$data) throw new \Exception('Data Penjualan Tidak Ditemukan.');

            if ($data->flag == 1) throw new \Exception('Transaksi penjualan ini sudah dibayar.');

            // hitung Subtotal
            $subtotal = PenjualanR::where('nopenjualan', $data->nopenjualan)->sum('subtotal');

            $diskon = $validated['diskon'] ?? 0;
            $kembali = $validated['kembali'] ?? 0;
            $jumlahBayar = $validated['jumlah_bayar'] ?? 0;
            // tentkan jumlah pembayran jika ada diskon dan tidak
            if ($diskon > 0) $nilaiBayar = (int)$subtotal - (int)$diskon;
            else $nilaiBayar = (int)$subtotal;
            // validasi jumlah pembayaran
            if ((int)$jumlahBayar < (int)$nilaiBayar) {
                throw new Exception('Jumlah Pembayaran kurang, minimal ' . $nilaiBayar);
            }
            // validasi kembalian
            if ($kembali == 0) $kembali = (int)$jumlahBayar - (float)$nilaiBayar;
            else $kembali = $validated['kembali'];
            // update data
            $data->update([
                'cara_bayar' => $validated['cara_bayar'],
                'diskon' => $diskon,
                'jumlah_bayar' => $jumlahBayar,
                'kembali' => $kembali,
                'kode_user' => $user->kode,
                'flag' => '1'
            ]);
            DB::commit();
            $data->load([
                'rinci.master:nama,kode,satuan_k,satuan_b,isi,kandungan'
            ]);
            return new JsonResponse([
                'message' => 'Pembayaran berhasil dilakukan',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }

    public function hapus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required',
        ], [
            'id.required' => 'Tidak Ada Rincian untuk dihapus',
        ]);

        try {
            DB::beginTransaction();
            $msg = 'Data Obat sudah dihapus';
            $rinci = PenjualanR::find($validated['id']);
            if (!$rinci) throw new \Exception('Data Obat Tidak Ditemukan.');

            $header = PenjualanH::where('nopenjualan', $rinci->nopenjualan)->first();
            if ($header->flag !== null) throw new Exception('Data sudah terkunci, tidak boleh dihapus');

            // hitung sisa rincian
            $rinci->delete();
            $sisaRinci = PenjualanR::where('nopenjualan', $rinci->nopenjualan)->get()->count();
            if ($sisaRinci == 0) {
                $header->delete();
                $msg = 'Data Obat sudah dihapus, Sisa rincian sebanyak 0 data';
            }
            DB::commit();
            return new JsonResponse([
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => Auth::user(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
}
