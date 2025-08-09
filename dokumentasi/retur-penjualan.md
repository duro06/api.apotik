# 📄 Dokumentasi API ReturPenjualanController

Base URL: `/api/transactions/returpenjualan`

## 🔷 Daftar Endpoint

1. **GET** `/` - Mendapatkan daftar retur penjualan
2. **POST** `/simpan` - Menyimpan/memperbarui data retur penjualan
3. **POST** `/lock-retur-penjualan` - Mengunci data retur penjualan
4. **POST** `/open-lock-retur-penjualan` - Membuka kunci data retur penjualan
5. **DELETE** `/hapus` - Menghapus data retur penjualan
6. **DELETE** `/hapus-rincian-tidak-dikunci` - Menghapus rincian retur penjualan yang belum dikunci

## 🔷 Status Flag Retur Penjualan

| Nilai Flag | Keterangan     |
| ---------- | -------------- |
| null       | Belum Terkunci |
| 1          | Sudah Terkunci |

## 1. Mendapatkan Daftar Retur Penjualan

**GET** `/api/transactions/returpenjualan`

### Query Parameters

| Parameter | Type   | Required | Default      | Description                                                        |
| --------- | ------ | -------- | ------------ | ------------------------------------------------------------------ |
| q         | string | ❌        | -            | Keyword pencarian (noretur, nopenerimaan, nofaktur, nama supplier) |
| order_by  | string | ❌        | `created_at` | Kolom untuk sorting                                                |
| sort      | string | ❌        | `asc`        | Arah sorting (`asc` atau `desc`)                                   |
| page      | int    | ❌        | 1            | Nomor halaman                                                      |
| per_page  | int    | ❌        | 10           | Jumlah item per halaman                                            |

### Response Sukses (200)

```json
{
  "data": [
    {
      "id": 1,
      "noretur": "RPJ000001",
      "nopenerimaan": "PN0001",
      "nofaktur": "NF0001",
      "tgl_retur": "2025-08-09 13:07:10",
      "kode_supplier": "SUP002",
      "kode_user": "USR000001",
      "flag": null,
      "created_at": "2025-08-09T12:41:01.000000Z",
      "updated_at": "2025-08-09T13:07:10.000000Z",
      "retur_penjualan_r": [
        {
          "id": 1,
          "noretur": "RPJ000001",
          "kode_barang": "BRG000012",
          "nobatch": "NB0004",
          "jumlah_k": "15.00",
          "satuan_k": "pill",
          "harga": "30000.00",
          "kode_user": "USR000001",
          "returpenjualan_h_id": 1,
          "returpenjualan_h_noretur": "RPJ000001",
          "created_at": "2025-08-09T12:41:02.000000Z",
          "updated_at": "2025-08-09T13:06:01.000000Z",
          "master_barang": {
            "nama": "Zinc Tablet",
            "kode": "BRG000012",
            "satuan_k": "strip",
            "satuan_b": null,
            "isi": 10,
            "kandungan": "20mg"
          }
        }
      ],
      "penerimaan_h": {
        "id": 1,
        "nopenerimaan": "PN0001",
        "noorder": "TRX000012",
        "tgl_penerimaan": "2025-07-30",
        "nofaktur": "NF0001",
        "tgl_faktur": "2025-07-30",
        "kode_suplier": "SUP002",
        "jenispajak": "negara",
        "pajak": "100000",
        "kode_user": "USR000001",
        "flag": "1",
        "created_at": null,
        "updated_at": null,
        "rincian": []
      },
      "supplier": null
    }
  ],
  "meta": {
    "first": "http://localhost:8185/api/transactions/returpenjualan?page=1",
    "last": "http://localhost:8185/api/transactions/returpenjualan?page=1",
    "prev": null,
    "next": null,
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1,
    "from": 1,
    "to": 1
  }
}
```

## 2. Menyimpan/Memperbarui Retur Penjualan

**POST** `/api/transactions/returpenjualan/simpan`

### Request Body

