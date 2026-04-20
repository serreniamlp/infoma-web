# INFOMA - Aplikasi Informasi Kebutuhan Mahasiswa

## 📋 **Deskripsi Aplikasi**

**INFOMA** adalah aplikasi web berbasis Laravel + Tailwind CSS yang menyediakan informasi kebutuhan mahasiswa, khususnya:
- **Tempat Tinggal**: Kost dan kontrakan
- **Kegiatan Kampus**: Seminar, webinar, mentoring, lomba, training, dan aktivitas lainnya

### **Tujuan Aplikasi:**
- Memudahkan mahasiswa mencari tempat tinggal dan kegiatan kampus
- Menyediakan platform bagi penyedia untuk menawarkan layanan
- Memberikan sistem booking dan transaksi yang mudah dan aman

---

## 🎨 **Desain & Tema**

### **Color Scheme:**
- **Main Color**: Putih (White)
- **Secondary Colors**: Biru (Blue), Hitam (Black)
- **Font**: Poppins

### **Design Principles:**
- Clean dan modern
- User-friendly interface
- Responsive design
- Professional appearance

---

## 👥 **User Roles & Permissions**

### **1. User (Mahasiswa)**
**Fitur yang dapat dilakukan:**
- ✅ Booking residences/tempat tinggal dengan upload dokumen (KTP, dll)
- ✅ Booking activities dengan informasi yang dibutuhkan
- ✅ Menerapkan voucher/diskon yang tersedia
- ✅ Melakukan transaksi (tanpa payment gateway, hanya simulasi)
- ✅ Menambah/hapus residence atau activity ke bookmark
- ✅ Memberikan rating dan komentar (hanya setelah booking completed)
- ✅ Melihat status booking di dashboard
- ✅ Melihat riwayat booking berdasarkan status

### **2. Provider (Penyedia)**
**Fitur yang dapat dilakukan:**
- ✅ Membuat residence dengan informasi lengkap
- ✅ Membuat activities dengan informasi lengkap
- ✅ Menambahkan sistem diskon langsung (percentage/flat)
- ✅ CRUD operations untuk residence, activities
- ✅ Menerima/menolak booking dengan alasan penolakan
- ✅ Dashboard dengan informasi residence, activities, dan booking
- ✅ Manajemen booking (approve/reject)

### **3. Admin**
**Fitur yang dapat dilakukan:**
- ✅ CRUD untuk semua pengguna (semua role)
- ✅ Melihat history online dari pengguna
- ✅ Dashboard dengan statistik aplikasi
- ✅ User management dan monitoring

---

## 📱 **Halaman Utama Sistem**

### **1. Homepage**
- Menampilkan residence dan activities yang masih tersedia
- Featured items dengan rating tinggi
- Search bar utama
- Kategori populer

### **2. Residence Page**
- Daftar residence yang tersedia
- Filter pencarian: kategori, harga, periode sewa, lokasi
- Sorting: harga, rating, tanggal
- Pagination

### **3. Activities Page**
- Daftar activities yang tersedia dan registration masih terbuka
- Filter pencarian: kategori, tanggal, lokasi, harga
- Sorting: tanggal terdekat, harga, rating
- Pagination

### **4. Bookmark Page**
- Residence dan activities yang disimpan user
- Quick access untuk booking
- Remove dari bookmark

### **5. Booking History**
- Riwayat booking berdasarkan status:
  - Pending (menunggu approval)
  - Approved (disetujui)
  - Rejected (ditolak + alasan)
  - Completed (selesai)
  - Cancelled (dibatalkan)

### **6. Profile Page**
- Informasi user
- Edit profile
- Change password
- Upload profile picture

---

## 🏗️ **Struktur Database**

### **Core Tables:**
```
users (id, name, email, password, phone, address, profile_picture)
roles (id, name)
user_roles (user_id, role_id)
categories (id, name, type: residence/activity)
```

### **Business Tables:**
```
residences (
  id, provider_id, category_id, name, description, address,
  rental_period: monthly/yearly, price, capacity, available_slots,
  facilities: JSON, images: JSON, discount_type, discount_value, is_active
)

activities (
  id, provider_id, category_id, name, description, location,
  event_date, registration_deadline, price, capacity, available_slots,
  images: JSON, discount_type, discount_value, is_active
)
```

