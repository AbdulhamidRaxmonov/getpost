# YesPOS - To'liq Savdo Boshqaruv Tizimi

Uzbekistondagi **YesPOS** tizimining to'liq kloni. Flutter Desktop POS dasturi va Laravel backend bilan.

---

## 📁 Loyiha tuzilmasi

```
getpost/
├── backend/          # Laravel 11 - Backend API + Admin Panel
│   ├── app/
│   │   ├── Models/           # Barcha modellar
│   │   ├── Http/Controllers/
│   │   │   ├── Api/          # REST API Controllers
│   │   │   └── Admin/
│   │   │       ├── SuperAdmin/   # Super Admin Panel
│   │   │       └── Merchant/     # Tadbirkor Admin Panel
│   ├── database/migrations/  # Ma'lumotlar bazasi migratsiyalari
│   ├── routes/
│   │   ├── api.php           # API routes
│   │   └── web.php           # Web (admin panel) routes
│   └── resources/views/admin/  # Blade templates (TailwindCSS + Alpine.js)
│
└── pos_desktop/      # Flutter Desktop POS dasturi
    └── lib/
        ├── core/
        │   ├── theme/         # AppTheme
        │   ├── providers/     # Riverpod providers
        │   ├── services/      # ApiService, AuthService, StorageService
        │   └── router/        # GoRouter
        └── features/
            ├── auth/          # PIN login, Terminal setup
            ├── pos/           # Asosiy POS ekrani
            │   ├── models/    # ProductModel, CartItem, CustomerModel
            │   ├── providers/ # CartNotifier, productsProvider
            │   ├── screens/   # PosScreen
            │   └── widgets/   # CartPanel, ProductGrid, CategoryBar
            ├── payment/       # To'lov ekrani (7 xil usul)
            ├── receipt/       # Chek ekrani + PDF chop etish
            └── shift/         # Smena ochish/yopish/hisobot
```

---

## 🚀 O'rnatish va ishga tushirish

### Laravel Backend

```bash
cd backend

# 1. Paketlarni o'rnatish
composer install

# 2. .env faylini yaratish
cp .env.example .env
php artisan key:generate

# 3. Ma'lumotlar bazasini sozlash (.env da DB_* ni o'zgartiring)
php artisan migrate --seed

# 4. Serverni ishga tushirish
php artisan serve
```

### Flutter Desktop POS

```bash
cd pos_desktop

# 1. Paketlarni o'rnatish
flutter pub get

# 2. Windows uchun build
flutter run -d windows

# 3. Linux uchun build
flutter run -d linux

# 4. macOS uchun build
flutter run -d macos
```

---

## 👤 Demo hisoblar

| Role | Telefon | Parol | PIN |
|------|---------|-------|-----|
| Super Admin | +998901234567 | admin123 | — |
| Org Admin | +998990001100 | demo123 | 1234 |
| Kassir | +998990001101 | demo123 | 5678 |

---

## 🔑 Tizim imkoniyatlari

### Super Admin Panel (`/super/dashboard`)
- ✅ Barcha tashkilotlarni boshqarish
- ✅ Yangi tashkilot + admin yaratish
- ✅ Obuna rejalarini boshqarish (Basic, Pro, Enterprise)
- ✅ Umumiy statistika va hisobotlar
- ✅ Foydalanuvchilar ro'yxati

### Merchant (Tadbirkor) Admin Panel (`/merchant/dashboard`)
- ✅ Dashboard - kunlik/oylik statistika
- ✅ Mahsulotlar boshqaruvi (CRUD + shtrix kod)
- ✅ Kategoriyalar
- ✅ Ombor holati va tartibga solish
- ✅ Kirim (yetkazib beruvchilardan)
- ✅ Barcha savdolar ro'yxati
- ✅ Mijozlar bazasi
- ✅ Hodimlar va PIN kodlar
- ✅ Filiallar va terminallar
- ✅ Hisobotlar: sotuv, mahsulotlar, smenalar, kassirlar

### Flutter Desktop POS
- ✅ PIN kod bilan kirish (rasmlaridagidek)
- ✅ Terminal sozlamasi
- ✅ Smena ochish (boshlang'ich saldo bilan)
- ✅ Asosiy savdo ekrani - ikki ustunli (mahsulot jadvali + savat)
- ✅ Mahsulot qidirish (nom, SKU, barcode)
- ✅ Kategoriya bo'yicha filterlash
- ✅ Savatda miqdor/narx tahrirlash
- ✅ **7 xil to'lov usuli:** Naqd, Plastik, Click, Payme, Humo, Uzcard, Qarz
- ✅ Tezkor klaviatura shortcut'lar (F6-Naqd, F7-Plastik, F8-Bo'lish, F9-Humo)
- ✅ PDF chek chop etish
- ✅ Smena hisoboti (savdo turlari, kassa muvozanati)
- ✅ Bir vaqtda bir nechta chek (multi-tab)
- ✅ Ombor qoldiqlarini ko'rsatish

---

## 🛠️ Texnologiyalar

### Backend
- **Laravel 11** - PHP Framework
- **MySQL** - Ma'lumotlar bazasi
- **Laravel Sanctum** - API autentifikatsiya (token + PIN)
- **TailwindCSS + Alpine.js** - Admin panel UI (CDN orqali)
- **Chart.js** - Grafiklar

### Flutter Desktop
- **Flutter 3.x** - Cross-platform framework
- **Riverpod 2** - State management
- **GoRouter** - Navigation
- **Dio** - HTTP client
- **window_manager** - Desktop window management
- **printing + pdf** - Chek chop etish
- **shared_preferences** - Local storage
- **Google Fonts (Inter)** - Typografiya

---

## 📋 API Endpoints (asosiy)

```
POST /api/auth/login          - Telefon/parol bilan kirish
POST /api/auth/pin-login      - PIN bilan kirish (POS uchun)
POST /api/auth/logout         - Chiqish

GET  /api/products            - Mahsulotlar ro'yxati
GET  /api/products/barcode    - Barcode bo'yicha qidirish
GET  /api/categories          - Kategoriyalar

POST /api/shifts/open         - Smenani ochish
POST /api/shifts/{id}/close   - Smenani yopish
GET  /api/shifts/current      - Joriy smena
GET  /api/shifts/{id}/report  - Smena hisoboti

POST /api/orders              - Yangi savdo yaratish
GET  /api/orders/{id}/receipt - Chekni olish
POST /api/orders/{id}/return  - Qaytarish

GET  /api/reports/dashboard   - Dashboard statistika
GET  /api/reports/daily-sales - Kunlik sotuv
```

---

## 📞 Qo'llab-quvvatlash

**YesPOS** - Uzbekiston uchun zamonaviy savdo boshqaruv tizimi

Call-markaz: +998 (00) 123-45-67
