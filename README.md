# README - FAQ Client

## Setup Instructions

### Local Development
1. Copy `.env.example` to `.env`
2. Update `.env` with your database credentials (SAME as admin)
3. Run: `php -S localhost:3000 -t api/app`
4. Visit: `http://localhost:3000`

### Vercel Deployment
1. Connect this repo to Vercel
2. Add these environment variables (SAME as Admin project):
   - DB_HOST
   - DB_USER
   - DB_PASSWORD
   - DB_NAME
   - ADMIN_URL (URL of admin project)

3. Deploy!

### Database
Uses same MySQL database as admin - they're connected!

### File Structure
```
api/app/
├── connect.php - Database connection
├── index.php - Homepage
├── my_tickets.php - User tickets
├── submit_ticket.php - New ticket form
└── ... (other client files)
```

### Connection to Admin
- Both projects use same database
- User tickets created in client are visible in admin
- Admin can manage all articles viewed by client
