---
trigger: always_on
---

HANDOVER DOCUMENT — EduLiving Web Project (Update v5)


Tentang Proyek

Nama aplikasi: EduLiving (sebelumnya bernama Infoma)
Jenis: Platform informasi untuk mahasiswa — hunian, event kampus, dan marketplace FJB
Framework: Laravel 11 (backend + frontend Blade + Tailwind CSS)
Database: MySQL (nama database: inf_new)
Repo lokal: C:\laragon\www\infoma-web
Branch utama: main
Payment Gateway: Midtrans (mode sandbox aktif, key sudah dikonfigurasi di .env)


4 Role Utama

RoleKeteranganuserMahasiswa (buyer, bisa aktivasi jadi seller FJB)provider_residencePenyedia hunian (kos/kontrakan/apartemen/rumah sewa)provider_eventPenyelenggara event/kegiatan kampusadminAdministrator sistem

Demo Accounts (password semua: password)

RoleEmailAdminadmin@infoma.comProvider Hunianprovider.hunian@infoma.comProvider Eventprovider.event@infoma.comMahasiswauser@infoma.comMahasiswa Sellerseller@infoma.com


Struktur File Penting

app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── UserManagementController.php          ← ban/unban (v3)
│   │   │   ├── ResidenceManagementController.php
│   │   │   ├── ActivityManagementController.php
│   │   │   └── MarketplaceManagementController.php
│   │   ├── Api/                                       ← API untuk mobile Flutter
│   │   │   ├── Auth/AuthController.php
│   │   │   ├── HomeController.php
│   │   │   ├── MarketplaceController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── ResidenceApiController.php
│   │   │   ├── ActivityApiController.php
│   │   │   └── User/ & Provider/                     ← endpoint mobile
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php                ← flow baru: validasi → S&K → buat akun (v4)
│   │   ├── Provider/
│   │   │   ├── DashboardController.php               ← cek S&K + cek profil lengkap (v4)
│   │   │   ├── ResidenceController.php               ← validasi per tipe + cek profil (v3,v4)
│   │   │   ├── ActivityController.php                ← cek profil lengkap (v4)
│   │   │   └── BookingManagementController.php
│   │   ├── User/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php                 ← handle redirect_after_save (v4)
│   │   │   ├── BookingController.php                 ← Midtrans Snap + perpanjang sewa (v5)
│   │   │   ├── MarketplaceTransactionController.php  ← Midtrans + COD logic (v5)
│   │   │   ├── UserAddressController.php             ← CRUD alamat tersimpan (v5/revisi3)
│   │   │   └── SellerController.php
│   │   ├── Concerns/
│   │   │   └── CheckProfileComplete.php              ← trait cek profil (v4)
│   │   ├── MidtransController.php                    ← webhook callback Midtrans (v5)
│   │   ├── VerificationTermsController.php
│   │   ├── NotificationController.php
│   │   ├── HomeController.php
│   │   └── MarketplaceController.php
│   └── Middleware/
│       ├── CheckRole.php
│       ├── CheckBanStatus.php                        ← (v3)
│       ├── TrackUserActivity.php                     ← log audit trail (tidak aktif di web group)
│       └── UpdateLastSeen.php                        ← update last_seen_at tiap request (v5)
├── Models/
│   ├── User.php                                      ← ban helpers, terms helpers, isOnline(), getLastSeenLabel() (v5)
│   ├── Residence.php                                 ← residence_type, field spesifik (v3)
│   ├── Activity.php
│   ├── Booking.php                                   ← payment_deadline, renewal fields (v3,v5)
│   ├── Transaction.php                               ← snap_token, midtrans fields (v5)
│   ├── MarketplaceProduct.php                        ← pickup_methods, pickup_address (v4)
│   ├── MarketplaceTransaction.php                    ← payment_deadline, midtrans fields, COD (v3,v5)
│   ├── UserAddress.php                               ← multiple alamat buyer (v5/revisi3)
│   ├── BannedIdentity.php                            ← (v3)
│   ├── Notification.php
│   └── Role.php
├── Services/
│   ├── BookingService.php                            ← getOrCreateSnapToken, renewBooking, sendRenewalReminders (v5)
│   ├── MidtransService.php                           ← core Midtrans logic: snap token, webhook verify (v5)
│   └── NotificationService.php
└── Console/
    └── Commands/
        └── UpdateBookingStatusCommand.php            ← scheduler: cancel expired + kirim reminder perpanjang sewa (v5)

