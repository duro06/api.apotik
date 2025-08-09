# 📄 Dokumentasi API PenjualanController

Base URL: `/api/transactions/penjualan`

---

## 🔷 Get List Obat

**GET** `/get-list-obat`

> Mengambil daftar obat dengan stok untuk penjualan.

### Query Parameters

| Parameter | Type   | Required | Default | Notes                                 |
| --------- | ------ | -------- | ------- | ------------------------------------- |
| q         | string | ❌       | -       | Kata kunci pencarian (`nama`, `kode`) |
| order_by  | string | ❌       | `nama`  | Kolom untuk sorting                   |
| sort      | string | ❌       | `asc`   | Arah sorting: `asc` atau `desc`       |
| per_page  | int    | ❌       | 10      | Jumlah item per halaman               |
| page      | int    | ❌       | 1       | Halaman yang diambil                  |

### Response Success (200)

```json
{
    "data": [
        {
            "nama": "Paracetamol",
            "kode": "OBT001",
            "harga_jual_resep_k": "8000",
            "harga_jual_biasa_k": "6000",
            "id_penerimaan_rinci": 1,
            "id_stok": 1,
            "harga_beli": "4000",
            "satuan_k": "Tablet",
            "satuan_b": "Box",
            "isi": 10,
            "nobatch": "B001",
            "tgl_exprd": "2025-01-01"
        }
    ]
}
```

---

## 🔷 Get List Penjualan

**GET** `/get-list`

> Mengambil daftar transaksi penjualan dengan filter tanggal.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                |
| --------- | ------ | -------- | ------------ | ------------------------------------ |
| q         | string | ❌       | -            | Kata kunci pencarian                 |
| order_by  | string | ❌       | `created_at` | Kolom untuk sorting                  |
| sort      | string | ❌       | `asc`        | Arah sorting: `asc` atau `desc`      |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman              |
| page      | int    | ❌       | 1            | Halaman yang diambil                 |
| from      | date   | ❌       | today        | Filter tanggal awal (format: Y-m-d)  |
| to        | date   | ❌       | today        | Filter tanggal akhir (format: Y-m-d) |

### Response Success (200)

```json
{
    "data": [
        {
            "id": 1,
            "nopenjualan": "TRX0001",
            "tgl_penjualan": "2024-01-01 14:30:00",
            "kode_pelanggan": "PLG001",
            "kode_dokter": "DOK001",
            "cara_bayar": "TUNAI",
            "flag": null,
            "diskon": 0,
            "jumlah_bayar": 0,
            "kembali": 0,
            "rinci": [
                {
                    "id": 1,
                    "nopenjualan": "TRX0001",
                    "kode_barang": "OBT001",
                    "jumlah_k": 10,
                    "jumlah_b": 1,
                    "harga_jual": 5000,
                    "harga_beli": 4000,
                    "subtotal": 50000,
                    "master": {
                        "nama": "Paracetamol",
                        "kode": "OBT001",
                        "satuan_k": "Tablet",
                        "satuan_b": "Box",
                        "isi": 10,
                        "kandungan": "500mg"
                    }
                }
            ]
        }
    ],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "path": "...",
        "per_page": 10,
        "to": 10,
        "total": 100
    }
}
```

---

## 📌 Simpan Penjualan

**POST** `/tambah`

> Tambah atau update transaksi penjualan.

### Body Parameters

| Field               | Type   | Required | Notes                           |
| ------------------- | ------ | -------- | ------------------------------- |
| nopenjualan         | string | ❌       | Auto-generated jika tidak diisi |
| tgl_penjualan       | date   | ❌       | Default: waktu sekarang         |
| kode_pelanggan      | string | ❌       | -                               |
| kode_dokter         | string | ❌       | -                               |
| kode_barang         | string | ✅       | Kode obat yang dijual           |
| jumlah_k            | int    | ✅       | Jumlah dalam satuan kecil       |
| satuan_k            | string | ❌       | -                               |
| satuan_b            | string | ❌       | -                               |
| isi                 | int    | ✅       | Isi per satuan besar            |
| harga_jual          | int    | ✅       | Harga jual per satuan           |
| harga_beli          | int    | ✅       | Harga beli per satuan           |
| id_penerimaan_rinci | int    | ✅       | ID rincian penerimaan stok      |
| nopenerimaan        | string | ✅       | Nomor penerimaan stok           |
| nobatch             | string | ✅       | Nomor batch obat                |
| tgl_exprd           | date   | ✅       | Tanggal kadaluarsa              |
| id_stok             | int    | ✅       | ID stok obat                    |

### Response Success (200)

```json
{
  "message": "Data berhasil disimpan",
  "data": { ...header penjualan... },
  "rinci": { ...rincian penjualan... }
}
```

---

## 📌 Pembayaran Penjualan

**POST** `/bayar`

> Proses pembayaran transaksi penjualan.

### Body Parameters

| Field        | Type   | Required | Notes                       |
| ------------ | ------ | -------- | --------------------------- |
| id           | int    | ✅       | ID header penjualan         |
| diskon       | int    | ❌       | Nilai diskon                |
| jumlah_bayar | int    | ✅       | Jumlah uang yang dibayarkan |
| kembali      | int    | ❌       | Uang kembalian              |
| cara_bayar   | string | ✅       | Metode pembayaran           |

### Response Success (200)

```json
{
    "message": "Pembayaran berhasil dilakukan",
    "data": {
        "id": 1,
        "nopenjualan": "TRX0001",
        "tgl_penjualan": "2024-01-01 14:30:00",
        "kode_pelanggan": "PLG001",
        "kode_dokter": "DOK001",
        "cara_bayar": "TUNAI",
        "flag": "1",
        "diskon": 0,
        "jumlah_bayar": 50000,
        "kembali": 0,
        "rinci": [
            {
                "id": 1,
                "nopenjualan": "TRX0001",
                "kode_barang": "OBT001",
                "jumlah_k": 10,
                "jumlah_b": 1,
                "harga_jual": 5000,
                "harga_beli": 4000,
                "subtotal": 50000,
                "master": {
                    "nama": "Paracetamol",
                    "kode": "OBT001",
                    "satuan_k": "Tablet",
                    "satuan_b": "Box",
                    "isi": 10,
                    "kandungan": "500mg"
                }
            }
        ]
    }
}
```

### Catatan Tambahan

✅ Saat pembayaran berhasil:

-   Status transaksi (`flag`) akan diubah menjadi "1"
-   Stok obat akan otomatis berkurang sesuai jumlah pembelian
-   Jika kembalian yang dikirim tidak sesuai perhitungan, sistem akan menghitung ulang

---

## 📌 Hapus Rincian

**POST** `/hapus`

> Hapus rincian penjualan berdasarkan ID.

### Body Parameters

| Field | Type | Required | Notes                |
| ----- | ---- | -------- | -------------------- |
| id    | int  | ✅       | ID rincian penjualan |

### Response Success (200)

```json
{
    "message": "Data Obat sudah dihapus"
}
```

---

### 🔷 Catatan

✅ Semua endpoint membutuhkan autentikasi Sanctum.

✅ Response error validasi (422):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Pesan error di sini."]
    }
}
```

✅ Response error sistem (410):

```json
{
    "message": "Pesan error",
    "file": "...",
    "line": "...",
    "trace": [...]
}
```