### **Transaction Tables:**
```
bookings (
  id, user_id, bookable_type, bookable_id, booking_code,
  check_in_date, check_out_date, documents: JSON,
  status: pending/approved/rejected/completed/cancelled,
  rejection_reason, notes
)

transactions (
  id, booking_id, transaction_code, original_amount,
  discount_amount, final_amount, payment_method,
  payment_status: pending/paid/failed, payment_proof
)
```

### **Social Tables:**
```
ratings (user_id, rateable_type, rateable_id, rating: 1-5, comment)
bookmarks (user_id, bookmarkable_type, bookmarkable_id)
notifications (user_id, title, message, type, is_read)
user_activities (user_id, action, description, ip_address, user_agent)
```

---

## ⚡ **Fitur Khusus**

### **1. Sistem Diskon**
```php
// Discount di residence/activity table
discount_type: 'percentage' | 'flat' | null
discount_value: decimal

// Calculation method:
getDiscountedPrice() {
  if (discount_type === 'percentage') 
    return price - (price * discount_value / 100);
  if (discount_type === 'flat') 
    return max(0, price - discount_value);
  return price;
}
```

### **2. Availability Management**
- **Initial**: available_slots = capacity
- **When Approved**: available_slots decrements
- **When Cancelled** (hanya jika pending): available_slots increments
- **Display**: Items dengan available_slots = 0 masih tampil tapi tidak bisa dibook

### **3. Booking Flow**
```
1. User pilih residence/activity
2. Upload dokumen (KTP, dll)
3. Submit booking → status: 'pending'
4. Provider approve/reject
5. Jika approved → buat transaction, kurangi available_slots
6. User bayar (simulasi) → update payment_status
7. Setelah check_out_date → status: 'completed'
8. User bisa kasih rating
```

### **4. Periode Sewa Logic**
```
Residence dengan rental_period:
- 'monthly' → check_out_date = check_in_date + 1 month
- 'yearly' → check_out_date = check_in_date + 1 year

Activity:
- check_in_date = event_date
- check_out_date = event_date
```

### **5. Rating System**
- Hanya user dengan booking status 'completed' yang bisa kasih rating
- One rating per user per item
- Rating 1-5 stars + optional comment
- Display average rating di item list

---

## 🔧 **Technical Stack**

### **Backend:**
- **Framework**: Laravel 10+
- **Database**: MySQL
- **Authentication**: Laravel Breeze
- **File Storage**: Laravel Storage (public disk)
- **Queue**: Database (for notifications)

### **Frontend:**
- **CSS Framework**: Tailwind CSS
- **JavaScript**: Alpine.js (optional)
- **Icons**: Heroicons atau Lucide
- **Image Optimization**: Laravel Intervention Image

### **Additional Packages:**
```php
// Suggested packages
"intervention/image" // Image manipulation
"spatie/laravel-permission" // Alternative role management
"maatwebsite/excel" // Export data
"barryvdh/laravel-dompdf" // Generate PDF reports
```

---

## 📂 **Folder Structure**

```
app/
├── Console/Commands/
│   └── UpdateBookingStatusCommand.php
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── User/
│   │   │   ├── ResidenceController.php
│   │   │   ├── ActivityController.php
│   │   │   ├── BookingController.php
│   │   │   ├── BookmarkController.php
│   │   │   └── RatingController.php
│   │   ├── Provider/
│   │   │   ├── DashboardController.php
│   │   │   ├── ResidenceController.php
│   │   │   ├── ActivityController.php
│   │   │   └── BookingManagementController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       └── UserManagementController.php
│   ├── Middleware/
│   │   ├── CheckRole.php
│   │   └── TrackUserActivity.php
│   ├── Requests/
│   │   ├── StoreResidenceRequest.php
│   │   ├── StoreActivityRequest.php
│   │   └── StoreBookingRequest.php
│   └── Policies/
│       ├── ResidencePolicy.php
│       ├── ActivityPolicy.php
│       └── BookingPolicy.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── Category.php
│   ├── Residence.php
│   ├── Activity.php
│   ├── Booking.php
│   ├── Transaction.php
│   ├── Rating.php
│   ├── Bookmark.php
│   ├── Notification.php
│   └── UserActivity.php
├── Services/
│   ├── BookingService.php
│   └── NotificationService.php
└── Policies/
```