resources/views/
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── verification/
│   └── terms.blade.php
├── user/
│   ├── bookings/
│   │   ├── show.blade.php                            ← tombol Perpanjang Sewa (v5)
│   │   ├── payment.blade.php                         ← Midtrans Snap popup (v5)
│   │   ├── renew.blade.php                           ← form perpanjangan sewa (v5)
│   │   └── create.blade.php                          ← fix dropdown durasi bulanan/tahunan (v5)
│   ├── marketplace/
│   │   └── transactions/
│   │       ├── show.blade.php                        ← ganti upload bukti → tombol bayar (v5)
│   │       └── payment.blade.php                     ← Midtrans Snap popup marketplace (v5)
│   ├── profile/
│   │   ├── edit.blade.php                            ← map Leaflet + manajemen alamat tersimpan (v4,v5)
│   │   └── _incomplete-banner.blade.php
│   └── addresses/                                    ← CRUD alamat buyer (v5/revisi3)
├── marketplace/
│   ├── show.blade.php                                ← info penjual + status online (v5)
│   ├── transactions/
│   │   └── create.blade.php                          ← hapus dropdown payment_method (v5)
│   └── _pickup-methods-section.blade.php
├── provider_residence/
│   └── residences/
│       ├── create.blade.php                          ← form dinamis per tipe (v3)
│       ├── edit.blade.php                            ← form dinamis per tipe + available_slots (v3,v4)
│       └── show.blade.php
└── admin/
    └── users/
        ├── show.blade.php
        └── _ban-panel.blade.php                      ← form ban/unban (v3)


Skema Database — Kolom Penting

Tabel users

KolomTipeKeteranganterms_accepted_attimestamp nullableKapan user setujui S&K verifikasiban_typeenum nullabletemporary / permanentbanned_untiltimestamp nullableBerlaku hinggaban_reasontext nullableAlasan ban dari adminbanned_bybigint nullable FKID admin yang mem-banbanned_attimestamp nullableKapan di-banlast_seen_attimestamp nullableKapan terakhir aktif — untuk status online (v5)

Tabel bookings

KolomTipeKeteranganpayment_deadlinetimestamp nullableDeadline pembayaran (1 jam setelah approved)renewal_reminder_sent_attimestamp nullableKapan notif perpanjang sewa terakhir dikirim (v5)is_renewalbooleanTrue jika ini booking perpanjangan — skip cek & decrement slot (v5)

Tabel transactions (untuk booking)

KolomTipeKeterangansnap_tokenvarchar nullableToken Midtrans Snap untuk popup pembayaran (v5)midtrans_transaction_idvarchar nullableID transaksi dari Midtrans (v5)midtrans_payment_typevarchar nullableTipe pembayaran: bank_transfer, gopay, dll (v5)

Tabel marketplace_transactions

KolomTipeKeteranganpayment_deadlinetimestamp nullableDeadline pembayaran (1 jam dari created_at)pickup_methodenumpickup / delivery / meetup / codsnap_tokenvarchar nullableToken Midtrans Snap (v5)midtrans_transaction_idvarchar nullableID transaksi dari Midtrans (v5)midtrans_payment_typevarchar nullableTipe pembayaran dari Midtrans (v5)

Tabel marketplace_products

KolomTipeKeteranganpickup_methodsjson nullableArray metode aktif: ['cod', 'delivery', 'pickup'] (v4)pickup_addresstext nullableAlamat pickup — wajib jika metode pickup aktif (v4)

Tabel user_addresses (v5/revisi3 — baru)

