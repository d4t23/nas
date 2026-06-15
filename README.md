# GoCloud

A modern cloud storage and secure file management platform built in PHP using object-oriented principles.

## Features

- User registration and login
- Password reset flow
- File upload, download, rename, delete
- Folder creation and file move
- Secure share links with token validation
- Dashboard with recent files and search
- Profile settings and password change

## Structure

- `app/classes/` - core OOP classes
- `config/` - database configuration
- `public/` - web entry points
- `assets/` - CSS, JavaScript, images
- `storage/uploads/` - uploaded files
- `database.sql` - schema for MySQL

## Setup

1. Create a MySQL database named `gocloud`.
2. Import `database.sql`.
3. Set your database credentials in `config/database.php`.
4. Point your web server to `public/` or access pages via `public/` path.
5. Ensure `storage/uploads/` is writable.
