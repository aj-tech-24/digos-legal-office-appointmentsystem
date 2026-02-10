# System Installation Guide

This guide provides step-by-step instructions to set up and run the Appointment System on your local machine.

## Prerequisites

Before starting, ensure you have the following installed on your system:

*   **PHP** (version 8.2 or higher)
*   **Composer** (Dependency Manager for PHP)
*   **Node.js & NPM** (For frontend assets)
*   **Database** (MySQL or SQLite)

## Installation Steps

### 1. Clone or Download the Repository

If you haven't already, download the project source code to your local machine.

### 2. Install Backend Dependencies

Navigate to the project directory in your terminal and run the following command to install the necessary PHP packages:

```bash
composer install
```

### 3. Install Frontend Dependencies

Install the JavaScript dependencies required for the user interface:

```bash
npm install
```

### 4. Environment Configuration

1.  duplicate the `.env.example` file and rename it to `.env`:

    ```bash
    cp .env.example .env
    ```
    *(On Windows, you can just manually copy and rename the file)*

2.  Generate the application encryption key:

    ```bash
    php artisan key:generate
    ```

3.  Open the `.env` file and configure your database settings. For example, if using MySQL:

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=appointmentsystem
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    *Make sure to create the database (e.g., `appointmentsystem`) in your database management tool before proceeding.*

    *If using SQLite, simply set `DB_CONNECTION=sqlite` and create a `database.sqlite` file in the `database` folder.*

### 5. Database Setup

Run the migrations to create the database tables and seed them with initial data (roles, users, etc.):

```bash
php artisan migrate --seed
```
*Note: The `--seed` flag runs the `DatabaseSeeder`, which sets up essential roles, permissions, and default users.*

### 6. Build Frontend Assets

Compile the CSS and JavaScript assets using Vite:

```bash
npm run build
```
*(For development, you can use `npm run dev` to watch for changes)*

### 7. Run the Application

Start the local development server:

```bash
php artisan serve
```

The application should now be accessible at [http://localhost:8000](http://localhost:8000).

---

## Default Login Credentials

The system comes pre-configured with the following user accounts for testing:

### Admin Account
*   **Email:** `admin@digoscity.gov.ph`
*   **Password:** `password`
*   **Role:** Full system access

### Staff Account
*   **Email:** `staff@digoscity.gov.ph`
*   **Password:** `password`
*   **Role:** Manage queue and check-ins

### Lawyer Accounts
*   **Email:** `maria.santos@digoscity.gov.ph`
*   **Password:** `password`
*   **Role:** Consultation and schedule management

*(Other seeded lawyers: `juan.delacruz@digoscity.gov.ph`, `rosa.reyes@digoscity.gov.ph`, `pedro.garcia@digoscity.gov.ph` - all with password `password`)*
