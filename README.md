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
| **User** | Register | Create new user accounts; validate unique email; password ≥ 6 characters; hash passwords. | PHP Form + SQL INSERT | Leo & Mekial |
|  | Login / Logout | Authenticate user credentials; manage PHP session. | PHP Session + SQL SELECT | Leo & Mekial |
|  | Edit Profile | Update user info (email, password). | SQL UPDATE | Leo & Mekial |
|  | View My Auctions | Display auctions the user created (as seller). | SQL SELECT JOIN | Leo & Mekial |
| **Bid** | Place Bid | Submit bid; validate amount; record buyerId + auctionId + bidTime. | SQL INSERT + Validation | Leo & Mekial |
|  | View All Bids for Auction | List bids for a specific auction. | SQL SELECT ORDER BY | Leo & Mekial |
|  | Display Highest Bid | Query current highest bid dynamically. | SQL MAX() | Leo & Mekial |
|  | Basic Bid Validation | Reject low bids; ensure auction is active. | PHP Logic | Leo & Mekial |
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
| **Watchlist** | Add to Watchlist | Save an item or auction for later viewing. | SQL INSERT | Leo & Mekial |
|  | View Watchlist | List user’s saved items with links to detail pages. | SQL SELECT JOIN | Leo & Mekial |
|  | Remove from Watchlist (New) | Remove saved auction from watchlist. | SQL DELETE | Leo & Mekial |
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
├── database/                            # Database layer (SQL scripts and connection)
│   ├── auction_db_schema.sql            # Defines all tables (users, items, auctions, bids, watchlist, notifications)
│   ├── sample_data.sql                  # Sample dataset for testing and demo
│   ├── triggers.sql                     # Optional triggers for automatic updates (e.g. auction close)
│   └── db_connect.php                   # PHP connection file to MySQL (used by all other scripts)
│
├── php/                                 # Backend logic (PHP code)
│   │
│   ├── user/                            # (Leo & Mekial) User and Bid modules
│   │   ├── register.php                 # Register new users (validation + password hashing)
│   │   ├── login.php                    # User login authentication
│   │   ├── logout.php                   # Logout and destroy PHP session
│   │   ├── profile.php                  # View or edit user details
│   │   ├── place_bid.php                # Place a bid on an active auction
│   │   ├── bid_history.php              # Display user's bidding history
│   │   ├── view_bids.php                # Show all bids under a specific auction
│   │   ├── watchlist.php                # Add or remove items from user watchlist
│   │   ├── remove_watchlist.php           # Remove item/auction from watchlist
│   │   ├── notifications.php            # Display user notifications (outbid, won auction, etc.)
│   │   └── update_notification_status.php # Mark notifications as read
│   │
│   ├── auction/                         # (Yufei & Irene) Item and Auction modules
│   │   ├── add_item.php                 # Seller adds new item (title, description, category, image)
│   │   ├── edit_item.php                # Seller edits item details before auction starts
│   │   ├── delete_item.php              # Delete item if auction not active
│   │   ├── create_auction.php           # Start auction for selected item
│   │   ├── auction_list.php             # Show all ongoing auctions
│   │   ├── item_detail.php              # Display item info + current bids + countdown timer
│   │   ├── update_status.php            # Automatically close expired auctions
│   │   ├── winner_notification.php      # Send notification to highest bidder after auction ends
│   │   ├── search_filter.php            # Filter auctions by category, keyword, or price range
│   │   └── upload_image.php             # Handle image uploads for items
│   │
│   ├── shared/                          # Common resources shared by all pages
│   │   ├── header.php                   # Navigation bar and page header
│   │   ├── footer.php                   # Common footer section
│   │   ├── utils.php                    # Helper functions (e.g. format price, check login)
│   │   └── auth_check.php               # Middleware: restrict access to logged-in users
│
├── css/                                 # Frontend styling
│   ├── style.css                        # Global stylesheet
│   ├── auction.css                      # Auction and item detail pages
│   └── user.css                         # Login/register/profile pages
│
├── js/                                  # Frontend scripts
│   ├── main.js                          # Core JavaScript (validation, dynamic updates)
│   ├── countdown.js                     # Countdown timer for active auctions
│   ├── watchlist.js                     # Add/remove watchlist functionality via AJAX
│   └── notifications.js                 # Real-time notification refresh (AJAX polling)
│
├── images/                              # Image storage
│   ├── sample_items/                    # Example item images for demo
│   └── uploads/                         # User-uploaded item images
│
├── docs/                                # Project documentation
│   ├── ERD_diagram.png                  # Entity-Relationship Diagram
│   ├── ROADMAP.md                       # Project plan and weekly milestones
│   ├── function_table.md                # Detailed function descriptions (extended)
│   ├── schema_explanation.pdf           # Explanation of database relationships
│   └── final_report.docx                # Coursework report draft
│
├── index.php                            # Homepage displaying active auctions
├── about.php                            # About / project introduction page
├── contact.php                          # (Optional) Contact form or info page
├── config.php                           # Configuration constants (DB credentials, paths)
└── README.md                            # Project overview, feature list, and team division
```
