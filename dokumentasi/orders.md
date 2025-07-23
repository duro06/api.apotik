# 📄 Dokumentasi API OrderController

Base URL: `/api/v1/transactions/order`

## 🔷 Get Order List (Header + Records)

**GET** `/api/v1/transactions/order/get-list`

> Mengambil daftar order lengkap dengan header dan records, termasuk pencarian, sorting, dan pagination.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                                              |
| --------- | ------ | -------- | ------------ | ------------------------------------------------------------------ |
| q         | string | ❌       | -            | Kata kunci pencarian (`nomor_order`, `kode_user`, `kode_supplier`) |
| order_by  | string | ❌       | `created_at` | Kolom sorting (`nomor_order`, `tgl_order`, `created_at`, dll)      |
| sort      | string | ❌       | `asc`        | Arah sorting (`asc` atau `desc`)                                   |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman                                            |
| page      | int    | ❌       | 1            | Halaman yang diambil                                               |

### Contoh Request

```http
GET /api/v1/transactions/order/get-list?q=TRX&order_by=tgl_order&sort=desc&per_page=5
```

### Response (200)

```json
{
    "data": [
        {
            "header": {
                "nomor_order": "TRX20230001",
                "tgl_order": "2023-01-15",
                "kode_user": "USR001",
                "kode_supplier": "SUP001",
                "created_at": "...",
                "updated_at": "..."
            },
            "records": [
                {
                    "nomor_order": "TRX20230001",
                    "kode_barang": "BRG001",
                    "satuan_k": "PCS",
                    "satuan_b": "BOX",
                    "isi": 10,
                    "created_at": "...",
                    "updated_at": "..."
                }
            ]
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 5,
        "total": 15
    }
}
```

## 🔷 Get Order Header List

**GET** `/api/v1/transactions/order/header/get-list`

> Mengambil daftar order header saja.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                                              |
| --------- | ------ | -------- | ------------ | ------------------------------------------------------------------ |
| q         | string | ❌       | -            | Kata kunci pencarian (`nomor_order`, `kode_user`, `kode_supplier`) |
| order_by  | string | ❌       | `created_at` | Kolom sorting (`nomor_order`, `tgl_order`, `created_at`, dll)      |
| sort      | string | ❌       | `asc`        | Arah sorting (`asc` atau `desc`)                                   |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman                                            |
| page      | int    | ❌       | 1            | Halaman yang diambil                                               |

### Response (200)

```json
{
    "data": [
        {
            "nomor_order": "TRX20230001",
            "tgl_order": "2023-01-15",
            "kode_user": "USR001",
            "kode_supplier": "SUP001",
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 25
    }
}
```

## 🔷 Get Order Record List

**GET** `/api/v1/transactions/order/record/get-list`

> Mengambil daftar order records saja.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                                         |
| --------- | ------ | -------- | ------------ | ------------------------------------------------------------- |
| q         | string | ❌       | -            | Pencarian (`nomor_order` atau `kode_barang`)                  |
| order_by  | string | ❌       | `created_at` | Kolom sorting (`nomor_order`, `tgl_order`, `created_at`, dll) |
| sort      | string | ❌       | `asc`        | Arah sorting (`asc` atau `desc`)                              |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman                                       |
| page      | int    | ❌       | 1            | Halaman yang diambil                                          |

### Response (200)

```json
{
    "data": [
        {
            "nomor_order": "TRX20230001",
            "kode_barang": "BRG001",
            "satuan_k": "PCS",
            "satuan_b": "BOX",
            "isi": 10,
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 50
    }
}
```

## 🔷 Simpan Order Record List

**POST** `/api/v1/transactions/order/simpan`

> Membuat atau mengupdate order lengkap (header dan records).

### Request Body

