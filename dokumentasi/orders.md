# 📄 Dokumentasi API OrderController

Base URL: `/api/v1/transactions/order`

## 🔷 Get Order List (Header + Rinci + Supplier)

**GET** `/api/v1/transactions/order/get-list`

> Mengambil daftar order lengkap dengan header dan records, termasuk pencarian, sorting, dan pagination.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                                                               |
| --------- | ------ | -------- | ------------ | ----------------------------------------------------------------------------------- |
| q         | string | ❌        | -            | Kata kunci pencarian (`nomor_order`, `kode_user`, `kode_supplier`, `nama_supplier`) |
| order_by  | string | ❌        | `created_at` | Kolom sorting (`nomor_order`, `tgl_order`, `created_at`, dll)                       |
| sort      | string | ❌        | `asc`        | Arah sorting (`asc` atau `desc`)                                                    |
| per_page  | int    | ❌        | 10           | Jumlah item per halaman                                                             |
| page      | int    | ❌        | 1            | Halaman yang diambil                                                                |
| from      | date   | ❌        | -            | Date format Y-m-d contoh (2025-07-31)                                               |
| to        | date   | ❌        | -            | Date format Y-m-d contoh (2025-07-31)                                               |

### Contoh Request

```http
GET /api/v1/transactions/order/get-list?from=2025-07-31&to=2025-07-31&q=SUP002
```

### Response (200)

