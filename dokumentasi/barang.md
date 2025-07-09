## Master Barang

-   general route `api/v1/master/barang`

-   get `/get-list` payload :

    -   q
    -   per_page
    -   page

-   post `Simpan` : `/simpan` payload :

    -   kode `null / '' jika baru`
    -   nama `divalidasi`
    -   satuan_k
    -   satuan_b
    -   isi
    -   kandungan
    -   harga_jual_resep_k
    -   harga_jual_biasa_k

-   post `Hapus` : `/delete` payload
    -   id