| Parameter     | Type   | Required | Description                       |
| ------------- | ------ | -------- | --------------------------------- |
| noretur       | string | ❌        | Nomor retur (kosong jika baru)    |
| nopenerimaan  | string | ✔️        | Nomor penerimaan                  |
| nofaktur      | string | ✔️        | Nomor faktur                      |
| tgl_retur     | date   | ❌        | Tanggal retur (default: sekarang) |
| kode_supplier | string | ✔️        | Kode supplier                     |
| kode_barang   | string | ✔️        | Kode barang                       |
| nobatch       | string | ✔️        | Nomor batch                       |
| jumlah_k      | number | ✔️        | Jumlah dalam satuan kecil         |
| satuan_k      | string | ✔️        | Satuan kecil                      |
| harga         | number | ✔️        | Harga barang                      |

### Response Sukses (201)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "noretur": "RPJ000001",
    "nopenerimaan": "PN0001",
    "nofaktur": "NF0001",
    "tgl_retur": "2025-08-09 13:07:10",
    "kode_supplier": "SUP002",
    "kode_user": "USR000001",
    "flag": null,
    "created_at": "2025-08-09T12:41:01.000000Z",
    "updated_at": "2025-08-09T13:07:10.000000Z",
    "retur_penjualan_r": [
      {
        "id": 1,
        "noretur": "RPJ000001",
        "kode_barang": "BRG000012",
        "nobatch": "NB0004",
        "jumlah_k": "15.00",
        "satuan_k": "pill",
        "harga": "30000.00",
        "kode_user": "USR000001",
        "returpenjualan_h_id": 1,
        "returpenjualan_h_noretur": "RPJ000001",
        "created_at": "2025-08-09T12:41:02.000000Z",
        "updated_at": "2025-08-09T13:06:01.000000Z",
        "master_barang": {
          "nama": "Zinc Tablet",
          "kode": "BRG000012",
          "satuan_k": "strip",
          "satuan_b": null,
          "isi": 10,
          "kandungan": "20mg"
        }
      }
    ],
    "penerimaan_h": {
      "id": 1,
      "nopenerimaan": "PN0001",
      "noorder": "TRX000012",
      "tgl_penerimaan": "2025-07-30",
      "nofaktur": "NF0001",
      "tgl_faktur": "2025-07-30",
      "kode_suplier": "SUP002",
      "jenispajak": "negara",
      "pajak": "100000",
      "kode_user": "USR000001",
      "flag": "1",
      "created_at": null,
      "updated_at": null,
      "rincian": []
    },
    "supplier": null
  },
  "message": "Data Retur Penjualan berhasil disimpan"
}
```

### Error Responses

- **401 Unauthorized**: User tidak login
- **410 Gone**: 
  - Data retur sudah terkunci
  - Data penerimaan tidak ditemukan
  - Gagal menyimpan data

## 3. Mengunci Retur Penjualan

**POST** `/api/transactions/returpenjualan/lock-retur-penjualan`

### Request Body

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| noretur   | string | ✔️        | Nomor retur |

### Response Sukses (201)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "noretur": "RPJ000001",
    "nopenerimaan": "PN0001",
    "nofaktur": "NF0001",
    "tgl_retur": "2025-08-09 13:07:10",
    "kode_supplier": "SUP002",
    "kode_user": "USR000001",
    "flag": "1",
    "created_at": "2025-08-09T12:41:01.000000Z",
    "updated_at": "2025-08-09T13:07:10.000000Z",
    "retur_penjualan_r": [
      {
        "id": 1,
        "noretur": "RPJ000001",
        "kode_barang": "BRG000012",
        "nobatch": "NB0004",
        "jumlah_k": "15.00",
        "satuan_k": "pill",
        "harga": "30000.00",
        "kode_user": "USR000001",
        "returpenjualan_h_id": 1,
        "returpenjualan_h_noretur": "RPJ000001",
        "created_at": "2025-08-09T12:41:02.000000Z",
        "updated_at": "2025-08-09T13:06:01.000000Z",
        "master_barang": {
          "nama": "Zinc Tablet",
          "kode": "BRG000012",
          "satuan_k": "strip",
          "satuan_b": null,
          "isi": 10,
          "kandungan": "20mg"
        }
      }
    ],
    "penerimaan_h": {
      "id": 1,
      "nopenerimaan": "PN0001",
      "noorder": "TRX000012",
      "tgl_penerimaan": "2025-07-30",
      "nofaktur": "NF0001",
      "tgl_faktur": "2025-07-30",
      "kode_suplier": "SUP002",
      "jenispajak": "negara",
      "pajak": "100000",
      "kode_user": "USR000001",
      "flag": "1",
      "created_at": null,
      "updated_at": null,
      "rincian": []
    },
    "supplier": null
  },
  "message": "Data Retur Penjualan berhasil dikunci"
}
```

