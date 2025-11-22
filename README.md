# Databoss-33
This is our **UCL Database Auction Project**, developed using **HTML + PHP + MySQL**.  
The system allows users to register, list items, create auctions, and place bids online.

---

## Group Division

| Member | Responsibility |
|---------|----------------|
| **Yufei**  | Item & Auction modules |
| **Irene**  | Item & Auction modules |
| **Leo** | User & Bid modules |
| **Mekial** | User & Bid modules |
||mail|

---

## Function Overview (Detailed)
| Module | Function | Description | Involves | Owner |
|---------|-----------|--------------|-----------|--------|
| **User** | Register | Create new user accounts; validate unique email; password ≥ 6 characters; hash passwords. | PHP Form + SQL INSERT | Mekial |
|  | Login / Logout | Authenticate user credentials; manage PHP session. | PHP Session + SQL SELECT | Mekial |
|  | Edit Profile | Update user info (email, password). | SQL UPDATE | Mekial |
|  | View My Auctions | Display auctions the user created (as seller). | SQL SELECT JOIN | Mekial |
| **Bid** | Get Highest Bid for an Auction | Retrieve the highest bid for a specific auction; return NULL if no bids exist. | SQL SELECT MAX(bidPrice) | Leo |
|  | Get All Bids for an Auction | “Auction’s view” — list all bids placed on a specific auction. | SQL SELECT ORDER BY bidTime DESC | Leo |
|  | Get Bids by User | “Buyer’s view” — display all bids placed by the current user (“My Bids”). | SQL SELECT WHERE buyerId = ? | Leo |
|  | Place Bid | Allow a buyer to submit a bid; validate amount and auction status before inserting. | SQL INSERT + PHP validation | Leo |
|  | View Bids on My Auctions | “Seller’s view” — view all bids placed on auctions created by the current user. | SQL SELECT JOIN (filter by sellerId) | Leo |
| **Item** | Add New Item | Seller adds item with title, description, category, and image URL. | SQL INSERT | Yufei & Irene |
|  | Edit Item | Seller modifies item info before auction starts. | SQL UPDATE | Yufei & Irene |
|  | Delete Item | Seller removes item (only if not under auction). | SQL DELETE | Yufei & Irene |
|  | Browse Items | List all items with image preview and category filter. | SQL SELECT | Yufei & Irene |
|  | Item Details | Show full item info and link to its auction & bids. | SQL SELECT JOIN | Yufei & Irene |
| **Auction** | Create Auction | Seller sets start_price, start_time, end_time; link item_id to auction_id. | SQL INSERT | Yufei |
|  | Update Auction Status | Automatically close auctions past end_time (“active” → “closed”). | SQL UPDATE + PHP Cron | Yufei |
|  | View Active Auctions | Display all auctions currently active with countdown timers. | SQL SELECT WHERE status='active' | Yufei |
|  | Auction Result | Determine winner (highest bidder) when auction closes. | SQL SELECT MAX(bid_amount) | Yufei |
|  | found the highest bid price and update| cron -- lecture 7|lecture 7|Yufei|
|  | schedule automatically | lecture 7 | lecture 7 | Yufei| 
| **Watchlist** | Add to Watchlist | Save an item or auction for later viewing. | SQL INSERT | Leo |
|  | View Watchlist | List user’s saved items with links to detail pages. | SQL SELECT JOIN | Leo |
|  | Remove from Watchlist (New) | Remove saved auction from watchlist. | SQL DELETE | Leo |
| **Images** | Upload Image | Allow seller to attach multiple images to each item. | File Upload + SQL INSERT | Yufei & Irene |
|  | Display Image | Retrieve and show item images in item detail page. | SQL SELECT | Yufei & Irene |


---

## Project Roadmap

| Week | Goal | Deliverables |
|------|------|---------------|
| **Week 6** | database schema | ERD Diagram + SQL tables |
| **Week 7** | Add sample data and test PHP–SQL connection &Implement core features (User, Bid, Item, Auction)  | Test dataset + db_connect.php |
| **Week 8** | Implement core features (User, Bid, Item, Auction) & frontend design| CRUD pages + backend logic |

| **Week 9** | Integration & final testing | Full system + presentation report |

---
## Technical Responsibilities Overview

