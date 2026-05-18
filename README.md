# 🎙️ PodWave — Professional Podcast Hosting & Listening Platform

**PodWave** is a full-featured, production-grade podcast platform built with Laravel 11, offering a Spotify-like dark UI for creators and listeners.

---

## 🚀 Features

### 👤 User Roles
- **Admin** — Full platform control, analytics, moderation
- **Podcast Creator** — Upload and manage podcasts and episodes
- **Listener** — Browse, play, like, subscribe, and comment

### 🎧 Listener Features
- Built-in audio player with progress saving
- Like, favorite, and subscribe to creators
- Comment on episodes
- Filter by category, genre, popularity
- Trending & recommended podcasts
- Listening history
- AJAX live search

### 🎙️ Creator Features
- Create podcast channels with thumbnails
- Upload audio episodes with metadata
- Draft/Publish workflow
- Episode analytics (plays, likes, subscribers)
- Tag management

### 🛡️ Admin Features
- Dashboard with platform statistics
- User/Creator management (ban/unban)
- Content moderation
- Category & genre management
- System-wide reports

---

## ⚙️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.2+) |
| Database | MySQL 8.0+ |
| Frontend | Blade + Bootstrap 5 + Custom CSS |
| Auth | Laravel Breeze / built-in Auth |
| Storage | Local disk (configurable to S3) |
| Queue | Database driver |

---

## 📦 Installation Guide

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL 8.0+
- Node.js >= 18
- NPM

### Step 1: Clone / Setup
```bash
git clone https://github.com/yourname/podwave.git
cd podwave
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Configure `.env`
```env
APP_NAME="PodWave"
APP_URL=http://localhost:8000
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=podwave
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
MAIL_FROM_ADDRESS="hello@podwave.fm"
MAIL_FROM_NAME="PodWave"

FILESYSTEM_DISK=public
```

### Step 5: Create Database
```sql
CREATE DATABASE podwave CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 6: Run Migrations & Seeders
```bash
php artisan migrate --seed
```

### Step 7: Storage Link
```bash
php artisan storage:link
```

### Step 8: Build Assets
```bash
npm run build
# or for development:
npm run dev
```

### Step 9: Run the App
```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## 🧪 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@podwave.fm | password |
| Creator | creator@podwave.fm | password |
| Listener | listener@podwave.fm | password |

---

## 📁 Folder Structure

```
podwave/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin panel controllers
│   │   │   ├── Auth/           # Authentication controllers
│   │   │   ├── Creator/        # Creator dashboard controllers
│   │   │   └── Listener/       # Listener feature controllers
│   │   ├── Middleware/         # Auth, Role, Ban middleware
│   │   └── Requests/           # Form validation requests
│   ├── Models/                 # Eloquent models
│   └── Providers/              # Service providers
├── database/
│   ├── factories/              # Model factories for seeding
│   ├── migrations/             # Database schema
│   └── seeders/                # Seeder classes
├── public/
│   ├── css/                    # Compiled CSS
│   └── js/                     # Compiled JS
├── resources/
│   ├── css/                    # Source CSS / Tailwind
│   ├── js/                     # Source JS
│   └── views/
│       ├── admin/              # Admin panel views
│       ├── auth/               # Login/register views
│       ├── creator/            # Creator dashboard views
│       ├── layouts/            # Master layout templates
│       └── listener/          # Listener/browse views
├── routes/
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
└── storage/
    └── app/public/
        ├── avatars/            # User avatars
        ├── thumbnails/         # Podcast/episode thumbnails
        └── audio/              # Podcast audio files
```

---

## 🗃️ Database ER Diagram Description

```
users (id, name, email, password, role, avatar, bio, is_banned)
  |
  |-- podcasts (id, user_id[creator], category_id, genre_id, title, description, thumbnail, tags, status)
  |     |
  |     |-- episodes (id, podcast_id, title, description, audio_file, thumbnail, duration, plays, status, release_date)
  |           |
  |           |-- comments (id, episode_id, user_id, body, parent_id)
  |           |-- likes (id, user_id, likeable_type, likeable_id) [polymorphic]
  |           |-- listening_history (id, user_id, episode_id, progress_seconds, listened_at)
  |
  |-- subscriptions (id, subscriber_id, creator_id)
  |-- favorites (id, user_id, podcast_id)
  |-- notifications (id, user_id, type, data, read_at)

categories (id, name, slug, icon, color)
genres (id, name, slug)
```

---

## 🧩 Module Descriptions

| Module | Description |
|--------|-------------|
| **Auth** | Registration, Login, Forgot Password, Email Verification |
| **Roles** | Middleware-based role guard (admin/creator/listener) |
| **Podcast** | CRUD for channels; thumbnail & audio uploads |
| **Episode** | Upload audio, set metadata, draft/publish |
| **Player** | HTML5 audio player with progress saving via AJAX |
| **Search** | Live AJAX search across podcasts and episodes |
| **Subscriptions** | Creator subscriber management |
| **Likes/Favorites** | Polymorphic like system; favorite podcasts |
| **Comments** | Threaded comments on episodes |
| **History** | Auto-saved listening history per user |
| **Trending** | Sorted by play count in the last 7 days |
| **Admin Panel** | Full CRUD + ban system + analytics dashboard |
| **Notifications** | DB-driven notification system |
| **Recommendations** | Simple logic: same category + subscribed creators |

---

## 📸 Pages Overview

1. **/** — Hero homepage with trending, featured, categories
2. **/about** — About PodWave page
3. **/contact** — Contact form
4. **/browse** — Browse all podcasts with filters
5. **/podcasts/{id}** — Podcast detail with episode list
6. **/episodes/{id}** — Episode detail with player & comments
7. **/creators/{id}** — Creator public profile
8. **/categories** — All categories grid
9. **/search** — Search results
10. **/register, /login** — Auth pages
11. **/dashboard** — Role-based dashboard redirect
12. **/creator/\*** — Creator management panel
13. **/admin/\*** — Admin panel

---

## 🧪 Testing

```bash
php artisan test
```

---

## 📝 License

MIT — Free to use for educational and commercial projects.
