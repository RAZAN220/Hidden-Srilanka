# 🌴 Hidden Sri Lanka – Community Powered Tourism Platform

> **Discover Sri Lanka through the eyes of locals.**

Hidden Sri Lanka is a modern web-based tourism platform that helps travelers discover popular attractions and hidden gems across Sri Lanka. The platform allows local communities to contribute new tourist destinations, which are reviewed and approved by administrators before becoming publicly available.

This project aims to promote local tourism by providing an interactive map, detailed destination information, community contributions, and a user-friendly experience.

---

## ✨ Features

### 👤 Tourist
- User Registration & Login
- Browse Tourist Places
- Search Destinations
- Filter by Category and District
- View Place Details
- Interactive Map
- Rate & Review Places
- Manage User Profile

### 📝 Contributor
- Submit New Tourist Places
- Upload Multiple Images
- Track Submission Status
- Edit Own Submissions

### 👨‍💼 Admin
- Secure Admin Dashboard
- Approve or Reject Tourist Places
- Manage Users
- Manage Categories
- Manage Reviews
- View Platform Statistics

---

## 🗺️ Main Modules

- Authentication System
- Tourist Place Management
- Community Contributions
- Reviews & Ratings
- Category Management
- Admin Dashboard
- User Profile Management
- Interactive Map Integration

---

## 🛠️ Technology Stack

### Frontend
- HTML5
- CSS3
- JavaScript (ES6)

### Backend
- PHP

### Database
- MySQL

### Maps
- Google Maps API / Leaflet.js

### Web Server
- Apache (XAMPP)

---

## 📂 Project Structure

```
hidden-sri-lanka/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── places.php
│   ├── categories.php
│   └── reviews.php
│
├── user/
│   ├── dashboard.php
│   ├── profile.php
│   ├── add-place.php
│   └── my-places.php
│
├── includes/
│   ├── config.php
│   ├── header.php
│   ├── navbar.php
│   ├── footer.php
│   └── session.php
│
├── uploads/
│
├── index.php
├── login.php
├── register.php
├── explore.php
├── place-details.php
├── about.php
├── contact.php
└── README.md
```

---

## 🗃️ Database Tables

- Users
- Categories
- Places
- Place Images
- Reviews
- Favorites

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/hidden-sri-lanka.git
```

### 2. Move the Project

Copy the project folder into your **XAMPP** `htdocs` directory.

```
C:\xampp\htdocs\hidden-sri-lanka
```

### 3. Create Database

Create a MySQL database named:

```
hidden_sri_lanka
```

### 4. Import Database

Import the SQL file into MySQL using phpMyAdmin.

### 5. Configure Database

Update your database credentials in:

```
includes/config.php
```

### 6. Start Server

Start **Apache** and **MySQL** from XAMPP.

### 7. Open the Website

```
http://localhost/hidden-sri-lanka
```

---

## 📸 Screens

- Home
- Login
- Register
- Explore Places
- Place Details
- Add Place
- User Dashboard
- Admin Dashboard
- Profile
- Contact

---

## 🎯 Future Enhancements

- AI Travel Recommendations
- Weather Integration
- Trip Planner
- Multi-language Support
- Offline Maps
- Mobile Application
- Event Calendar
- Emergency Contacts
- Hotel & Restaurant Recommendations

---

## 🎓 Academic Purpose

This project was developed as a **Final Year Project** to demonstrate practical knowledge of full-stack web development using PHP and MySQL while addressing real-world tourism challenges in Sri Lanka.

---

## 👨‍💻 Author

**Mohamath Razan**

- Web Developer
- UI/UX Designer
- Cybersecurity Learner

---

## 📄 License

This project is intended for educational and portfolio purposes.