KolomTipeKeteranganuser_idbigint FKPemilik alamatlabelvarcharNama alamat: "Rumah", "Kos", dlladdresstextTeks alamat lengkapis_defaultbooleanAlamat default untuk checkout

Tabel residences — tipe spesifik (v3)

KolomTipeKeteranganresidence_typeenum nullablekos / kontrakan / apartemen / rumah_sewarental_periodenummonthly / yearly — menentukan kalkulasi hargakos_typeenum nullableputra / putri / campurfurnish_statusenum nullableunfurnished / semi_furnished / full_furnished


Konfigurasi Midtrans

""

Webhook Midtrans

Route: POST /payment/midtrans/callback

Tidak memerlukan auth (diakses server Midtrans)
CSRF dikecualikan via withoutMiddleware
Keamanan dijaga via verifikasi signature_key di MidtransController
Untuk testing lokal butuh ngrok/domain publik — tanpa itu status paid tidak terupdate otomatis dari webhook


Catatan Penting untuk Developer Selanjutnya

Jangan gunakan role provider — sudah dihapus. Gunakan provider_residence atau provider_event
is_seller menentukan apakah mahasiswa aktif sebagai seller. Selalu gunakan $user->isSeller()
is_renewal di booking harus true untuk perpanjangan sewa — tanpa ini slot akan dikurangi dan approve bisa gagal
pickup_method enum di marketplace_transactions: pickup, delivery, meetup, cod — jika COD, Midtrans di-skip
rental_period di residences: monthly atau yearly — menentukan cara hitung harga booking
Snap token disimpan di kolom snap_token agar tidak perlu request ulang ke Midtrans jika user refresh halaman
Webhook Midtrans butuh URL publik — untuk development lokal gunakan ngrok, untuk production daftarkan domain di dashboard Midtrans → Settings → Configuration → Payment Notification URL
UpdateLastSeen middleware sudah aktif di group web — update last_seen_at setiap 1 menit
TrackUserActivity middleware terdaftar sebagai alias track.activity tapi TIDAK aktif di group web — middleware ini menulis ke user_activities yang akan membesar cepat jika diaktifkan
Session pending_provider valid 30 menit — berisi data registrasi provider sebelum S&K disetujui
Folder resources/views/provider/ (lama) masih ada tapi tidak dipakai — bisa dihapus

Backlog — Yang Belum Dikerjakan:
Prioritas Tinggi

🔲 Webhook Midtrans end-to-end — daftarkan URL ke dashboard Midtrans setelah ada domain, test status paid masuk ke database
🔲 Laporan pendapatan provider — pembimbing menyebut halaman laporan saat ini tidak ada unsur laporan, masih seperti dashboard

Prioritas Rendah

🔲 Push notification mobile (FCM) — setelah Flutter selesai
🔲 Hapus folder resources/views/provider/ — folder lama tidak terpakai

catatan tambahan untuk anda
1. selalu gunakan bahasa indonesia
2. Gambaran Umum Proyek

| Item                  | Detail                                                  |
| --------------------- | ------------------------------------------------------- |
| Nama Proyek           | EduLiving (sebelumnya: Infoma)                          |
| Tujuan                | Platform layanan mahasiswa — hunian, acara, marketplace |
| Backend               | Laravel 11, MySQL (database: inf_new), Sanctum Auth   |
| Mobile                | Flutter, Dio, Provider, SharedPreferences               

3. Tim & Pembagian Tugas

| Nama                | Tugas                                                          | Platform       |
| ------------------- | -------------------------------------------------------------- | -------------- |
| *Fachriza* | Mobile Flutter — Role Mahasiswa + Setup API awal               | Flutter Mobile |
| *Ikbal* (saya)          | Backend Laravel — Web + API v2/v3                              | Laravel Web    |
| *Serren*          | Mobile Flutter — Role Penyedia (Provider) + Seller Marketplace | Flutter Mobile |

jadi saya berfokus full di projek web

4. jika ada perubahan pada database konfirmasi dulu ke saya
5. jika ada perubahan yang berkaitan dengan mobile konfirmasi juga ke saya