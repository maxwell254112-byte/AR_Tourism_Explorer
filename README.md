# AR Tourism Explorer

A web-based Augmented Reality tourism and cultural heritage information system. Visitors open the site on a smartphone, allow camera access, and scan a prepared tourism poster. Administrators manage destinations, attractions, posters and hotspots from a browser — no native app is required.

This is a generic platform. Destination names, posters, videos and map coordinates are configurable. The demo data is fictional sample content, not a copy of a city-specific product.

## Features

- Public tourism website: home, destinations, attractions, about, contact, search
- Visitor AR experience using MindAR image tracking and Three.js
- Administrator dashboard with destinations, attractions, categories, posters, users, activity logs and settings
- Visual AR poster editor with draggable, resizable hotspots stored as normalized coordinates
- In-browser MindAR target compilation, plus `.mind` file upload
- YouTube video extraction (`watch`, `youtu.be`, `embed`)
- Google Maps links from stored URLs or latitude/longitude
- Attraction image galleries with a simple lightbox
- CSRF protection, password hashing, PDO prepared statements, upload validation, login throttling
- HTTPS warning for smartphone camera access

Visitors scan one system QR code to open the AR page. Attraction cards show photos only. After the camera starts, visitors scan a printed or on-screen photo to watch the video.

## Penang AR photos

These three photos are the AR scan targets. Print them or open them on another screen, then point the phone camera at the photo (not a QR code).

| Attraction | Photo file | YouTube |
| --- | --- | --- |
| Penang Hill | `assets/images/penang/penang-hill.jpg` | https://www.youtube.com/watch?v=XPTGsusPL7M |
| Kek Lok Si Temple | `assets/images/penang/kek-lok-si.jpg` | https://www.youtube.com/watch?v=TMK2Zpml6eM |
| George Town Street Art | `assets/images/penang/georgetown-street-art.jpg` | https://www.youtube.com/watch?v=Q-uf2CiJNvg |

Compiled MindAR target (all three photos in one set):

- `assets/ar-targets/penang-set.mind`

Demo destination cover photos (Coastal, Food, Harbour, etc.):

- `assets/images/demo/*.jpg`

Print page: open `photos.php` on the site.

If attraction cards show `???` instead of text, import `database/fix-photo-text.sql` in phpMyAdmin (broken Chinese encoding on the server).

## Technology stack

- PHP 8.3+
- MySQL 8+
- PDO
- HTML5, CSS3, Vanilla JavaScript
- Bootstrap 5, Font Awesome, Google Fonts
- MindAR Image Tracking and Three.js

Do not use Laravel, CodeIgniter, Symfony, React, Vue, Angular, Node.js as the main backend, or Firebase as the main database.

## Requirements

- PHP 8.3+
- MySQL 8+
- Apache (XAMPP) or PHP’s built-in server
- A modern browser with camera support
- HTTPS for camera access on a real smartphone (`localhost` is allowed for development)

## Installation

1. Copy the project folder into XAMPP `htdocs` (or your cPanel `public_html` folder).
2. Create a MySQL database, or let `database/database.sql` create `ar_tourism`.
3. Import `database/database.sql` in phpMyAdmin or the MySQL client.
4. Edit `includes/config.php` and set `DB_HOST`, `DB_NAME`, `DB_USER` and `DB_PASS`.
5. Start Apache and MySQL.
6. Open the site, for example `http://localhost/ar-tourism/` or `http://localhost:8000/`.
7. Sign in at `admin/login.php`.

Default administrator:

- Username: `admin`
- Password: `admin123`

Change this password before any public deployment.

## Database setup

```sql
SOURCE database/database.sql;
```

The SQL file creates tables for users, categories, destinations, attractions, attraction images, AR posters, AR hotspots, settings, activity logs and login attempts. Foreign keys and indexes are included. Demo destinations and attractions are inserted automatically.

## XAMPP setup

