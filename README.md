# SIADO Backend - Sistem Informasi Dosen (Backend API)

SIADO Backend is a robust Laravel-based API that powers the SIADO academic management system. It provides comprehensive endpoints for course management, user authentication, assignment tracking, and student evaluation, designed to work with the separate React frontend.

## ✨ Features

- **Role-based access control** for administrators, lecturers, and students
- **RESTful API architecture** with comprehensive endpoints
- **JWT authentication** with secure token management
- **Course management** with enrollment tracking and material organization
- **Assignment system** with submission handling and grading
- **User management** for administrators
- **Comprehensive data validation** and error handling

## 🛠️ Tech Stack

- **Backend**: Laravel 12 with PHP 8.2+
- **Database**: MySQL/PostgreSQL
- **Authentication**: JWT (tymon/jwt-auth ^2.2)
- **Testing**: Pest PHP
- **Development Tools**: Laravel Sail, Laravel Pint, Laravel Pail

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL or PostgreSQL
- Frontend application running on `http://localhost:3000`

## 🚀 Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/AiFahri/siado-clone.git
cd siado-clone
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` to configure your database connection and JWT settings.

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Development Server

```bash
php artisan serve
```

## 🗂️ Project Structure

```
app/
├── Http/
│   ├── Controllers/     # API controllers
│   └── Middleware/      # Request middleware
├── Models/              # Eloquent models
├── Services/            # Business logic services
└── Exceptions/          # Exception handlers
routes/
├── api.php             # API route definitions
└── web.php             # Web route definitions
database/
├── migrations/         # Database migrations
└── seeders/            # Database seeders
```

## 🔐 Authentication & Role Management

SIADO implements JWT-based authentication with role-based access control:

- **Admin**: Full system access - user management, course management
- **Lecturer**: Course teaching - assignment creation, grading, material management
- **Student**: Course enrollment - assignment submission, material access

## 📡 API Endpoints

### Authentication
- `POST /api/auth/signup` - User registration
- `POST /api/auth/signin` - User login

### User Management
- `GET /api/users/_self` - Get current user profile
- `GET /api/users/_self/courses` - Get user's enrolled courses
- `GET /api/users/_self/assignments` - Get user's assignments
- `GET /api/users/_self/submissions` - Get user's submissions

### Course Management
- `GET /api/courses` - List all available courses
- `GET /api/courses/{course}` - Get course details
- `POST /api/courses/{course}` - Enroll in a course
- `DELETE /api/courses/{course}` - Unenroll from a course

### Lecturer & Course Management
- `POST /api/courses/{course}/lecturers/{lecturer}` - Assign lecturer to course
- `GET /api/courses/{course}/lecturers` - List lecturers for a course
- `DELETE /api/courses/{course}/lecturers/{lecturer}` - Remove lecturer from course
- `GET /api/courses/{course}/students` - Get students enrolled in a course

### Assignment Management (Lecturer)
- `POST /api/lecturer/courses/{course}/assignments` - Create assignment
- `GET /api/lecturer/courses/{course}/assignments` - List course assignments
- `GET /api/lecturer/courses/{course}/assignments/{assignment}` - Get assignment details
- `PATCH /api/lecturer/courses/{course}/assignments/{assignment}` - Update assignment
- `DELETE /api/lecturer/courses/{course}/assignments/{assignment}` - Delete assignment
- `GET /api/lecturer/assignments/{assignment}/submissions` - List submissions for an assignment
- `POST /api/lecturer/submissions/{submission}/grade` - Grade a submission

### Material Management (Lecturer)
- `GET /api/lecturer/courses/{course}/materials` - List course materials
- `POST /api/lecturer/courses/{course}/materials` - Create course material
- `GET /api/lecturer/courses/{course}/materials/{material}` - Get material details
- `PATCH /api/lecturer/courses/{course}/materials/{material}` - Update material
- `DELETE /api/lecturer/courses/{course}/materials/{material}` - Delete material

### Student Access
- `GET /api/courses/{course}/assignments` - Get assignments for enrolled course
- `GET /api/courses/{course}/assignments/{assignment}` - Get assignment details
- `GET /api/courses/{course}/materials` - Get materials for enrolled course

### Admin Management
- `GET /api/admin/users` - List all users
- `POST /api/admin/users` - Create new user
- `PATCH /api/admin/users/{user}` - Update user
- `DELETE /api/admin/users/{user}` - Delete user
- `POST /api/admin/courses` - Create new course
- `PATCH /api/admin/courses/{course}` - Update course
- `DELETE /api/admin/courses/{course}` - Delete course
- `GET /api/admin/courses/{course}/students` - List students in course
- `POST /api/admin/courses/{course}/students/{student}` - Add student to course
- `DELETE /api/admin/courses/{course}/students/{student}` - Remove student from course
- `GET /api/admin/stats` - Get admin statistics

### Lecturer Statistics
- `GET /api/lecturer/stats` - Get lecturer statistics

## 🔄 Frontend Integration

This backend is designed to work with two frontend applications:

### 1. [SIADO Frontend](https://github.com/AiFahri/siado-clone-fe)
The primary frontend application built with React and TypeScript, focused on administrator and lecturer interfaces. It provides comprehensive dashboards, course management tools, and assignment grading capabilities.

### 2. [StudentClub](https://github.com/lidwinae/studentclub)
A student-focused web application built with Vue.js. StudentClub provides a clean, responsive interface for students to efficiently manage their academic activities, access course information, track assignments, and submit their work.

Both frontends communicate with this API via HTTP requests, providing a complete academic management ecosystem for all user roles.