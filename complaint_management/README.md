# ComplainX — Complaint Management System
### BCA Final Year Project | PHP + MySQL + Bootstrap 5 + Chart.js

---

## 📁 Project Structure

```
complaint_management/
├── index.php                   ← Login Page
├── register.php                ← Student Registration
├── logout.php                  ← Logout
│
├── config/
│   └── db.php                  ← Database Connection (edit this first!)
│
├── includes/
│   ├── functions.php           ← Helper functions (session, badges, etc.)
│   ├── sidebar_user.php        ← Student sidebar nav
│   └── sidebar_admin.php       ← Admin/Agent sidebar nav
│
├── user/
│   ├── dashboard.php           ← Student dashboard with stats + tracker
│   ├── submit_complaint.php    ← Submit new complaint with file upload
│   ├── my_complaints.php       ← View + filter all complaints
│   └── view_complaint.php      ← View single complaint + add remarks
│
├── admin/
│   ├── dashboard.php           ← Admin overview dashboard
│   ├── manage_complaints.php   ← All complaints table with inline update
│   ├── view_complaint.php      ← Full complaint management + remarks
│   ├── reports.php             ← Charts & analytics (Chart.js)
│   └── ajax_update.php         ← AJAX endpoint for inline status/agent update
│
├── assets/
│   ├── css/style.css           ← All custom CSS (Playfair + DM Sans fonts)
│   └── js/main.js              ← JavaScript (sidebar, AJAX, charts, toasts)
│
├── uploads/                    ← Uploaded complaint attachments (auto-created)
│
└── database/
    └── complaint_db.sql        ← Full database schema + seed data
```

---

## 🚀 Setup Instructions (XAMPP)

### Step 1 — Install & Start XAMPP
1. Download XAMPP from https://www.apachefriends.org
2. Install and open the XAMPP Control Panel
3. Click **Start** on **Apache** and **MySQL**
4. Both should show green "Running" status

### Step 2 — Copy Project Folder
1. Copy the entire `complaint_management` folder
2. Paste it inside: `C:\xampp\htdocs\`
3. Final path should be: `C:\xampp\htdocs\complaint_management\`

### Step 3 — Import the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** in the left panel
3. Type database name: `complaint_db` → Click **Create**
4. Click on `complaint_db` in the left panel
5. Click the **Import** tab at the top
6. Click **Choose File** → Select `database/complaint_db.sql`
7. Scroll down → Click **Import**
8. You should see "Import has been successfully finished"

### Step 4 — Configure Database (if needed)
Open `config/db.php` and update if your MySQL has a password:
```php
define('DB_USER', 'root');   // usually 'root' in XAMPP
define('DB_PASS', '');       // leave empty for default XAMPP
```

### Step 5 — Run the Project
Open browser → go to: `http://localhost/complaint_management/`

---

## 🔐 Login Credentials (from seed data)

| Role    | Email                     | Password  |
|---------|---------------------------|-----------|
| Student | arjun@student.com         | user123   |
| Admin   | admin@complainx.com       | password  |
| Agent   | ravi@complainx.com        | agent123  |
| Agent   | priya@complainx.com       | agent123  |

---

## 🛠️ Technologies Used

| Layer         | Technology                        |
|---------------|-----------------------------------|
| Frontend      | HTML5, CSS3, Bootstrap 5          |
| Fonts         | Playfair Display, DM Sans (Google)|
| Icons         | Font Awesome 6                    |
| Charts        | Chart.js 4.4                      |
| Backend       | PHP 8                             |
| Database      | MySQL                             |
| Local Server  | XAMPP (Apache + MySQL)            |
| File Uploads  | PHP move_uploaded_file()          |
| AJAX          | Vanilla JS fetch() API            |

---

## ✅ Features

**Student Panel:**
- Register / Login
- Submit complaint with file attachment
- Filter complaints by status
- View detailed complaint with progress tracker
- Add remarks/questions to any complaint

**Admin / Agent Panel:**
- Dashboard with live stats
- Manage all complaints in one table
- Inline status & agent update (AJAX — no page reload)
- Add staff remarks / internal notes
- Reports with doughnut, bar & line charts
- Category and priority breakdown

---

## 📝 Notes for Viva / Presentation

- The `uploads/` folder must have write permissions (XAMPP gives this by default)
- Passwords are hashed using PHP `password_hash()` — mention this in viva
- AJAX is used for inline updates in the admin panel — good talking point
- Chart.js is loaded from CDN — requires internet for charts
- All inputs are sanitized using `real_escape_string` and `htmlspecialchars`
- Session-based authentication with role-based redirects