```json
{
  "data": [
    {
      "id": 4,
      "nomor_order": "TRX000004",
      "tgl_order": "2025-07-31",
      "flag": "1",
      "kode_user": "USR000001",
      "kode_supplier": "SUP002",
      "created_at": "2025-07-31T13:03:10.000000Z",
      "updated_at": "2025-07-31T13:20:27.000000Z",
      "order_records": [
        {
          "id": 9,
          "nomor_order": "TRX000004",
          "kode_barang": "BRG000034",
          "jumlah_pesan": "20",
          "kode_user": "USR000001",
          "satuan_k": "pil",
          "satuan_b": "strip",
          "isi": "20",
          "flag": "1",
          "created_at": "2025-07-31T13:03:10.000000Z",
          "updated_at": "2025-07-31T13:20:27.000000Z",
          "master": {
            "nama": "OBH Combi",
            "kode": "BRG000034",
            "satuan_k": "botol",
            "satuan_b": null,
            "isi": 100,
            "kandungan": null
          }
        }
      ],
      "supplier": {
        "id": 2,
        "nama": "Apotek Sentosa Supplier",
        "kode": "SUP002",
        "tlp": "021-7654321",
        "bank": "Mandiri",
        "rekening": "9876543210",
        "alamat": "Jl. Melati No. 10, Bekasi",
        "created_at": "2025-07-30T06:16:15.000000Z",
        "updated_at": "2025-07-30T06:16:15.000000Z"
      }
    }
  ],
  "meta": {
    "first": "http://localhost:8185/api/v1/transactions/order/get-list?page=1",
    "last": "http://localhost:8185/api/v1/transactions/order/get-list?page=1",
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


## 🔷 POST Simpan Order (Create or Update)

**POST** `/api/v1/transactions/order/simpan`

> Membuat atau memperbarui data order (header dan record)

### Request Body (FormData)

| Parameter     | Type   | Required | Description                         |
| ------------- | ------ | -------- | ----------------------------------- |
| nomor_order   | string | ❌        | Jika Kosong maka membuat order baru |
| tgl_order     | date   | ❌        | Tanggal order (format: YYYY-MM-DD)  |
| kode_supplier | string | ✔️        | Kode supplier                       |
| kode_barang   | string | ✔️        | Kode barang                         |
| satuan_k      | string | ❌        | Satuan kecil                        |
| satuan_b      | string | ❌        | Satuan besar (opsional)             |
| isi           | number | ❌        | Jumlah isi satuan besar             |
| jumlah_pesan  | number | ❌        | Jumlah pesan                        |

### Contoh Request
```json
{
  "nomor_order": "",
  "tgl_order": "2025-07-31",
  "kode_supplier": "SUP002",
  "kode_barang": "BRG000034",
  "satuan_k": "pil",
  "satuan_b": "strip",
  "isi": 20,
  "jumlah_pesan": 20
}
```

### Response (200)
```json
{
  "success": true,
  "data": {
    "id": 5,
    "nomor_order": "TRX000005",
    "tgl_order": "2025-07-31",
    "flag": null,
    "kode_user": "USR000001",
    "kode_supplier": "BRG000041",
    "created_at": "2025-07-31T13:56:35.000000Z",
    "updated_at": "2025-07-31T13:56:35.000000Z",
    "order_records": [
      {
        "id": 10,
        "nomor_order": "TRX000005",
        "kode_barang": "BRG000034",
        "jumlah_pesan": "20",
        "kode_user": "USR000001",
        "satuan_k": "pil",
        "satuan_b": "strip",
        "isi": "20",
        "flag": null,
        "created_at": "2025-07-31T13:56:35.000000Z",
        "updated_at": "2025-07-31T13:56:35.000000Z",
        "master": {
          "nama": "OBH Combi",
          "kode": "BRG000034",
          "satuan_k": "botol",
          "satuan_b": null,
          "isi": 100,
          "kandungan": null
        }
      }
    ],
    "supplier": null
  },
  "message": "Data Orders berhasil disimpan"
}
```

### Jika Order Sudah Terkunci Response (410)
```json
{
  "success": false,
  "message": "Data Order Sudah Terkunci"
}
```

### Jika Order Sudah Masuk Ke penerimaan Response (410)
```json
{
  "success": false,
  "message": "Data Order ini Sudah Masuk Ke penerimaan"
}
```

## 🔷 POST kunci Order 
**POST** `/api/v1/transactions/order/lock-order`

> Lock Order Hanya bisa jika flag order null / masih di status Draft


### Request Body (FormData)

| Parameter   | Type   | Required | Description                    |
| ----------- | ------ | -------- | ------------------------------ |
| nomor_order | string | ✔️        | nomor_order yang akan di kunci |

### Contoh Request
```json
{
  "nomor_order": "TRX000004",
}
```

### Response (200)
```json
{
  "success": true,
  "data": {
    "id": 4,
    "nomor_order": "TRX000004",
    "tgl_order": "2025-07-31",
    "flag": "1",
    "kode_user": "USR000001",
    "kode_supplier": "SUP002",
    "created_at": "2025-07-31T13:03:10.000000Z",
    "updated_at": "2025-07-31T13:20:27.000000Z",
    "order_records": [
      {
        "id": 9,
        "nomor_order": "TRX000004",
        "kode_barang": "BRG000034",
        "jumlah_pesan": "20",
        "kode_user": "USR000001",
        "satuan_k": "pil",
        "satuan_b": "strip",
        "isi": "20",
        "flag": "1",
        "created_at": "2025-07-31T13:03:10.000000Z",
        "updated_at": "2025-07-31T13:20:27.000000Z",
        "master": {
          "nama": "OBH Combi",
          "kode": "BRG000034",
          "satuan_k": "botol",
          "satuan_b": null,
          "isi": 100,
          "kandungan": null
        }
      }
    ],
    "supplier": {
      "id": 2,
      "nama": "Apotek Sentosa Supplier",
      "kode": "SUP002",
      "tlp": "021-7654321",
      "bank": "Mandiri",
      "rekening": "9876543210",
      "alamat": "Jl. Melati No. 10, Bekasi",
      "created_at": "2025-07-30T06:16:15.000000Z",
      "updated_at": "2025-07-30T06:16:15.000000Z"
    }
  },
  "message": "Data Orders berhasil Dikunci"
}
```
### Jika Order Sudah Terkunci (410)
```json
{
  "success": false,
  "message": "Data ini sudah terkunci."
}
```

## 🔷 POST buka kunci Order 
**POST** `/api/v1/transactions/order/unlock-order`

> Open Lock Order Hanya bisa jika belum masuk ke penerimaan dan data order dalam kondisi draft


### Request Body (FormData)

| Parameter   | Type   | Required | Description                         |
| ----------- | ------ | -------- | ----------------------------------- |
| nomor_order | string | ✔️        | nomor_order yang akan di buka kunci |

### Contoh Request
```json
{
  "nomor_order": "TRX000004",
}
```

### Response (200)
```json
{
  "success": true,
  "data": {
    "id": 4,
    "nomor_order": "TRX000004",
    "tgl_order": "2025-07-31",
    "flag": null,
    "kode_user": "USR000001",
    "kode_supplier": "SUP002",
    "created_at": "2025-07-31T13:03:10.000000Z",
    "updated_at": "2025-07-31T14:10:05.000000Z",
    "order_records": [
      {
        "id": 9,
        "nomor_order": "TRX000004",
        "kode_barang": "BRG000034",
        "jumlah_pesan": "20",
        "kode_user": "USR000001",
        "satuan_k": "pil",
        "satuan_b": "strip",
        "isi": "20",
        "flag": null,
        "created_at": "2025-07-31T13:03:10.000000Z",
        "updated_at": "2025-07-31T14:10:05.000000Z",
        "master": {
          "nama": "OBH Combi",
          "kode": "BRG000034",
          "satuan_k": "botol",
          "satuan_b": null,
          "isi": 100,
          "kandungan": null
        }
      }
    ],
    "supplier": {
      "id": 2,
      "nama": "Apotek Sentosa Supplier",
      "kode": "SUP002",
      "tlp": "021-7654321",
      "bank": "Mandiri",
      "rekening": "9876543210",
      "alamat": "Jl. Melati No. 10, Bekasi",
      "created_at": "2025-07-30T06:16:15.000000Z",
      "updated_at": "2025-07-30T06:16:15.000000Z"
    }
  },
  "message": "Kunci Data Orders Berhasil Dibuka"
}
```

### Jika Order Belum Terkunci (410)
```json
{
  "success": false,
  "message": "Data ini belum terkunci."
}
```

### Jika Order Sudah Masuk Ke penerimaan (410)
```json
{
  "success": false,
  "message": "Data Order ini Sudah Masuk Ke penerimaan."
}
```

## 🔷 POST Hapus Order 
**POST** `/api/v1/transactions/order/delete`

> Delete Order hanya bisa di status draft


### Request Body (FormData)

| Parameter   | Type   | Required | Description                   |
| ----------- | ------ | -------- | ----------------------------- |
| nomor_order | string | ✔️        | Data order yang akan di hapus |

### Contoh Request
```json
{
  "nomor_order": "TRX000004",
}
```

### Response (200)
```json
{
  "success": true,
  "message": "Data order berhasil dihapus"
}
```

### Jika Sudah Masuk ke penerimaan (410)
```json
{
  "success": false,
  "message": "Data Order ini Sudah Masuk Ke penerimaan"
}
```

### Jika Order Terkunci (410)
```json
{
  "success": false,
  "message": "Data Order ini Tidak Dapat dirubah"
}
```

## 🔷 POST Hapus Rinci / record 
**POST** `/api/v1/transactions/order/delete-record`

> Delete Order Rinci / Record hanya bisa di status draft


### Request Body (FormData)

| Parameter   | Type   | Required | Description                         |
| ----------- | ------ | -------- | ----------------------------------- |
| nomor_order | string | ✔️        | nomor_order header                  |
| kode_barang | string | ✔️        | Data order rinci yang akan di hapus |

### Contoh Request
```json
{
  "nomor_order": "TRX000005",
  "kode_barang": "BRG000023",
}
```

### Response (200)
```json
{
  "success": true,
  "data": {
    "id": 10,
    "nomor_order": "TRX000005",
    "kode_barang": "BRG000034",
    "jumlah_pesan": "20",
    "kode_user": "USR000001",
    "satuan_k": "pil",
    "satuan_b": "strip",
    "isi": "20",
    "flag": null,
    "created_at": "2025-07-31T13:56:35.000000Z",
    "updated_at": "2025-07-31T13:56:35.000000Z"
  },
  "message": "Data record berhasil dihapus"
}
```

### Jika Flag Order Terkunci Flag (410)
```json
{
  "succes": false,
  "message": "Data Order ini Tidak Dapat dirubah."
}
```

### Jika Sudah Masuk ke penerimaan (410)
```json
{
  "success": false,
  "message": "Data Order ini Sudah Masuk Ke penerimaan"
}
```