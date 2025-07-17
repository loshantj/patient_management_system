# 🏥 Clinic Patient Management System (Clinic PMS)

A web-based patient management system designed to streamline hospital clinic workflows using role-based access, QR integration, and digital appointment tracking. Built with PHP, MySQL, and Bootstrap.

---

## 🚀 Features

### 👥 User Roles

- **Patient**: Register, login, book appointments, view history
- **Doctor**: View appointments, add/update medical history, complete checkups
- **Receptionist**: Register patients, manage appointment queue, scan QR
- **Admin**: Oversee system usage, generate reports, manage staff

### 🗂️ Core Modules

- **New Patient Registration** (with QR code generation)
- **Login System** (Role-based)
- **Appointment Booking** (Doctor-assigned or auto-scheduled)
- **Queue Management** (Real-time tracking for doctors and reception)
- **Medical History Tracking** (Editable by doctors only)
- **Doctor Dashboard** (Today's queue, previous visits, consultation notes)
- **Secure Logout** (Session-based)

---

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 8+, MySQL
- **QR Code**: [phpqrcode library](https://sourceforge.net/projects/phpqrcode/)
- **Session & Authentication**: Native PHP session management

---

## 📸 Screenshots

| Registration | Doctor Dashboard | QR Example |
|--------------|------------------|------------|
| ![Register](assets/screens/register.png) | ![Doctor](assets/screens/doctor_dashboard.png) | ![QR](assets/screens/qr_sample.png) |

---

## 🔧 Setup Instructions

1. **Clone the repo**

   ```bash
   git clone https://github.com/your-username/clinic-pms.git
