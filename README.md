     GROUP MEMBERS 
John Kigotho - SCT212-0215/2024
John Njogu - SCT212-0450/2024
Roy Kiptoo - SCT212-0451/2024

# JKUAT GPA & Graduation Planner
## Setup Guide for VS Code + XAMPP

---

### Prerequisites
Install these before starting:
- **XAMPP** (includes Apache + MySQL + PHP): https://www.apachefriends.org
- **VS Code**: https://code.visualstudio.com
- Recommended VS Code extension: **PHP Intelephense** (for PHP autocompletion)

---

### Step-by-step Setup

#### 1. Place the project in XAMPP's web folder
Copy the entire `gpa_planner` folder into:
```
C:\xampp\htdocs\gpa_planner\        (Windows)
/Applications/XAMPP/htdocs/gpa_planner/  (macOS)
```

#### 2. Start XAMPP
Open the XAMPP Control Panel and start:
- **Apache**
- **MySQL**

#### 3. Create the database
- Open your browser and go to: `http://localhost/phpmyadmin`
- Click **"New"** on the left sidebar
- Create a database named `gpa_planner`
- Click the **"Import"** tab
- Upload the file `database.sql` from this project
- Click **Go**

#### 4. Configure database credentials (if needed)
Open `includes/config.php` and update:
```php
define('DB_USER', 'root');    // your MySQL username
define('DB_PASS', '');        // your MySQL password (blank by default in XAMPP)
```

#### 5. Open the project in VS Code
```
File → Open Folder → select the gpa_planner folder
```

#### 6. Access the app
Open your browser and go to:
```
http://localhost/gpa_planner/login.php
```

---

### Project File Structure
```
gpa_planner/
├── database.sql          ← Run this first in phpMyAdmin
├── login.php             ← Login page
├── register.php          ← Student registration
├── dashboard.php         ← Main dashboard (CGPA, charts)
├── logout.php            ← Session logout
│
├── includes/
│   ├── config.php        ← DB credentials, grade scale, honors thresholds
│   ├── db.php            ← Database connection class
│   ├── gpa.php           ← All GPA calculation logic
│   └── auth.php          ← Login/register/session management
│
├── pages/
│   ├── semesters.php     ← Add semesters and enter grades
│   ├── projections.php   ← Required GPA projections per honors class
│   └── profile.php       ← Edit student profile
│
├── css/
│   └── style.css         ← All styles
│
└── js/
    └── semesters.js      ← Dynamic course rows + live GPA preview
```

---

### JKUAT Grading Scale Used
| Grade | Points | Percentage |
|-------|--------|------------|
| A     | 4.0    | 70%+       |
| B     | 3.0    | 60%+       |
| C     | 2.0    | 50%+       |
| D     | 1.0    | 40%+       |
| E     | 0.0    | Below 40%  |

### Honors Classification Thresholds
| Classification      | Minimum CGPA |
|---------------------|--------------|
| First Class         | 3.80         |
| Second Class Upper  | 3.50         |
| Second Class Lower  | 3.00         |
| Pass                | 2.00         |
| Fail                | Below 2.00   |

---

### Tech Stack
- **Backend**: PHP 8+ (no framework)
- **Database**: MySQL via MySQLi (prepared statements)
- **Frontend**: HTML5, CSS3, Vanilla JS
- **Charts**: Chart.js (CDN)
- **Local server**: XAMPP (Apache + PHP + MySQL)

---

### Common Issues
| Problem | Solution |
|---------|----------|
| "Connection refused" | Make sure Apache and MySQL are running in XAMPP |
| "Database not found" | Run `database.sql` in phpMyAdmin |
| Blank white page | Check PHP error log in XAMPP → Apache → Logs |
| CSS not loading | Make sure the folder is inside `htdocs/`, not on the Desktop |
