# Auctionzone

Auctionzone is a PHP-based auction marketplace built for XAMPP / Apache with MySQL. Users can register, create auctions, place bids, and complete payments. Administrators can manage auctions and view platform statistics.

## Features

- User registration and login
- Auction creation with title, description, starting price, end date, and optional image upload
- Auction browsing with search and open/closed filters
- Detailed auction pages with bid history
- Real-time bidding and highest bid tracking
- User dashboard showing own auctions and bids
- Payment confirmation for auction winners
- Admin panel for managing auctions

## Requirements

- PHP 7.4+ (or compatible PHP 8)
- MySQL / MariaDB
- Apache (XAMPP recommended)
- PDO extension for MySQL

## Installation

1. Place the `Auctionzone` folder under your web server root. For XAMPP, use:
   - `C:\xampp\htdocs\Auctionzone`

2. Import the database schema:
   - Open phpMyAdmin or use MySQL CLI
   - Run `database.sql`
   - This creates the `auctionzone` database and required tables
   - It also inserts a default admin user

3. Verify database settings in `includes/db.php`:
   - Host: `localhost`
   - Database: `auctionzone`
   - User: `root`
   - Password: `` (empty)

4. Open the site in your browser:
   - `http://localhost/Auctionzone`

## Default Admin Account

- Email: `admin@example.com`
- Password: `admin123`

> Note: The default password is hashed and only valid if the same hash is kept in `database.sql`.

## Project Structure

- `index.php` - Homepage with auction listings and search
- `login.php`, `register.php`, `logout.php` - Authentication pages
- `dashboard.php` - User dashboard
- `auction.php` - Auction detail page
- `create_auction.php`, `edit_auction.php` - Auction creation/editing
- `bid.php` - Bid submission handler
- `payment.php` - Payment confirmation for winners
- `admin/` - Admin dashboard and auction management
- `includes/` - Shared functions, database connection, header/footer
- `css/` - Styles
- `js/` - Scripts
- `uploads/` - Uploaded auction images

## Notes

- `BASE_URL` is defined in `includes/functions.php` as `/Auctionzone`. If you move the project to a different path, update this constant.
- Uploaded images are stored in `uploads/` and served from the web root.
- The app does not include a production-ready payment gateway. `payment.php` only updates the auction status.

## Customization

- Update styles in `css/style.css`
- Add new functionality by extending PHP pages and MySQL tables
- Secure deployment by enabling HTTPS and using environment-based credentials
