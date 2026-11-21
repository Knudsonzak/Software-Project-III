# Software Project III - Employee Management System

## Project Name and Purpose

**Software Project III** is a comprehensive **Employee Management System** built with Laravel. The application is designed to streamline employee information management, leave requests, payroll processing, time tracking, and administrative dashboards.

## Features

- **User Authentication** - Secure login system with email and password authentication
- **Employee Dashboard** - View personal employee information and profile
- **Employee Profile Management** - Update and manage employee details
- **Leave Management** - Request and track leave with different leave types
- **Payroll System** - Process and manage employee salaries and payments
- **Time Logging** - Track employee work hours and time logs
- **Admin Dashboard** - Administrative interface for managing all employees and system data
- **Admin Leave Management** - Approve or reject leave requests from employees
- **Role-Based Access Control** - Different interfaces for employees and administrators

## Technology Stack

- **Backend**: Laravel 12.71.0
- **Frontend**: HTML, CSS, JavaScript, Blade Templating Engine
- **Database**: MySQL
- **PHP Version**: 8.2.12
- **Package Manager**: Composer

## Prerequisites

Before setting up the project, ensure you have the following installed:

- **PHP** >= 8.2
- **Composer** (Latest version)
- **MySQL**
- **Git**
- **Node.js** (for frontend asset compilation)
- **npm** (for managing JavaScript dependencies)

## Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/Knudsonzak/Software-Project-III.git
cd Software-Project-III
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Build Assets
```bash
npm run build
```

### 6. Start the Application
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## How to Use or Test the Application

### Login
1. Navigate to `http://localhost:8000/login`
2. Enter your email and password
3. Click "Sign In"

### Access Employee Features
- After login, employees can view their dashboard at `/dashboard`
- The employee dashboard shows only the signed-in employee's time logs, leave requests, and payroll records. The clock-in/out button is not yet available; recorded breaks are deducted from displayed hours, and overnight sessions are attributed to their clock-in date.
- Payslip dates reflect when payroll records were created; the current payroll schema does not record a payroll period.
- Manage their profile information
- Submit leave requests
- View payroll information
- Log work hours

### Access Admin Features
- Admins can access the admin dashboard
- View and manage all employees
- Approve or reject leave requests
- Manage payroll and payments
- View administrative reports

## Known Limitations

- Social login integration is not yet implemented
- Password reset functionality needs to be completed
- Two-factor authentication is not currently available
- The system currently supports basic role-based access control (employee/admin)
- Automated payroll calculations require manual configuration
- Reporting features are limited to basic views
- Integration with external HR systems is not available
- Mobile responsiveness is in progress