1. Place the folder in `C:\xampp\htdocs\`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Import the SQL file through phpMyAdmin.
4. Confirm `assets/uploads` and `assets/ar-targets` are writable by Apache.

## HTTPS setup

Smartphone browsers generally refuse camera access on plain HTTP.

- Local development: use `http://localhost` or `http://127.0.0.1`.
- LAN phone testing: use HTTPS, or a tunnel that provides HTTPS.
- Production: enable SSL on cPanel / AutoSSL / Let’s Encrypt.

The administrator dashboard and AR page show a warning when the current URL is not a secure context.

## AR setup

1. Sign in as administrator.
2. Create destinations and attractions first.
3. Open **AR Posters** and upload a printed tourism poster image.
4. Open the visual editor, click the poster to add hotspots, assign attractions, then save.
5. Compile the MindAR target (or upload a `.mind` file).
6. When the target status is `READY`, open **AR Experience** on a phone and scan the printed poster.

Recommended poster images: high-contrast, detailed photographs. Very simple graphics track poorly.

## MindAR target compilation

The compiler runs in the administrator’s browser:

```text
poster.jpg
    → MindAR Compiler
    → poster.mind
```

Progress is shown on the compilation page. The generated file is stored under `assets/ar-targets/` and linked to that poster. Replacing the poster image sets `target_status` back to `NOT_COMPILED`.

If the in-browser compiler is unavailable, compile the image with the official MindAR compiler and upload the `.mind` file on the same page.

PHP cannot compile MindAR targets by itself. Pretending otherwise would be unreliable.

## Admin usage

Sidebar:

- Dashboard
- Destinations, Attractions, Categories
- AR Posters, Target Compilation
- Users, Activity Logs, Settings
- Logout

Confirm dialogs appear before deletions. Searches and pagination are available on list pages.

## Visitor usage

1. Scan the system QR on the home page or `qr.php`.
2. The phone opens `ar.php`.
3. Tap **START AR** and allow camera access.
4. Point the rear camera at a Penang attraction photo, not a QR code.
5. When the photo is detected, the YouTube video starts.

Videos start muted when the browser requires it for autoplay.

## File upload requirements

- Images only: JPG, PNG, WEBP, GIF
- Maximum 8 MB
- MIME type and `getimagesize()` are checked
- Stored names are generated; original filenames are not trusted
- Upload directories block PHP execution with `.htaccess`

## Troubleshooting

| Problem | What to check |
| --- | --- |
| Database error | Credentials in `includes/config.php` and that the SQL file was imported |
| Cannot log in | Default password `admin123`; after 5 failures wait 10 minutes. Or open `install/reset-admin.php` once, then delete it |
| Cards show `???` | Import `database/fix-photo-text.sql` (UTF-8 encoding) |
| Camera denied | Browser permission, HTTPS, and that another app is not using the camera |
| Poster not detected | Lighting, full poster in view, and that the compiled target matches the printed image |
| Target missing | Compile or upload a `.mind` file until status is `READY` |
| No attractions in AR | Save hotspots and assign attractions in the poster editor |
| YouTube unavailable | Confirm the stored URL contains a valid 11-character video ID |

## Deployment to cPanel

1. Upload the project into `public_html` (or a subfolder), including `assets/images/penang/` and `assets/ar-targets/penang-set.mind`.
2. Create a MySQL database and user in cPanel.
3. Import `database/database-cpanel.sql` (or `database/database.sql` for local).
4. Update `includes/config.php`.
5. Enable SSL.
6. Make `assets/uploads` and `assets/ar-targets` writable.
7. Change the default admin password.
8. Confirm AR works: open `ar.php` → **START AR** → scan a Penang photo.

## Project structure

```text
ar-tourism/
├── index.php
├── ar.php
├── qr.php
├── photos.php
├── destinations.php
├── destination.php
├── attractions.php
├── attraction.php
├── about.php
├── contact.php
├── admin/
├── api/ar/
├── assets/css|js|images|uploads|ar-targets
├── includes/
├── database/database.sql
└── README.md
```

## Known browser limits

AR image tracking quality depends on the device, lighting and poster design. Autoplay with sound is blocked by most mobile browsers, so videos start muted. CSS3D HTML attached to the tracked poster is possible, but a screen-space information card is used because it remains readable and tappable on phones.