Our team’s development structure is **module-based**, meaning each group owns both backend and frontend work for their specific modules.  
The project uses a classic **LAMP-style full-stack architecture**:  
Frontend (HTML/CSS/JS) → Backend (PHP) → Database (MySQL).

---

### Overall Architecture

| Layer | Technology | Responsibility |
|-------|-------------|----------------|
| **Frontend** | HTML, CSS, JavaScript | User interface, form validation, auction countdowns, dynamic updates |
| **Backend** | PHP | Core business logic, authentication, bid processing, auction control |
| **Database** | MySQL | Store and manage persistent data (users, items, bids, auctions, watchlists, notifications) |

---

### Group Division by Technical Focus

| Group | Modules | Backend Focus | Frontend Focus |
|--------|-----------|----------------|----------------|
| **Group A (Leo & Mekial)** | **User + Bid** | <ul><li>User registration and login (session management)</li><li>Bid validation and comparison (SQL MAX logic)</li><li>Notification system (insert + read updates)</li></ul> | <ul><li>Dynamic bid updates (AJAX polling)</li><li>User dashboard and bid history pages</li><li>Form validation and alerts</li></ul> |
| **Group B (Yufei & Irene)** | **Item + Auction** | <ul><li>Item CRUD (add/edit/delete items)</li><li>Auction lifecycle control (start, end, auto-close)</li><li>Winner determination and notification</li></ul> | <ul><li>Item listing and search/filtering</li><li>Countdown timer display</li><li>Image upload and preview functionality</li></ul> |

---

###  Collaboration and Integration
- Both groups share the same **database schema (`auction_db_schema.sql`)**.  
- **`db_connect.php`** serves as the universal database connection file used across all modules.  
- Common frontend files (`/css`, `/js`, `/shared/`) are collaboratively maintained.  
- When integrated:
  - **Auction group** creates and manages auctions (`auction_id`).
  - **Bid group** references those auctions for bid submission and price updates.

---

### Summary
> Both groups are **full-stack** within their modules.  
> The project emphasizes backend logic (PHP + MySQL) with interactive frontend components for a complete user experience.
> 

## Folder Structure

The project follows a modular structure separating backend (PHP logic), database scripts, frontend assets, and documentation.  
Each group is responsible for a clear subset of folders as indicated below.

```
auction-website/
│
├── database/                            # Database layer
│   ├── auction_schema.sql               # Defines tables (users, items, auctions, bids, watchlist)
│   ├── sample_data.sql                  # Optional test data
│   └── db_connect.php                   # Shared DB connection
│
├── php/                                 # Backend logic
│   │
│   ├── users/                           # User module (Register / Login / Profile)
│   │   ├── register.php                 # Create new user accounts
│   │   ├── login.php                    # User authentication + session start
│   │   ├── logout.php                   # Destroy session
│   │   └── profile.php                  # View or edit user details
│   │
│   ├── bids/                            # Bid module (Leo)
│   │   ├── bids_functions.php           # Core backend functions:
│   │   │                                   # - Get highest bid
│   │   │                                   # - Get bids for auction
│   │   │                                   # - Get bids by user
│   │   │                                   # - Place bid
│   │   │                                   # - View bids on my auctions
│   │   ├── place_bid.php                # Handle bid submission (POST)
│   │   ├── view_bids.php                # Display bids for a specific auction
│   │   └── my_bids.php                  # “Buyer’s view” — all bids by current user
│   │
│   ├── watchlist/                       # Watchlist module (Leo & Mekial)
│   │   ├── watchlist_funcs.php          # Backend functions:
│   │   │                                   # - add_to_watchlist
│   │   │                                   # - view_watchlist
│   │   │                                   # - remove_from_watchlist
│   │   ├── watchlist.php                 # View user watchlist
│   │   └── remove_watchlist.php          # Remove auction from watchlist
│   │
│   ├── shared/                          # Common helpers
│   │   ├── header.php                   # Navigation bar
│   │   ├── footer.php                   # Footer section
│   │   └── utilities.php                # Shared helper functions (e.g., auth checks)
│
├── css/
│   └── style.css                        # Global styling
│
├── js/
│   └── main.js                          # Basic frontend scripts (optional)
│
├── images/
│   └── uploads/                         # Item images (if applicable)
│
├── index.php                            # Homepage — list active auctions
└── README.md                            # Project overview + function tables

