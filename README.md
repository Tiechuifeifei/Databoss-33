# Databoss-33
This is our **UCL Database Auction Project**, developed using **HTML + PHP + MySQL**.  
The system allows users to register, list items, create auctions, and place bids online.

---

## Group Division

| Member | Role | Responsibility |
|---------|------|----------------|
| **Yufei** | Database + Backend | Item & Auction modules |
| **Irene** | Database + Backend | Item & Auction modules |
| **Leo** | Database + Frontend | User & Bid modules |
| **Mekial** | Database + Frontend | User & Bid modules |

---

## Function Overview (Detailed)

| Module | Function | Description | Involves | Owner |
|---------|-----------|--------------|-----------|--------|
| **User** | Register | Create new user accounts; validate unique username & email; hash passwords before storing. | PHP Form + SQL INSERT | Leo & Mekial |
|  | Login / Logout | Authenticate user credentials; start or destroy PHP session. | PHP Session + SQL SELECT | Leo & Mekial |
|  | Edit Profile | Update user info (email, password, etc.) with validation. | SQL UPDATE | Leo & Mekial |
|  | View My Auctions | Display all auctions where user is the seller. | SQL SELECT JOIN | Leo & Mekial |
| **Bid** | Place Bid | Submit bid amount; check if higher than current max; record bidder_id, auction_id, bid_time. | SQL INSERT + Validation | Leo & Mekial |
|  | View All Bids for Auction | Show all bids of one auction sorted by amount/time. | SQL SELECT ORDER BY | Leo & Mekial |
|  | Display Highest Bid | Query and show current highest bid dynamically on auction detail page. | SQL MAX() | Leo & Mekial |
|  | Bid Validation | Reject bids lower than current highest; prevent self-bidding by seller. | PHP Logic | Leo & Mekial |
| **Item** | Add New Item | Seller adds item with title, description, category, and image URL. | SQL INSERT | Yufei & Irene |
|  | Edit Item | Seller modifies item info before auction starts. | SQL UPDATE | Yufei & Irene |
|  | Delete Item | Seller removes item (only if not under auction). | SQL DELETE | Yufei & Irene |
|  | Browse Items | List all items with image preview and category filter. | SQL SELECT | Yufei & Irene |
|  | Item Details | Show full item info and link to its auction & bids. | SQL SELECT JOIN | Yufei & Irene |
| **Auction** | Create Auction | Seller sets start_price, start_time, end_time; link item_id to auction_id. | SQL INSERT | Yufei & Irene |
|  | Update Auction Status | Automatically close auctions past end_time (“active” → “closed”). | SQL UPDATE + PHP Cron | Yufei & Irene |
|  | View Active Auctions | Display all auctions currently active with countdown timers. | SQL SELECT WHERE status='active' | Yufei & Irene |
|  | Auction Result | Determine winner (highest bidder) when auction closes. | SQL SELECT MAX(bid_amount) | Yufei & Irene |
| **Watchlist** | Add to Watchlist | Save an item or auction for later viewing. | SQL INSERT | Shared |
|  | View Watchlist | List user’s saved items with links to detail pages. | SQL SELECT JOIN | Shared |
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

## Folder Structure
auction-website/
│
├── 📁 database/ # Database layer (SQL scripts and connection)
│ ├── auction_db_schema.sql # Defines all tables (users, items, auctions, bids, watchlist, notifications)
│ ├── sample_data.sql # Sample dataset for testing and demo
│ ├── triggers.sql # Optional triggers for automatic updates (e.g. auction close)
│ └── db_connect.php # PHP connection file to MySQL (used by all other scripts)
│
├── 📁 php/ # Backend logic (PHP code)
│ │
│ ├── 📁 user/ # (Leo & Mekial) User and Bid modules
│ │ ├── register.php # Register new users (validation + password hashing)
│ │ ├── login.php # User login authentication
│ │ ├── logout.php # Logout and destroy PHP session
│ │ ├── profile.php # View or edit user details
│ │ ├── place_bid.php # Place a bid on an active auction
│ │ ├── bid_history.php # Display user's bidding history
│ │ ├── view_bids.php # Show all bids under a specific auction
│ │ ├── watchlist.php # Add or remove items from user watchlist
│ │ ├── notifications.php # Display user notifications (outbid, won auction, etc.)
│ │ └── update_notification_status.php # Mark notifications as read
│ │
│ ├── 📁 auction/ # (Yufei & Irene) Item and Auction modules
│ │ ├── add_item.php # Seller adds new item (title, description, category, image)
│ │ ├── edit_item.php # Seller edits item details before auction starts
│ │ ├── delete_item.php # Delete item if auction not active
│ │ ├── create_auction.php # Start auction for selected item
│ │ ├── auction_list.php # Show all ongoing auctions
│ │ ├── item_detail.php # Display item info + current bids + countdown timer
│ │ ├── update_status.php # Automatically close expired auctions
│ │ ├── winner_notification.php # Send notification to highest bidder after auction ends
│ │ ├── search_filter.php # Filter auctions by category, keyword, or price range
│ │ └── upload_image.php # Handle image uploads for items
│ │
│ ├── 📁 shared/ # Common resources shared by all pages
│ │ ├── header.php # Navigation bar and page header
│ │ ├── footer.php # Common footer section
│ │ ├── utils.php # Helper functions (e.g. format price, check login)
│ │ └── auth_check.php # Middleware: restrict access to logged-in users
│
├── 📁 css/ # Frontend styling
│ ├── style.css # Global stylesheet
│ ├── auction.css # Auction and item detail pages
│ └── user.css # Login/register/profile pages
│
├── 📁 js/ # Frontend scripts
│ ├── main.js # Core JavaScript (e.g. validation, dynamic updates)
│ ├── countdown.js # Countdown timer for active auctions
│ ├── watchlist.js # Add/remove watchlist functionality via AJAX
│ └── notifications.js # Real-time notification refresh (AJAX polling)
│
├── 📁 images/ # Image storage
│ ├── sample_items/ # Example item images for demo
│ └── uploads/ # User-uploaded item images
│
├── 📁 docs/ # Project documentation
│ ├── ERD_diagram.png # Entity-Relationship Diagram
│ ├── ROADMAP.md # Project plan and weekly milestones
│ ├── function_table.md # Detailed function descriptions (extended version)
│ ├── schema_explanation.pdf # Explanation of database relationships
│ └── final_report.docx # Coursework report draft
│
├── index.php # Homepage displaying active auctions
├── about.php # About or project introduction page
├── contact.php # (Optional) Contact form or info page
├── config.php # Configuration constants (DB credentials, paths)
└── README.md # Project overview, feature list, and team division
