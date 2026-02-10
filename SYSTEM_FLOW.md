# System Flow Documentation

## 1. User Roles & Responsibilities

The system serves four distinct user types, each with specific permissions and workflows:

| Role | Key Responsibilities | Access Level |
| :--- | :--- | :--- |
| **Client** | Book appointments, view status, receive notifications. | Public (Booking Page) |
| **Staff** | Manage the queue, check-in clients, confirm pending appointments. | Restricted Admin Panel |
| **Lawyer** | Conduct consultations, manage schedule, add case notes. | Restricted Admin Panel |
| **Admin** | Full system control: Manage staff/lawyers, view analytics, system settings. | Full Access |

---

## 2. Appointment Lifecycle

The core of the system is the appointment workflow. An appointment moves through several statuses:

`Pending` → `Confirmed` → `Checked In` → `In Progress` → `Completed`

### A. Booking Phase (Client Side)
1.  **Step 1: Application Type**
    *   Client selects the type of legal service (e.g., Legal Advice, Notary).
    *   System filters available lawyers based on specialization.
2.  **Step 2: Date & Time**
    *   Client selects a date.
    *   System checks `LawyerSchedule` and existing `Appointments` to show available slots.
3.  **Step 3: Personal Info**
    *   Client enters name, email, phone, and address.
    *   *Note: If email matches an existing record, the system links it to that `ClientRecord`.*
4.  **Step 4: Review**
    *   Client reviews details.
5.  **Step 5: Submission**
    *   Appointment is created with status **`Pending`**.
    *   Email notification is sent to the client.

### B. Pre-Consultation Phase (Staff/Admin)
1.  **Review & Confirm**
    *   Staff views "Pending Appointments".
    *   Staff verifies details and clicks **Confirm**.
    *   Status changes to **`Confirmed`**.
    *   *Optional: System sends confirmation email.*
2.  **Check-In (Day of Appointment)**
    *   Client arrives at the office.
    *   Staff locates the appointment in "Today's Queue" or lists.
    *   Staff clicks **Check In**.
    *   Status remains `Confirmed` (or internal flag set), but a **Queue Number** is assigned.
    *   Appointment appears on the "Waiting" list in the Queue Dashboard.

### C. Consultation Phase (Lawyer/Admin)
1.  **Start Consultation**
    *   Lawyer (or Staff) calls the queue number.
    *   Action: Click **Start Appointment**.
    *   Status changes to **`In Progress`**.
2.  **During Consultation**
    *   Lawyer views client details and history.
    *   Lawyer adds **Case Notes** or uploads documents.
3.  **Completion**
    *   Lawyer finishes the session.
    *   Action: Click **Complete Appointment**.
    *   Status changes to **`Completed`**.

### D. Exceptions
*   **Cancellation**: Can be triggered by Client (via link) or Staff/Admin. Status: `cancelled`.
*   **No-Show**: If client doesn't check in by a certain time (manual action). Status: `no_show`.

---

## 3. Client Record Management

The system maintains a permanent record for every individual who uses the service.

*   **Creation**: Automatically created upon first booking.
*   **Timeline**: Every action (Booking, Check-in, Note added, Status change) is logged in the `client_record_entries` table.
*   **Case Details**: Stores personal info, case number, and linked documents.
*   **Status**: Clients can be `Active`, `Closed`, or `Archived`.

---

## 4. Administrative Workflows

### Staff Management
1.  **Create Staff**: Admin enters details and assigns credentials.
2.  **Manage**: Admin can edit details or delete accounts (soft delete).

### Lawyer Management
1.  **Onboarding**: Admin creates Lawyer account + Professional Profile.
2.  **Scheduling**: Admin or Lawyer sets "Available Hours" and "Specializations".
3.  **Approval**: Admin approves new Lawyer accounts before they appear on the booking page.

### System Settings
*   **Configuration**: Admin configures Office Name, Hours, Default Appointment Duration via the Settings page.
*   **Printing**: Summary reports available for Appointments, Client Lists, and individual Client Records.

---

## 5. Technology Stack & Database

*   **Framework**: Laravel 11.x
*   **Database**: MySQL / SQLite
*   **PDF/Printing**: CSS Print Media (@media print)
*   **Frontend**: Blade Templates + Bootstrap 5 + Vanilla JS