```json
{
    "nomor_order": "TRX20230001", // Optional (jika kosong akan digenerate)
    "tgl_order": "2023-01-15", // Optional (default: sekarang)
    "kode_user": "USR001", // Required
    "kode_supplier": "SUP001", // Required
    "items": [
        // Required (minimal 1 item)
        {
            "kode_barang": "BRG001", // Required
            "satuan_k": "PCS", // Optional
            "satuan_b": "BOX", // Optional
            "isi": 10 // Optional
        }
    ]
}
```

### Response (201 - Success)

```json
{
    "success": true,
    "data": {
        "header": {
            "nomor_order": "TRX20230001",
            "tgl_order": "2023-01-15",
            "kode_user": "USR001",
            "kode_supplier": "SUP001",
            "created_at": "...",
            "updated_at": "..."
        },
        "records": [
            {
                "nomor_order": "TRX20230001",
                "kode_barang": "BRG001",
                "satuan_k": "PCS",
                "satuan_b": "BOX",
                "isi": 10,
                "created_at": "...",
                "updated_at": "..."
            }
        ]
    },
    "message": "Data Orders berhasil disimpan"
}
```

### Response (400 - Validation Error)

```json
{
    "success": false,
    "message": "Kode User Harus Di isi."
}
```

## 🔷 Simpan/Update Order Header

**POST** `/api/v1/transactions/order/header/simpan`

> Membuat atau mengupdate order header saja.

### Request Body

```json
{
    "nomor_order": "TRX20230001", // Optional (jika kosong akan digenerate)
    "tgl_order": "2023-01-15", // Optional
    "kode_user": "USR001", // Required
    "kode_supplier": "SUP001" // Required
}
```

### Response (200 - Success)

```json
{
    "data": {
        "nomor_order": "TRX20230001",
        "tgl_order": "2023-01-15",
        "kode_user": "USR001",
        "kode_supplier": "SUP001",
        "created_at": "...",
        "updated_at": "..."
    },
    "message": "Data header berhasil disimpan"
}
```

## 🔷 Simpan/Update Order Record

**POST** `/api/v1/transactions/order/record/simpan`

> Membuat atau mengupdate order record.

### Request Body

```json
{
    "nomor_order": "TRX000007",
    "kode_barang": "BRG001",
    "kode_user": "USR001",
    "satuan_k": "PCS",
    "satuan_b": "BOX",
    "isi": 10
}
```

### Response (200 - Success)

```json
{
    "data": {
        "nomor_order": "TRX20230001",
        "kode_barang": "BRG001",
        "satuan_k": "PCS",
        "satuan_b": "BOX",
        "isi": 10,
        "created_at": "...",
        "updated_at": "..."
    },
    "message": "Data record berhasil disimpan"
}
```

## 🔷 Hapus Order (Header + Records)

**POST** `/api/v1/transactions/order/delete`

> Menghapus order lengkap (header dan semua records terkait).

### Request Body

```json
{
    "nomor_order": "TRX20230001" // Required
}
```

### Response (200 - Success)

```json
{
    "message": "Data order berhasil dihapus"
}
```

### Response (404 - Not Found)

```json
{
    "message": "Nomor order tidak ditemukan"
}
```

## 🔷 Hapus Order Header

**POST** `/api/v1/transactions/order/header/delete`

> Menghapus order lengkap (header dan semua records terkait).

### Request Body

```json
{
    "nomor_order": "TRX20230001" // Required
}
```

### Response (200 - Success)

```json
{
    "data": {
        "id": 1,
        "nomor_order": "TRX20230001",
        "...": "..."
    },
    "message": "Data header berhasil dihapus"
}
```

## 🔷 Hapus Order Record

**POST** `/api/v1/transactions/order/record/delete`

> Menghapus order record tertentu.

### Request Body

```json
{
    "nomor_order": "TRX20230001", // Required
    "kode_barang": "BRG001"
}
```

### Response (200 - Success)

```json
{
    "data": {
        "id": 1,
        "nomor_order": "TRX20230001",
        "kode_barang": "BRG001",
        "...": "..."
    },
    "message": "Data record berhasil dihapus"
}
```
