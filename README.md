# 🎓 Internship NOC Portal

A full-featured web application for managing student internship **No Objection Certificate (NOC)** requests — built with **Laravel 12**. It streamlines the multi-level approval workflow between students, faculty in-charge, and higher-level faculty, with automated email notifications and PDF NOC generation.

---

## ✨ Features

- **Role-Based Access Control** — Three roles: Student, Faculty In-Charge, Higher-Level Faculty
- **Internship Application Submission** — Students submit applications with company details and offer letters
- **Multi-Level Approval Workflow** — Faculty reviews → Higher Faculty gives final approval
- **NOC Generation** — Automatic PDF NOC generation on final approval (via DomPDF)
- **Email Notifications** — Automated emails at every workflow stage (submission, review, NOC generation)
- **Secure File Uploads** — Offer letters stored securely with role-based access
- **Responsive Dashboards** — Separate dashboards per role with application tracking

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Frontend | Blade Templates + Tailwind CSS + Vite |
| Database | MySQL |
| PDF | barryvdh/laravel-dompdf |
| Auth Scaffolding | Laravel Breeze |
| Mail | SMTP (Gmail) |

---

## ⚙️ Prerequisites

Make sure the following are installed on your machine before proceeding:

- **PHP** >= 8.2 → [php.net/downloads](https://www.php.net/downloads)
- **Composer** >= 2.x → [getcomposer.org](https://getcomposer.org/)
- **Node.js** >= 18.x + **npm** → [nodejs.org](https://nodejs.org/)
- **MySQL** >= 8.0 (or via XAMPP/WAMP/Laragon)
- A **mail account** with SMTP access (Gmail recommended with App Password)

---

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/manav-git01/NOC-Portal.git
cd NOC-Portal
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Configure Environment

Copy the example environment file and fill in your values:

```bash
cp .env.example .env
```

Then open `.env` and update the following sections:

#### 🗄️ Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=internship_noc
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

#### 📧 Mail (Gmail SMTP)

> **Note:** For Gmail, you must use an **App Password** (not your regular password).
> Generate one at: [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Internship NOC Portal"
```

#### 🔧 App Settings (for local development)

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Create the Database

Create a MySQL database matching the name in your `.env`:

```sql
CREATE DATABASE internship_noc;
```

### 7. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

This will:
- Create all required tables
- Seed the **3 roles**: Student, Faculty In-Charge, Higher-Level Faculty

### 8. Create Storage Symlink

```bash
php artisan storage:link
```

### 9. Build Frontend Assets

```bash
npm run build
```

---

## ▶️ Running the Application

### Development Mode (with hot reload)

```bash
# Terminal 1 — Laravel dev server
php artisan serve

# Terminal 2 — Vite asset watcher
npm run dev
```

App will be available at: **http://localhost:8000**

### Or run both together

```bash
composer run dev
```

---

## 👥 User Roles & Access

| Role | Description |
|------|-------------|
| **Student** | Register, submit internship applications, upload offer letters, track status |
| **Faculty In-Charge** | Review student applications, approve or reject with remarks |
| **Higher-Level Faculty** | Give final approval, generate & download PDF NOC |

> **Assigning roles:** After registering a user, update their role directly in the database or use tinker:
> ```bash
> php artisan tinker
> # Then:
> \App\Models\User::where('email','faculty@example.com')->first()->update(['role_id' => 2]);
> ```

---

## 📁 Project Structure

```
NOC-Portal/
├── app/
│   ├── Http/Controllers/     # Application, NOC, Faculty controllers
│   ├── Models/               # User, Role, InternshipApplication, NOC, Approval
│   └── Mail/                 # Mailable classes for email notifications
├── database/
│   ├── migrations/           # All DB table schemas
│   └── seeders/              # RoleSeeder, TestUsersSeeder
├── resources/
│   ├── views/
│   │   ├── dashboards/       # Student, Faculty, Higher-Faculty dashboards
│   │   ├── student/          # Application create & show views
│   │   ├── faculty/          # Faculty review views
│   │   ├── higher-faculty/   # Final approval views
│   │   ├── pdf/              # NOC PDF template
│   │   └── emails/           # Email blade templates
│   └── css/ & js/            # Frontend assets (compiled by Vite)
├── routes/web.php            # All application routes
├── public/images/            # Logos and signature images
└── docs/                     # Project report and structure docs
```

---

## 📋 Key Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# Re-run migrations (fresh start)
php artisan migrate:fresh --seed

# Process queued jobs (email notifications)
php artisan queue:work

# View application logs
php artisan pail
```

---

## 🔒 Security Notes

- Never commit your `.env` file — it is excluded via `.gitignore`
- Use **App Passwords** for Gmail SMTP, not your account password
- Set `APP_DEBUG=false` in production
- Offer letter files are stored in `storage/app/private/` — not publicly accessible

---

## 📄 License

This project is built for academic purposes under the **MIT License**.

---

## 👨‍💻 Author

**Manav** — [@manav-git01](https://github.com/manav-git01)
