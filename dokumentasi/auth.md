# 📄 Dokumentasi API AuthController

Base URL: `/api/v1/auth`

---

## 🔷 Register

**POST** `/api/v1/auth/register`

> Digunakan untuk mendaftarkan user baru. dan hanya bisa digunakan oleh user yang sudah login

### Request Body

| Field                 | Type   | Required | Notes                             |
| --------------------- | ------ | -------- | --------------------------------- |
| nama                  | string | ✅       | Panjang 2–100 karakter            |
| username              | string | ✅       | Unik, panjang 2–100 karakter      |
| email                 | string | ✅       | Format email valid, unik, max 100 |
| password              | string | ✅       | Min. 6 karakter                   |
| password_confirmation | string | ✅       | Harus sama dengan `password`      |
| hp                    | string | ❌       | Nomor HP, opsional                |
| alamat                | string | ❌       | Opsional                          |
| kode_jabatan          | string | ❌       | Opsional                          |

### Contoh Request

```json
{
    "nama": "Budi",
    "username": "budi123",
    "email": "budi@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "hp": "08123456789",
    "alamat": "Jl. Contoh",
    "kode_jabatan": "STAFF"
}
```

### Response Sukses (201)

```json
{
    "message": "User successfully registered",
    "user": {
        "id": 1,
        "nama": "Budi",
        "username": "budi123",
        "email": "budi@example.com",
        "hp": "08123456789",
        "alamat": "Jl. Contoh",
        "kode_jabatan": "STAFF",
        "kode": "USR00001",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

### Response Gagal (401)

```json
{
    "message": "registrasi gagal"
}
```

---

## 🔷 Login

**POST** `/api/v1/auth/login`

> Digunakan untuk login user. Bisa pakai `email` atau `username`.

### Request Body

| Field    | Type   | Required | Notes               |
| -------- | ------ | -------- | ------------------- |
| login    | string | ✅       | Email atau username |
| password | string | ✅       | Password            |

### Contoh Request

```json
{
    "login": "budi123",
    "password": "password123"
}
```

### Response Sukses (200)

```json
{
    "token": "token_sanctum_disini"
}
```

### Response Gagal (401)

```json
{
    "message": "Invalid Credentials"
}
```

---

## 🔷 Logout

**POST** `/api/v1/auth/logout`

> Logout user & hapus semua token.

### Header

Wajib mengirimkan token Bearer pada header:

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
    "message": "Logout Successfully"
}
```

---

## 🔷 Profile

**GET** `/api/v1/auth/profile`

> Mengambil data user yang sedang login.

### Header

Wajib mengirimkan token Bearer pada header:

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
    "user": {
        "id": 1,
        "nama": "Budi",
        "username": "budi123",
        "email": "budi@example.com",
        "hp": "08123456789",
        "alamat": "Jl. Contoh",
        "kode_jabatan": "STAFF",
        "kode": "USR00001",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

---

### 🔷 Catatan:

✅ Semua response error validasi (422) akan otomatis dikembalikan Laravel dengan format:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Pesan error di sini."]
    }
}
```

✅ Gunakan token hasil login di semua request yang butuh autentikasi (`logout`, `profile`).

✅ Token menggunakan Laravel Sanctum.
