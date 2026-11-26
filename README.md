 📦 Asset Tracker - QR Based Asset Management System

A comprehensive, enterprise-level **Digital Asset & Document Tracking System** built with Pure PHP and MySQL. This system replaces traditional Excel-based tracking with a modern, QR-code-driven approach, allowing organizations to track physical assets, documents, and equipment efficiently.

## ✨ Key Features

### 📦 Asset Management
* **Full CRUD:** Add, edit, and track assets with detailed information.
* **QR Code Generation:** Auto-generates QR codes for every new asset.
* **Bulk Import/Export:** Import assets via CSV and export data to Excel.
* **Soft Delete:** Recycle Bin functionality to restore accidentally deleted items.
* **Custom Prefix:** Configure custom asset code prefixes (e.g., `LAP-`, `DOC-`).

### 📷 Scanning & Tracking
* **Camera Scanner:** Scan asset QR codes using any device's camera.
* **History Tracking:** Keep a complete audit trail of asset transfers, checkouts, and status changes.
* **Attachments:** Upload images, invoices, and PDFs directly to asset profiles with file previews.

### 💰 Financial & Maintenance
* **Value Tracking:** Keep track of purchase prices and total asset value.
* **Depreciation:** Auto-calculate the current depreciated value of assets.
* **Maintenance Logs:** Track repair costs, service dates, and set reminders for next service.

### 🛡️ Security & Roles
* **Role-Based Access (RBAC):** Separate dashboards and permissions for `Admin` and `Staff`.
* **Security:** CSRF protection, Login rate limiting, Session hijacking prevention, and Auto-logout on inactivity.
* **Audit Logs:** Track every login, system change, and user action.
* **Password Strength Meter:** Enforce strong passwords.

### 🎨 UI/UX
* **Dark Mode:** Toggle between light and dark themes.
* **Multi-language:** Support for English, Sinhala (සිංහල), and Tamil (தமிழ்).
* **PWA Ready:** Installable as a mobile app on iOS and Android (Progressive Web App).
* **Interactive Charts:** Dashboard visualizes data using Chart.js.

## 🛠️ Tech Stack
* **Backend:** PHP 8+ (Pure PHP / PDO)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
* **Libraries:** Chart.js, HTML5-Qrcode, phpqrcode
* **Server Environment:** Apache (XAMPP/WAMP/LAMP)
