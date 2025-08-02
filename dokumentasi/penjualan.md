# Dokumentasi API Penjualan

## 1. Daftar Obat

**Endpoint:** `GET /api/penjualan/obat`

**Query Parameters:**

-   `q` (string, optional): Pencarian nama/kode obat.
-   `order_by` (string, default: `nama`): Kolom pengurutan.
-   `sort` (string, default: `asc`): Urutan (`asc`/`desc`).
-   `page` (int, default: 1): Halaman.
-   `per_page` (int, default: 10): Jumlah data per halaman.

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "kode": "OBT001",
            "nama": "Paracetamol",
            "satuan_k": "...",
            "satuan_b": "...",
            "isi": 10,
            "kandungan": "..."
        }
        // ...
    ]
}
```

---

## 2. Daftar Penjualan

**Endpoint:** `GET /api/penjualan`

**Query Parameters:**

-   `q` (string, optional): Pencarian nama/kode penjualan.
-   `order_by` (string, default: `created_at`): Kolom pengurutan.
-   `sort` (string, default: `asc`): Urutan (`asc`/`desc`).
-   `page` (int, default: 1): Halaman.
-   `per_page` (int, default: 10): Jumlah data per halaman.

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "nopenjualan": "TRX0001",
            "tgl_penjualan": "2024-06-01",
            "kode_pelanggan": "...",
            "kode_dokter": "...",
            "cara_bayar": "...",
            "rinci": [
                {
                    "kode_barang": "OBT001",
                    "jumlah_k": 10,
                    "harga_jual": 5000,
                    "subtotal": 50000,
                    "master": {
                        "nama": "Paracetamol",
                        "kode": "OBT001",
                        "satuan_k": "...",
                        "satuan_b": "...",
                        "isi": 10,
                        "kandungan": "..."
                    }
                }
            ]
        }
        // ...
    ],
    "meta": {
        "total": 100,
        "per_page": 10,
        "current_page": 1
    }
}
```

---

## 3. Simpan Penjualan (Tambah/Update)

**Endpoint:** `POST /api/penjualan/simpan`

**Body Parameters:**

-   `nopenjualan` (string, optional): Nomor penjualan (jika update).
-   `tgl_penjualan` (date, optional): Tanggal penjualan.
-   `kode_pelanggan` (string, optional)
-   `kode_dokter` (string, optional)
-   `kode_barang` (string, required)
-   `jumlah_k` (int, required)
-   `satuan_k` (string, optional)
-   `satuan_b` (string, optional)
-   `isi` (int, required)
-   `harga_jual` (int, required)
-   `harga_beli` (int, required)
-   `id_penerimaan_rinci` (int, required)
-   `nopenerimaan` (string, required)
-   `nobatch` (string, required)
-   `tgl_exprd` (date, required)
-   `id_stok` (int, required)

**Response:**

```json
{
  "message": "Data berhasil disimpan",
  "data": { ...header penjualan... },
  "rinci": { ...rincian penjualan... }
}
```

---

## 4. Pembayaran Penjualan

**Endpoint:** `POST /api/penjualan/bayar`

**Body Parameters:**

-   `id` (int, required): ID header penjualan.
-   `diskon` (int, optional): Diskon.
-   `jumlah_bayar` (int, required): Jumlah uang dibayarkan.
-   `kembali` (int, optional): Uang kembalian.
-   `cara_bayar` (string, required): Metode pembayaran.

**Response:**

```json
{
  "message": "Pembayaran berhasil dilakukan",
  "data": { ...header penjualan... }
}
```

---

## 5. Hapus Rincian Penjualan

**Endpoint:** `DELETE /api/penjualan/hapus`

**Body Parameters:**

-   `id` (int, required): ID rincian penjualan.

**Response:**

```json
{
    "message": "Data Obat sudah dihapus"
}
```

---

**Catatan:**

-   Semua endpoint membutuhkan autentikasi.
-   Jika terjadi error, response akan berisi pesan error dan detail trace untuk debugging.
