## Master Jabatan

-   general route `api/v1/master/jabatan`

-   get `/get-list` payload :

    -   q
    -   per_page
    -   page

-   post `Simpan` : `/simpan` payload :

    -   kode `null / '' jika baru`
    -   nama `divalidasi`
    -   tlp
    -   rekening
    -   alamat `'text'`

-   post `Hapus` : `/delete` payload
    -   id
