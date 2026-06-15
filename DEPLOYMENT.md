# Free Hosting Deployment

This project is a PHP + MySQL site. Deploy it to a hosting provider that supports PHP and MySQL, for example InfinityFree or AwardSpace.

## 1. Create Hosting And Database

1. Create a free hosting account.
2. Create a MySQL database in the hosting control panel.
3. Open phpMyAdmin for that database.
4. Import `database.sql`.

If phpMyAdmin rejects the first lines because database creation is not allowed, remove these lines from the SQL before importing:

```sql
CREATE DATABASE IF NOT EXISTS language_learning_platform
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE language_learning_platform;
```

## 2. Configure Database Credentials

Copy `config/local.example.php` to `config/local.php` and replace the placeholders with the database values from the hosting control panel:

```php
<?php

return [
    'APP_ENV' => 'production',
    'DB_HOST' => 'your-mysql-host',
    'DB_PORT' => '3306',
    'DB_NAME' => 'your-database-name',
    'DB_USER' => 'your-database-user',
    'DB_PASS' => 'your-database-password',
    'DB_SOCKET' => '',
];
```

Do not commit or share `config/local.php`.

## 3. Upload Files

Upload the project files to the hosting public web directory, usually `htdocs`, `public_html`, or the provider's file manager root.

Do not upload these local-only files and folders:

- `.git`
- `.DS_Store`
- `logs`
- `node_modules`
- `DP4-2_Grina-Lisova.docx`

## 4. Check The Site

Open the hosted URL and test:

- registration
- login
- profile
- tests
- admin login

If the page is blank, check the hosting error log first. Most deployment errors will be wrong database host/user/password values or a failed SQL import.
