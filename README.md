# ServicEngine - AMC & IT Field Service ERP Web Application

A comprehensive, enterprise-grade Annual Maintenance Contract (AMC), Field Service Dispatch, Inventory Ledger, and GST Billing portal built with modern PHP, Bootstrap 5, and MySQL.

---

## ?? Key Modules & Capabilities

### 1. Operations & Dispatch Dashboard
- Real-time KPI metrics tracking open calls, SLAs, overdue tickets, and revenue.
- Live SLA countdown timer & breach warnings.
- Fast Universal Search (Ctrl + K) across tickets, customer names, serial numbers, and assets.

### 2. Service Call Management
- Multi-tier lifecycle: LOGGED ? ASSIGNED ? ACCEPTED ? EN_ROUTE ? IN_PROGRESS ? SPARE_REQUESTED ? SPARE_ISSUED ? RESOLVED ? CLOSED.
- Field engineer geo-location tracking and journey status logging.
- Digital Job Cards with customer signature capture & printable reports.

### 3. Preventive Maintenance (PM) Engine
- Automated recurring PM scheduler based on contract frequency (Monthly, Quarterly, Half-Yearly, Yearly).
- 1-Click batch & individual ticket dispatch.
- Auto-completion tracking when PM service calls are resolved.
- Customizable equipment maintenance inspection checklists.

### 4. Client & Machine Asset Hierarchy
- Multi-location customer hierarchy with individual branch/plant contact mapping.
- IT Asset / Machine catalog with QR code generator for instant on-site equipment scanning.
- Live asset service history and failure logs.

### 5. Spare Parts & Double-Entry Stock Ledger
- Comprehensive spare parts catalog with SKU, HSN codes, purchase/selling prices, and minimum stock alerts.
- Engineer spare requisition & manager approval workflow.
- Immutable double-entry stock transactions (OPENING, PURCHASE, ISSUE, RETURN, ADJUSTMENT).

### 6. Vendor Purchases & Supplier Directory
- Supplier master directory with GSTIN and contact details.
- Inward purchase invoice recording with automatic stock ledger increments.

### 7. GST Billing & Multi-Item Tax Invoicing
- Multi-line item invoice generator with live client-side CGST/SGST/IGST tax calculation.
- Quick service presets (AMC Plans, Breakdown Calls, PM Visits, Hardware Spares).
- Payment receipts ledger with balance tracking (Paid, Partially Paid, Overdue).
- Clean, printable PDF-ready tax invoices.

### 8. Role-Based Access Control (RBAC)
- **Super Administrator** & **Administrator**: Full operational and system master control.
- **Support / Operations**: Call dispatch, PM scheduling, requisitions, purchases, billing.
- **Call Center**: Fast ticket logging and client asset lookup.
- **Field Engineer**: Mobile-first interface for accepting calls, requesting spares, and submitting job cards.
- **Customer Portal**: Self-service ticket logging, asset status, and invoice history.

---

## ??? Technology Stack
- **Backend**: Pure PHP 8.1+ (MVC architecture, PDO prepared statements, CSRF protection, RBAC Session Manager).
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, Vanilla JS, Responsive Desktop & Mobile layout.
- **Database**: MySQL / MariaDB (Optimized schema with foreign keys and transactional integrity).
- **Web Server**: Apache (XAMPP / LAMP / Docker).

---

## ?? Quick Setup & Installation

1. **Clone the Repository**:
   `ash
   git clone <YOUR_REPO_URL> AMCWebAPP
   `
2. **Move to Web Directory**:
   Place inside your web root (e.g. xampp/htdocs/AMCWebAPP or /var/www/html/AMCWebAPP).

3. **Database Configuration**:
   - Check/update config/database.php with your MySQL credentials.
   - Run the automated installer by navigating to http://localhost/AMCWebAPP/install in your browser.
   - Click **Run Database Migrations** and **Load Demo Data**.

4. **Default Logins**:
   - **Admin**: dmin@amc.local | password123
   - **Support**: support@amc.local | password123
   - **Engineer**: ahul.engineer@amc.local | password123
   - **Customer**: ithead@techcorp.com | password123
