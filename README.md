# Digos City Legal Office Appointment System

A Laravel-based government appointment booking platform with AI-powered service detection and lawyer recommendation for the Digos City Legal Office.

## Features

### Public Booking Flow
- **Narrative-first booking**: Clients describe their legal concern in their own words
- **AI Service Detection**: Uses Google Gemini to automatically detect legal services needed
- **Smart Lawyer Matching**: Weighted scoring algorithm for lawyer recommendations
- **Time-range scheduling**: Based on case complexity estimation
- **Document Checklist**: AI-generated list of required documents

### Admin Dashboard
- Manage lawyer accounts (approve/reject/suspend)
- View and manage appointments
- Client records with timeline history
- Today's queue management with check-in system

### Lawyer Portal
- Personal dashboard with today's schedule
- Appointment management
- Schedule/availability management
- Profile and specialization management

### Staff Portal
- Limited admin access for front-desk operations
- Queue management and check-in

## Technology Stack

- **Backend**: Laravel 12.x
- **Frontend**: Bootstrap 5 + Blade + Vanilla JS (AJAX/Fetch)
- **Database**: MySQL
- **AI Integration**: Google Gemini API
- **Permissions**: Spatie Laravel Permission

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (for asset compilation, optional)

### Setup Steps

1. **Clone the repository**
   ```bash
   cd "d:\Laravel Projects"
   git clone <repository-url> appointmentsystem
   cd appointmentsystem
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=appointmentsystem
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Configure Gemini AI** (optional, has fallback mock):
   ```
   GEMINI_API_KEY=your_api_key_here
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Create storage link**
   ```bash
   php artisan storage:link
   ```

8. **Start development server**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

| Role   | Email                     | Password |
|--------|---------------------------|----------|
| Admin  | admin@digoscity.gov.ph    | password |

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/           # Admin dashboard controllers
│   ├── Auth/            # Authentication controllers
│   ├── Client/          # Public booking controllers
│   └── Lawyer/          # Lawyer portal controllers
├── Mail/                # Email notifications
├── Models/              # Eloquent models
└── Services/
    └── AiService.php    # Gemini AI integration

resources/views/
├── admin/               # Admin views
├── auth/                # Login views
├── client/              # Booking flow views
├── emails/              # Email templates
├── layouts/             # Layout templates
└── lawyer/              # Lawyer portal views
```

## Booking Flow Steps

1. **Privacy Consent** - RA 10173 Data Privacy acknowledgment
2. **Narrative & Info** - Client describes their concern + contact info
3. **AI Analysis** - Service detection and complexity estimation
4. **Lawyer Selection** - Recommended lawyers with explainable scores
5. **Schedule** - Date and time selection based on availability
6. **Review** - Final review before submission
7. **Confirmation** - Reference number and next steps

## Lawyer Recommendation Algorithm

Weighted scoring with 6 factors:
- **Specialization Match (40%)** - How well lawyer's expertise matches detected services
- **Similar Cases (15%)** - Experience with similar case types
- **Availability (15%)** - Open slots in schedule
- **Experience (10%)** - Years of practice
- **Language (10%)** - Client language preference match
- **Workload (10%)** - Current appointment load

## Roles & Permissions

- **Admin**: Full system access
- **Lawyer**: Own appointments, schedule, profile
- **Staff**: Queue management, check-in, limited appointment view
- **Client**: Public booking (no account required)

## API Endpoints

### Booking API (AJAX)
- `POST /book/step/{step}` - Process booking step
- `POST /book/back/{step}` - Go back to previous step
- `POST /book/submit` - Final submission
- `GET /book/time-slots` - Get available time slots

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `GEMINI_API_KEY` | Google Gemini API key | (mock mode if empty) |
| `GEMINI_API_URL` | Gemini API endpoint | generativelanguage.googleapis.com |

## License

This project is proprietary software for the Digos City Legal Office.

## Support

For technical support, contact the IT Department of Digos City.
