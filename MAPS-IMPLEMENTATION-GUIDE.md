# Implementasi Fitur Maps dengan Leaflet

## Overview

Fitur maps telah berhasil diimplementasikan menggunakan Leaflet dan OpenStreetMap API dengan plugin Leaflet Control Geocoder untuk pencarian lokasi. Provider dapat menentukan lokasi activity atau residence melalui fitur search maps atau dengan pin langsung di maps.

## Fitur yang Diimplementasikan

### 1. Database Schema

-   Menambahkan kolom `latitude` dan `longitude` pada tabel `activities` dan `residences`
-   Tipe data: `decimal(10,8)` untuk latitude dan `decimal(11,8)` untuk longitude
-   Kolom nullable untuk fleksibilitas

### 2. Model Updates

-   Update model `Activity` dan `Residence` untuk include field koordinat
-   Menambahkan casting untuk koordinat sebagai decimal
-   Field koordinat ditambahkan ke `$fillable` array

### 3. Form Validation

-   Menambahkan validasi untuk koordinat di semua form request:
    -   `StoreActivityRequest`
    -   `UpdateActivityRequest`
    -   `StoreResidenceRequest`
    -   `UpdateResidenceRequest`
-   Validasi: `nullable|numeric|between:-90,90` untuk latitude dan `between:-180,180` untuk longitude

### 4. API Response Updates

-   Update semua API response untuk include koordinat
-   User API: `ActivityController` dan `ResidenceController`
-   Provider API: `ActivityController` dan `ResidenceController`

### 5. Frontend Implementation

#### JavaScript Files

-   `public/js/leaflet-maps.js`: Class utama untuk mengelola maps
-   `public/css/leaflet-maps.css`: Styling untuk maps dan komponen terkait

#### Maps Features

-   **Interactive Maps**: Provider dapat klik di peta untuk memilih lokasi
-   **Geocoder Search**: Pencarian alamat menggunakan Nominatim API
-   **Current Location**: Tombol untuk mendapatkan lokasi saat ini
-   **Auto-fill Coordinates**: Koordinat terisi otomatis saat memilih lokasi
-   **Reverse Geocoding**: Alamat terisi otomatis dari koordinat
-   **Search Integration**: Pencarian dari input field alamat/lokasi

#### Provider Forms

-   **Activity Forms**: Create dan Edit dengan maps integration
-   **Residence Forms**: Create dan Edit dengan maps integration
-   **Map Controls**: Tombol untuk lokasi saat ini dan hapus lokasi
-   **Coordinate Display**: Input field untuk latitude dan longitude

#### User Views

-   **Activity Detail**: Menampilkan maps dengan marker lokasi
-   **Residence Detail**: Menampilkan maps dengan marker lokasi
-   **Map Info**: Informasi koordinat dan alamat

## Cara Penggunaan

### Untuk Provider

#### 1. Membuat Activity/Residence Baru

1. Isi form seperti biasa
2. Di section "Peta Lokasi":
    - Klik "Lokasi Saat Ini" untuk menggunakan GPS
    - Atau ketik alamat di field "Lokasi"/"Alamat" untuk pencarian otomatis
    - Atau gunakan kotak pencarian di atas peta
    - Atau klik langsung di peta untuk memilih lokasi
3. Koordinat akan terisi otomatis
4. Submit form

#### 2. Edit Activity/Residence

1. Buka halaman edit
2. Maps akan menampilkan lokasi yang sudah ada
3. Ubah lokasi dengan cara yang sama seperti create
4. Save perubahan

### Untuk User

#### 1. Melihat Detail Activity/Residence

1. Buka halaman detail activity atau residence
2. Scroll ke section "Lokasi Kegiatan"/"Lokasi Residence"
3. Maps akan menampilkan lokasi dengan marker
4. Klik marker untuk melihat informasi detail

## Technical Details

### Dependencies

-   **Leaflet**: 1.9.4
-   **Leaflet Control Geocoder**: 1.13.0
-   **OpenStreetMap**: Tiles dan Nominatim API

### API Endpoints

-   **Nominatim Search**: `https://nominatim.openstreetmap.org/search`
-   **Nominatim Reverse**: `https://nominatim.openstreetmap.org/reverse`

### File Structure

```
public/
├── js/
│   └── leaflet-maps.js
├── css/
│   └── leaflet-maps.css
resources/views/
├── provider/
│   ├── activities/
│   │   ├── create.blade.php (updated)
│   │   └── edit.blade.php (updated)
│   └── residences/
│       ├── create.blade.php (updated)
│       └── edit.blade.php (updated)
└── user/
    ├── activities/
    │   └── show.blade.php (updated)
    └── residences/
        └── show.blade.php (updated)
```

### Database Changes

```sql
-- Activities table
ALTER TABLE activities ADD COLUMN latitude DECIMAL(10,8) NULL AFTER location;
ALTER TABLE activities ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude;

-- Residences table
ALTER TABLE residences ADD COLUMN latitude DECIMAL(10,8) NULL AFTER address;
ALTER TABLE residences ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude;
```

## Features

### Maps Controls

-   **Zoom In/Out**: Mouse wheel atau tombol zoom
-   **Pan**: Drag untuk memindahkan peta
-   **Search**: Kotak pencarian di atas peta
-   **Current Location**: Tombol untuk lokasi GPS
-   **Clear Location**: Tombol untuk menghapus lokasi

### Search Functionality

-   **Address Search**: Ketik alamat di input field
-   **Map Search**: Gunakan geocoder di peta
-   **Auto-complete**: Saran alamat saat mengetik
-   **Country Filter**: Terbatas pada Indonesia

### Responsive Design

-   Maps responsive untuk mobile dan desktop
-   Touch-friendly controls untuk mobile
-   Adaptive layout untuk berbagai ukuran layar

## Error Handling

-   Validasi koordinat di backend
-   Error handling untuk geocoding failures
-   Fallback untuk browser yang tidak support geolocation
-   User-friendly error messages

## Security Considerations

-   CSRF protection untuk semua form
-   Input validation dan sanitization
-   Rate limiting untuk API calls
-   XSS protection untuk user input

## Performance Optimizations

-   Lazy loading untuk maps
-   Caching untuk geocoding results
-   Optimized tile loading
-   Minimal JavaScript footprint

## Browser Support

-   Modern browsers (Chrome, Firefox, Safari, Edge)
-   Mobile browsers (iOS Safari, Chrome Mobile)
-   Fallback untuk browser lama

## Future Enhancements

-   Clustering untuk multiple markers
-   Custom map styles
-   Offline map support
-   Advanced search filters
-   Route planning integration







