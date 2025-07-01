# PELS-INYAS 🎯

> A PHP-based participant management system for events and activities.

## 📋 Features

- ✅ Add new participants
- ✏️ Edit existing participant information
- 🔒 Secure database operations with prepared statements
- 🌍 Environment-based configuration
- 📡 RESTful API responses with proper HTTP status codes

## 🚀 Quick Start

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB database
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/prakulkhebbur/pels-inyas.git
   cd pels-inyas
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   
   Create a `.env` file in the root directory:
   ```env
   DBHOST=localhost
   DBNAME=PELS
   DBUSER=root
   DBPASS=your_password_here
   ```

4. **Set up the database**
   
   Create a MySQL database and table:
   ```sql
   CREATE DATABASE PELS;
   USE PELS;
   
   CREATE TABLE participents (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255) NOT NULL,
       email VARCHAR(255) NOT NULL UNIQUE,
       role VARCHAR(100) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

## 📖 Usage

### Adding a Participant

Send a POST request to `app.php` with the following data:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "role": "Participant"
}
```

### Editing a Participant

Send a POST request to `app.php` with the following data:
```json
{
    "edit_participant": true,
    "participant_id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "role": "Organizer"
}
```

## 🔧 API Response Codes

- `200` - Success
- `500` - Server error (database connection/query failed)

## 📁 Project Structure

```
pels-inyas/
├── app.php          # Main application logic
├── display.php      # Display/view functionality
├── composer.json    # PHP dependencies
├── .env            # Environment configuration
└── README.md       # This file
```

## 🛠️ Built With

- **PHP** - Server-side scripting
- **MySQL** - Database management
- **Composer** - Dependency management
- **vlucas/phpdotenv** - Environment variable management

## 👥 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

Made with ❤️ by [prakulkhebbur](https://github.com/prakulkhebbur)
