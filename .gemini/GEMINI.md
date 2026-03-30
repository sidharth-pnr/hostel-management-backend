## Hostel Room Allocation and Complaint Management System - Mini Project Context (KTU CSD334)

# Campus Housing - Name of Website

## Project Overview
"Campus Housing" is a digital ecosystem designed for the **College of Engineering Trikaripur** to automate hostel room allocation and streamline complaint management. This project strictly adheres to the **KTU 2019 Scheme (Course Code: CSD334)** requirements for the Computer Science Mini Project.

### 🏗️ Technical Architecture
- **Frontend:** React 19 (Vite) + Tailwind CSS + Framer Motion.
- **Backend:** RESTful API in PHP (XAMPP environment).
- **Database:** MySQL (Relational schema with Foreign Key constraints).
- **State Management:** React Hooks (useState, useEffect) + Axios for API communication.

### 📂 Core Repositories
- **Frontend:** `C:\Users\LENOVO\OneDrive\Desktop\Mini Project Group15\hostel-management`
- **Backend API:** `C:\xampp\htdocs\hostel_room_api`

---

## 🚀 Key Modules & Workflow

### 1. Student Lifecycle
- **Registration:** Verification using University Reg No (Status: `PENDING`).
- **Approval:** Admins vet students before granting access (Status: `ACTIVE`).
- **Room Booking:** Real-time occupancy tracking and bed space allocation.
- **Complaint Center:** Students file maintenance tickets with priority levels and track resolution.

### 2. Administrative Command Center
- **Overview Dashboard:** Real-time stats on occupancy, student count, and urgent complaints.
- **RBAC:** Role-based access control for `SUPER` and `STAFF` admins.
- **Infrastructure Management:** CRUD operations for rooms and blocks.
- **Resolution Management:** Direct mapping of complaints to students for accountability.

---

## 🎓 KTU Syllabus Mandates (CSD334)
- **Software Engineering Focus:** Every feature must map to the SRS (Requirement Analysis) and SDD (System Design).
- **Aesthetics & Ergonomics:** High-end UI (Kerala Style) with minimal friction and professional layout.
- **Security:** Use of Prepared Statements in PHP to prevent SQL Injection and secure password hashing.
- **Testing:** Support for Functional and Integration testing reports as per the Teaching Plan.

---

## 🛠️ Operational Rules for Gemini CLI
- **Surgical Changes:** Prioritize performance and academic standards over flashy, non-functional features.
- **Self-Contained Deployment:** Prefer local SVG/Icons over external image URLs (refer to `src/components/Icons.jsx`).
- **API Integrity:** Ensure all frontend changes correspond to the PHP endpoints in the backend repository.