### Error Responses

- **401 Unauthorized**: User tidak login
- **410 Gone**: 
  - Data retur tidak ditemukan
  - Data sudah terkunci
  - Data bukan data draft (flag bukan null)

## 4. Membuka Kunci Retur Penjualan

**POST** `/api/transactions/returpenjualan/open-lock-retur-penjualan`

### Request Body

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| noretur   | string | ✔️        | Nomor retur |

### Response Sukses (201)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "noretur": "RPJ000001",
    "nopenerimaan": "PN0001",
    "nofaktur": "NF0001",
    "tgl_retur": "2025-08-09 13:07:10",
    "kode_supplier": "SUP002",
    "kode_user": "USR000001",
    "flag": null,
    "created_at": "2025-08-09T12:41:01.000000Z",
    "updated_at": "2025-08-09T13:07:10.000000Z",
    "retur_penjualan_r": [
      {
        "id": 1,
        "noretur": "RPJ000001",
        "kode_barang": "BRG000012",
        "nobatch": "NB0004",
        "jumlah_k": "15.00",
        "satuan_k": "pill",
        "harga": "30000.00",
        "kode_user": "USR000001",
        "returpenjualan_h_id": 1,
        "returpenjualan_h_noretur": "RPJ000001",
        "created_at": "2025-08-09T12:41:02.000000Z",
        "updated_at": "2025-08-09T13:06:01.000000Z",
        "master_barang": {
          "nama": "Zinc Tablet",
          "kode": "BRG000012",
          "satuan_k": "strip",
          "satuan_b": null,
          "isi": 10,
          "kandungan": "20mg"
        }
      }
    ],
    "penerimaan_h": {
      "id": 1,
      "nopenerimaan": "PN0001",
      "noorder": "TRX000012",
      "tgl_penerimaan": "2025-07-30",
      "nofaktur": "NF0001",
      "tgl_faktur": "2025-07-30",
      "kode_suplier": "SUP002",
      "jenispajak": "negara",
      "pajak": "100000",
      "kode_user": "USR000001",
      "flag": "1",
      "created_at": null,
      "updated_at": null,
      "rincian": []
    },
    "supplier": null
  },
  "message": "Kunci Data Retur Penjualan berhasil dibuka"
}
```

### Error Responses

- **401 Unauthorized**: User tidak login
- **410 Gone**: 
  - Data retur tidak ditemukan
  - Data belum terkunci
  - Data bukan data retur terkunci (flag bukan 1)

## 5. Menghapus Retur Penjualan

**DELETE** `/api/transactions/returpenjualan/hapus`

### Request Body

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| noretur   | string | ✔️        | Nomor retur |

### Response Sukses (200)

```json
{
  "success": true,
  "message": "Data retur penjualan berhasil dihapus"
}
```

### Error Responses

- **400 Bad Request**: Nomor retur tidak ditemukan
- **401 Unauthorized**: User tidak login
- **410 Gone**: 
  - Data retur tidak ditemukan
  - Data sudah terkunci

## 6. Menghapus Rincian Retur Penjualan (Belum Dikunci)

**DELETE** `/api/transactions/returpenjualan/hapus-rincian-tidak-dikunci`

### Request Body

| Parameter   | Type   | Required | Description |
| ----------- | ------ | -------- | ----------- |
| noretur     | string | ✔️        | Nomor retur |
| kode_barang | string | ✔️        | Kode barang |

### Response Sukses (200)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "noretur": "RPJ000001",
    "kode_barang": "BRG000012",
    "nobatch": "NB0004",
    "jumlah_k": "15.00",
    "satuan_k": "pill",
    "harga": "30000.00",
    "kode_user": "USR000001",
    "returpenjualan_h_id": 1,
    "returpenjualan_h_noretur": "RPJ000001",
    "created_at": "2025-08-09T12:41:02.000000Z",
    "updated_at": "2025-08-09T13:06:01.000000Z"
  },
  "message": "Data rincian retur berhasil dihapus"
}
```

### Error Responses

- **400 Bad Request**: Parameter tidak lengkap
- **401 Unauthorized**: User tidak login
- **410 Gone**: 
  - Data retur tidak ditemukan
  - Data sudah terkunci
  - Rincian tidak ditemukan