---

## 🚀 **Installation Guide**

### **1. Setup Laravel Project**
```bash
composer create-project laravel/laravel infoma
cd infoma
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run dev
```

### **2. Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=infoma
DB_USERNAME=root
DB_PASSWORD=
```

### **3. Database Setup**
```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### **4. File Permissions**
```bash
chmod -R 755 storage bootstrap/cache
```

---

## 📋 **Development Checklist**

### **Phase 1 - Core Setup**
- [ ] Laravel project setup
- [ ] Database migration & seeding
- [ ] Authentication system
- [ ] Role-based middleware
- [ ] Basic routing

### **Phase 2 - User Features**
- [ ] Homepage design
- [ ] Residence listing & detail
- [ ] Activity listing & detail
- [ ] Search & filter functionality
- [ ] Booking system
- [ ] User dashboard

### **Phase 3 - Provider Features**
- [ ] Provider dashboard
- [ ] CRUD residence
- [ ] CRUD activity
- [ ] Booking management
- [ ] Discount system

### **Phase 4 - Admin Features**
- [ ] Admin dashboard
- [ ] User management
- [ ] Activity monitoring
- [ ] Reports & analytics

### **Phase 5 - Additional Features**
- [ ] Rating & review system
- [ ] Bookmark functionality
- [ ] Notification system
- [ ] File upload optimization
- [ ] Email notifications

### **Phase 6 - Testing & Deployment**
- [ ] Unit testing
- [ ] Feature testing
- [ ] Performance optimization
- [ ] Security audit
- [ ] Production deployment

---

## 🔒 **Security Considerations**

### **File Upload Security:**
- Validate file types (MIME type checking)
- Limit file sizes
- Store files outside web root
- Generate unique filenames
- Scan for malware if possible

### **Data Validation:**
- Use Form Requests for validation
- Sanitize user inputs
- Prevent SQL injection (use Eloquent)
- CSRF protection (Laravel default)
- Rate limiting for API endpoints

### **Authentication:**
- Hash passwords (Laravel default)
- Implement email verification
- Session management
- Remember token security
- Password reset functionality

---

## 📊 **Monitoring & Analytics**

### **User Activity Tracking:**
- Login/logout events
- Page visits
- Booking actions
- Search queries
- Error logs

### **Business Metrics:**
- Total bookings per period
- Revenue tracking
- Popular residences/activities
- User engagement rates
- Conversion rates

---

## 🤝 **API Endpoints (Future)**

```php
// Public endpoints
GET /api/residences
GET /api/activities
GET /api/residences/{id}
GET /api/activities/{id}

// Authenticated endpoints
POST /api/bookings
GET /api/bookings
PATCH /api/bookings/{id}
POST /api/ratings
GET /api/bookmarks
POST /api/bookmarks

// Provider endpoints
GET /api/provider/dashboard
POST /api/provider/residences
PATCH /api/provider/bookings/{id}/approve
PATCH /api/provider/bookings/{id}/reject

// Admin endpoints
GET /api/admin/users
POST /api/admin/users
GET /api/admin/analytics
```

---

## 🎯 **Future Enhancements**

### **Version 2.0 Features:**
- [ ] Real payment gateway integration
- [ ] Mobile app (React Native/Flutter)
- [ ] Push notifications
- [ ] Advanced search with location mapping
- [ ] Multi-language support
- [ ] Social media integration
- [ ] Referral system
- [ ] Advanced analytics dashboard

### **Scalability Improvements:**
- [ ] Redis caching
- [ ] Queue system for heavy tasks
- [ ] CDN for image delivery
- [ ] Database optimization
- [ ] API rate limiting
- [ ] Load balancing configuration

---

## 📞 **Support & Documentation**

### **Development Team Contact:**
- **Developer**: [Your Name]
- **Email**: [Your Email]
- **Project Repository**: [GitHub URL]
- **Documentation**: [Wiki URL]

### **Key Resources:**
- Laravel Documentation: https://laravel.com/docs
- Tailwind CSS: https://tailwindcss.com
- Design Assets: [Figma/Design URL]
- Project Management: [Trello/Asana URL]

---

*Last Updated: [Current Date]*
*Version: 1.0.0*
