-- ====================================================================
-- DEMO DATA SEEDER FOR AMC & FIELD SERVICE MANAGEMENT SYSTEM
-- ====================================================================

-- 1. Roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Super Admin', 'Full system access & server configuration'),
(2, 'Admin', 'Operations, contract, billing & inventory manager'),
(3, 'Support', 'Call dispatch, engineer assignment & SLA coordinator'),
(4, 'Call Center', 'Fast search, customer inquiry & ticket booking'),
(5, 'Engineer', 'Field technician, GPS tracking, job card & spare requisition'),
(6, 'Customer', 'Client portal, asset tracking & ticket logging')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 2. System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('company_name', 'Apex IT Solutions & AMC Services', 'general'),
('company_email', 'support@apexit-amc.com', 'general'),
('company_phone', '+91 98765 43210', 'general'),
('company_mobile', '+91 98765 43210', 'general'),
('company_gstin', '27AAACA1234A1Z5', 'general'),
('company_address', 'Tower B, 4th Floor, Tech Park, Andheri East, Mumbai, MH 400069', 'general'),
('company_website', 'https://apexit-amc.com', 'general'),
('currency_symbol', '₹', 'general'),
('invoice_prefix', 'INV-', 'billing'),
('call_prefix', 'CALL-', 'calls'),
('contract_prefix', 'CON-', 'contracts'),
('customer_prefix', 'CUST-', 'customers'),
('asset_prefix', 'AST-', 'assets'),
('service_report_prefix', 'SR-', 'field'),
('purchase_prefix', 'PUR-', 'inventory'),
('spare_req_prefix', 'REQ-', 'inventory'),
('default_gst_rate', '18', 'billing'),
('sla_escalation_enabled', '1', 'sla'),
('pwa_enabled', '1', 'pwa'),
('whatsapp_notifications', '1', 'messaging')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 3. Default Users (Password for all demo users is: password123)
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password`, `mobile`, `employee_code`, `skills`, `is_active`) VALUES
(1, 1, 'Master Administrator', 'admin@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'EMP-001', 'System Architecture, Security, Database', 1),
(2, 2, 'Operations Manager', 'manager@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', 'EMP-002', 'Contracts, Billing, Inventory Approval', 1),
(3, 3, 'Support Dispatcher', 'support@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', 'EMP-003', 'Call Dispatch, SLA Monitoring', 1),
(4, 4, 'Call Center Agent', 'callcenter@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543213', 'EMP-004', 'Fast Search, Customer Support', 1),
(5, 5, 'Rahul Sharma (Senior Engineer)', 'rahul.engineer@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9820011223', 'ENG-101', 'Desktop, Laptop, Server Hardware, Windows Server, Linux, RAID', 1),
(6, 5, 'Amit Verma (Network & Hardware)', 'amit.engineer@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9820044556', 'ENG-102', 'Networking, Cisco Switches, Firewall, Printers, CCTV', 1),
(7, 5, 'Pooja Patel (Field Technician)', 'pooja.engineer@amc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9820077889', 'ENG-103', 'Desktop Hardware, OS Troubleshooting, Peripherals, Data Recovery', 1),
(8, 6, 'Vikram Mehta (TechCorp IT Head)', 'ithead@techcorp.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9811122233', 'CUST-U1', 'IT Asset Management', 1)
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`), `password` = VALUES(`password`);

-- 4. Asset Types
INSERT INTO `asset_types` (`id`, `name`, `category`, `icon`, `description`) VALUES
(1, 'Desktop Computer', 'HARDWARE', 'bi-pc-display', 'Workstation and office tower PCs'),
(2, 'Laptop Computer', 'HARDWARE', 'bi-laptop', 'Commercial notebooks & ultrabooks'),
(3, 'Rack/Tower Server', 'SERVER', 'bi-server', 'Enterprise servers & storage units'),
(4, 'Laser / Multi-function Printer', 'PERIPHERAL', 'bi-printer', 'Network printers & scanners'),
(5, 'Managed Network Switch / Router', 'NETWORK', 'bi-hdd-network', 'Cisco / HP managed switches & routers'),
(6, 'Online UPS System', 'POWER', 'bi-lightning-charge', '1kVA - 20kVA backup power units')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 5. Customers
INSERT INTO `customers` (`id`, `customer_code`, `company_name`, `contact_person`, `designation`, `mobile`, `alternate_mobile`, `email`, `gstin`, `pan`, `billing_address`, `city`, `state`, `pincode`, `customer_type`, `status`) VALUES
(1, 'CUST-2026-000001', 'TechCorp Solutions Pvt Ltd', 'Vikram Mehta', 'IT Infrastructure Head', '9811122233', '022-28471100', 'ithead@techcorp.com', '27AABCT8844K1ZV', 'AABCT8844K', '7th Floor, Infinity Tower, Mindspace, Malad West', 'Mumbai', 'Maharashtra', '400064', 'AMC', 'ACTIVE'),
(2, 'CUST-2026-000002', 'Zenith Financial Services Ltd', 'Priya Deshmukh', 'Chief Technology Officer', '9822233344', '022-67129900', 'cto@zenithfin.com', '27AAACZ4455M1Z2', 'AAACZ4455M', '14th Floor, Maker Chambers V, Nariman Point', 'Mumbai', 'Maharashtra', '400021', 'AMC', 'ACTIVE'),
(3, 'CUST-2026-000003', 'Apollo Healthcare Diagnostics', 'Dr. Sanjay Rao', 'Operations Director', '9833344455', '022-25608877', 'ops@apollodiagnostics.in', '27AAACA9911N1ZK', 'AAACA9911N', 'Apollo Diagnostic Complex, LBS Marg, Ghatkopar West', 'Mumbai', 'Maharashtra', '400086', 'BOTH', 'ACTIVE'),
(4, 'CUST-2026-000004', 'Metro Global Logistics Inc', 'Arun Nair', 'Admin Manager', '9844455566', '022-27891234', 'admin@metrologistics.com', '27AABCM7733P1ZF', 'AABCM7733P', 'Sector 17, Vashi Commercial Hub', 'Navi Mumbai', 'Maharashtra', '400703', 'PAID', 'ACTIVE')
ON DUPLICATE KEY UPDATE `customer_code` = VALUES(`customer_code`);

-- Link Customer User (ID 8) to TechCorp (ID 1)
UPDATE `users` SET `customer_id` = 1 WHERE `id` = 8;

-- 6. Customer Locations
INSERT INTO `customer_locations` (`id`, `customer_id`, `location_name`, `address`, `city`, `state`, `pincode`, `contact_person`, `mobile`, `email`, `latitude`, `longitude`) VALUES
(1, 1, 'Mindspace Corporate HQ', '7th Floor, Infinity Tower, Mindspace, Malad West', 'Mumbai', 'Maharashtra', '400064', 'Vikram Mehta', '9811122233', 'ithead@techcorp.com', 19.176400, 72.834700),
(2, 1, 'Andheri R&D Center', 'Unit 301, Pinnacle Business Park, Mahakali Caves Road', 'Mumbai', 'Maharashtra', '400093', 'Karan Johar (Site Lead)', '9811199988', 'rnd@techcorp.com', 19.123200, 72.868100),
(3, 2, 'Nariman Point Head Office', '14th Floor, Maker Chambers V, Nariman Point', 'Mumbai', 'Maharashtra', '400021', 'Priya Deshmukh', '9822233344', 'cto@zenithfin.com', 18.926100, 72.822800),
(4, 3, 'Ghatkopar Diagnostic Hub', 'LBS Marg, Ghatkopar West', 'Mumbai', 'Maharashtra', '400086', 'Dr. Sanjay Rao', '9833344455', 'ops@apollodiagnostics.in', 19.086400, 72.909000)
ON DUPLICATE KEY UPDATE `location_name` = VALUES(`location_name`);

-- 7. Contract Types
INSERT INTO `contract_types` (`id`, `name`, `code`, `is_labour_covered`, `is_spares_covered`, `is_visits_covered`, `pm_frequency`, `description`) VALUES
(1, 'Comprehensive AMC (All Inclusive)', 'COMPREHENSIVE', 1, 1, 1, 'QUARTERLY', 'Complete coverage: Labour, all spare parts replacement, unlimited visits & quarterly PM'),
(2, 'Non-Comprehensive AMC (Labour Only)', 'NON_COMPREHENSIVE', 1, 0, 1, 'QUARTERLY', 'Covers engineer visits, labour & PM. Spare parts billed extra as per actuals'),
(3, 'Preventive Maintenance Contract', 'PREVENTIVE_ONLY', 1, 0, 0, 'MONTHLY', 'Scheduled monthly health checks, cleaning & diagnostics. Breakdown visits chargeable'),
(4, 'Server & Mission-Critical SLA AMC', 'SERVER_CRITICAL', 1, 1, 1, 'MONTHLY', '24x7 2-Hour SLA response for enterprise servers & networking infrastructure')
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`);

-- 8. Contracts
INSERT INTO `contracts` (`id`, `contract_number`, `customer_id`, `contract_type_id`, `title`, `start_date`, `end_date`, `contract_value`, `billing_frequency`, `payment_terms`, `gst_rate`, `response_time_hrs`, `resolution_time_hrs`, `status`) VALUES
(1, 'CON-2026-000001', 1, 1, 'TechCorp Enterprise Annual AMC 2026-27', '2026-01-01', '2026-12-31', 450000.00, 'QUARTERLY', 'Quarterly in Advance', 18.00, 2, 8, 'ACTIVE'),
(2, 'CON-2026-000002', 2, 2, 'Zenith Financial Non-Comprehensive AMC', '2026-04-01', '2027-03-31', 180000.00, 'ANNUAL', '100% Advance', 18.00, 4, 12, 'ACTIVE'),
(3, 'CON-2026-000003', 3, 4, 'Apollo Hospital Mission Critical Server SLA', '2026-01-01', '2026-12-31', 320000.00, 'HALF_YEARLY', 'Half-yearly in Advance', 18.00, 1, 4, 'ACTIVE')
ON DUPLICATE KEY UPDATE `contract_number` = VALUES(`contract_number`);

-- 9. Machines / IT Assets
INSERT INTO `machines` (`id`, `asset_code`, `asset_tag`, `customer_id`, `location_id`, `asset_type_id`, `building`, `floor`, `department`, `assigned_employee`, `make`, `model`, `serial_number`, `processor`, `ram`, `storage`, `operating_system`, `ip_address`, `mac_address`, `status`, `qr_code_token`) VALUES
(1, 'AST-2026-000001', 'TC-SRV-01', 1, 1, 3, 'Infinity Tower', '7th Floor', 'Server Room', 'IT Infrastructure Team', 'Dell PowerEdge', 'R740 Rack Server', 'DLL-R740-984210', 'Intel Xeon Gold 6230 (20C/40T)', '64 GB ECC DDR4', '2x 1.92TB Enterprise NVMe SSD RAID 1', 'Windows Server 2022 Datacenter', '192.168.10.5', '00:14:22:01:23:45', 'UNDER_AMC', 'QR-TC-AST-000001-984210'),
(2, 'AST-2026-000002', 'TC-WS-101', 1, 1, 1, 'Infinity Tower', '7th Floor', 'Accounts & Finance', 'Rajesh K (Accounts Head)', 'HP EliteDesk', '800 G6 Microtower', 'HP-ED-441209', 'Intel Core i7-10700', '16 GB DDR4', '512 GB NVMe SSD + 1 TB HDD', 'Windows 11 Pro 64-bit', '192.168.10.45', '70:85:C2:55:11:88', 'UNDER_AMC', 'QR-TC-AST-000002-441209'),
(3, 'AST-2026-000003', 'TC-LP-204', 1, 2, 2, 'Pinnacle Park', '3rd Floor', 'Software Development', 'Neha Sen (Senior Architect)', 'Lenovo ThinkPad', 'T14 Gen 3', 'LN-TP-771133', 'AMD Ryzen 7 PRO 6850U', '32 GB LPDDR5', '1 TB Gen4 NVMe SSD', 'Ubuntu 24.04 LTS', '192.168.20.102', '3C:F8:62:99:44:12', 'UNDER_AMC', 'QR-TC-AST-000003-771133'),
(4, 'AST-2026-000004', 'TC-PRN-01', 1, 1, 4, 'Infinity Tower', '7th Floor', 'Operations Bay', 'Shared Operations Team', 'HP LaserJet Enterprise', 'M608dn Network Printer', 'HP-LJ-990021', 'N/A', '512 MB', 'Internal Print Spooler', 'HP FutureSmart Firmware', '192.168.10.200', '00:1E:0B:88:77:66', 'UNDER_AMC', 'QR-TC-AST-000004-990021'),
(5, 'AST-2026-000005', 'ZF-WS-501', 2, 3, 1, 'Maker Chambers V', '14th Floor', 'Equity Trading Desk', 'Manoj Jain (Trader)', 'Lenovo ThinkCentre', 'M90t Gen 3', 'LN-TC-558811', 'Intel Core i9-12900', '32 GB DDR5', '1 TB NVMe SSD', 'Windows 11 Pro', '10.50.1.34', 'A4:BB:6D:12:34:56', 'UNDER_AMC', 'QR-ZF-AST-000005-558811'),
(6, 'AST-2026-000006', 'AP-SRV-HIS', 3, 4, 3, 'Diagnostic Hub', 'Ground Floor', 'Data Center', 'Hospital IT Team', 'HP ProLiant', 'DL380 Gen10', 'HP-DL-883322', '2x Intel Xeon Silver 4210R', '128 GB ECC DDR4', '4x 960GB SAS SSD RAID 10', 'Red Hat Enterprise Linux 9', '172.16.1.10', 'D8:9D:67:AA:BB:CC', 'UNDER_AMC', 'QR-AP-AST-000006-883322')
ON DUPLICATE KEY UPDATE `asset_code` = VALUES(`asset_code`);

-- 10. Map Machines to Contracts
INSERT INTO `contract_machines` (`contract_id`, `machine_id`, `machine_rate`, `start_date`, `end_date`, `status`) VALUES
(1, 1, 45000.00, '2026-01-01', '2026-12-31', 'ACTIVE'),
(1, 2, 12000.00, '2026-01-01', '2026-12-31', 'ACTIVE'),
(1, 3, 15000.00, '2026-01-01', '2026-12-31', 'ACTIVE'),
(1, 4, 8000.00, '2026-01-01', '2026-12-31', 'ACTIVE'),
(2, 5, 9000.00, '2026-04-01', '2027-03-31', 'ACTIVE'),
(3, 6, 85000.00, '2026-01-01', '2026-12-31', 'ACTIVE')
ON DUPLICATE KEY UPDATE `machine_rate` = VALUES(`machine_rate`);

-- 11. Problem Categories & Problems
INSERT INTO `problem_categories` (`id`, `name`, `description`) VALUES
(1, 'System Boot & OS Failure', 'BSOD, boot loops, corrupt OS, bootloader issues'),
(2, 'Hardware Component Fault', 'Faulty RAM, dead Motherboard, SMPS failure, HDD noise/bad sectors'),
(3, 'Display & Graphics Issue', 'Flickering screen, no video signal, broken LCD matrix'),
(4, 'Network & Connectivity', 'LAN disconnection, IP conflict, WiFi drop, domain sync'),
(5, 'Printer & Peripheral Malfunction', 'Paper jam, toner streaking, roller wear, driver crash'),
(6, 'Preventive Maintenance & Cleaning', 'Blower dust cleaning, thermal paste re-application, diagnostics')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `problems` (`id`, `category_id`, `name`, `default_priority`, `estimated_minutes`) VALUES
(1, 1, 'Windows Blue Screen (BSOD) / Boot Device Not Found', 'HIGH', 60),
(2, 2, 'Continuous Beeping Sound / No POST / RAM Fault', 'HIGH', 45),
(3, 2, 'Hard Disk Clicking Sound / Bad Sectors Detected', 'HIGH', 90),
(4, 2, 'System Power Failure / Dead SMPS / No Power LED', 'HIGH', 60),
(5, 3, 'No Display Output on Monitor / GPU Failure', 'MEDIUM', 45),
(6, 4, 'Cannot Connect to Office Local Network / Domain Server', 'MEDIUM', 30),
(7, 5, 'Printer Paper Feed Roller Jam / Double Feed Issue', 'LOW', 45),
(8, 6, 'Quarterly Scheduled Preventive Maintenance & Dusting', 'LOW', 60)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 12. SLA Master
INSERT INTO `sla_master` (`id`, `priority`, `call_type`, `response_time_minutes`, `resolution_time_minutes`, `escalate_after_minutes`) VALUES
(1, 'CRITICAL', 'ALL', 30, 240, 120),
(2, 'HIGH', 'ALL', 60, 480, 240),
(3, 'MEDIUM', 'ALL', 120, 720, 480),
(4, 'LOW', 'ALL', 240, 1440, 720)
ON DUPLICATE KEY UPDATE `response_time_minutes` = VALUES(`response_time_minutes`);

-- 13. Spare Categories, Brands & Items
INSERT INTO `spare_categories` (`id`, `name`, `code`) VALUES
(1, 'Storage Drives (SSD & HDD)', 'STORAGE'),
(2, 'Memory Modules (RAM)', 'MEMORY'),
(3, 'Power Supply Units (SMPS)', 'POWER'),
(4, 'Motherboards & Processors', 'MOTHERBOARD'),
(5, 'Printer Cartridges & Rollers', 'PRINTER_PARTS'),
(6, 'Networking Accessories', 'NETWORK')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `spare_brands` (`id`, `name`) VALUES
(1, 'Samsung'),
(2, 'Seagate'),
(3, 'Crucial / Micron'),
(4, 'Kingston'),
(5, 'Corsair'),
(6, 'HP Enterprise'),
(7, 'Cisco')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `spare_items` (`id`, `category_id`, `brand_id`, `sku`, `name`, `capacity_spec`, `unit`, `hsn_code`, `purchase_price`, `selling_price`, `gst_rate`, `min_stock_level`, `max_stock_level`) VALUES
(1, 1, 1, 'SP-SSD-SAM-500G', 'Samsung 980 NVMe M.2 SSD', '500 GB', 'PCS', '8471', 3200.00, 4200.00, 18.00, 10, 50),
(2, 1, 1, 'SP-SSD-SAM-1TB', 'Samsung 980 PRO PCIe 4.0 NVMe SSD', '1 TB', 'PCS', '8471', 6500.00, 8500.00, 18.00, 8, 40),
(3, 1, 2, 'SP-HDD-SEA-1TB', 'Seagate BarraCuda 3.5" Internal SATA HDD', '1 TB', 'PCS', '8471', 2800.00, 3600.00, 18.00, 15, 60),
(4, 2, 3, 'SP-RAM-CRU-8GD4', 'Crucial DDR4 3200MHz Desktop RAM', '8 GB', 'PCS', '8473', 1400.00, 1950.00, 18.00, 20, 80),
(5, 2, 3, 'SP-RAM-CRU-16GD4', 'Crucial DDR4 3200MHz Desktop RAM', '16 GB', 'PCS', '8473', 2600.00, 3500.00, 18.00, 15, 60),
(6, 3, 5, 'SP-PSU-COR-550W', 'Corsair CV550 550W 80 PLUS Bronze SMPS', '550 Watt', 'PCS', '8504', 3100.00, 4100.00, 18.00, 10, 30),
(7, 5, 6, 'SP-TON-HP-88A', 'HP 88A Black Original LaserJet Toner Cartridge', 'Standard Yield', 'PCS', '8443', 2900.00, 3850.00, 18.00, 12, 40)
ON DUPLICATE KEY UPDATE `sku` = VALUES(`sku`);

-- 14. Initial Inventory & Stock Ledger
INSERT INTO `inventory` (`spare_item_id`, `warehouse_name`, `quantity`, `reserved_quantity`) VALUES
(1, 'Main Store', 25, 1),
(2, 'Main Store', 18, 0),
(3, 'Main Store', 30, 2),
(4, 'Main Store', 45, 0),
(5, 'Main Store', 35, 1),
(6, 'Main Store', 16, 0),
(7, 'Main Store', 22, 0)
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`);

INSERT INTO `inventory_transactions` (`spare_item_id`, `warehouse_name`, `transaction_type`, `reference_type`, `reference_id`, `quantity_in`, `quantity_out`, `unit_price`, `balance_after`, `remarks`) VALUES
(1, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 25, 0, 3200.00, 25, 'Initial Opening Balance Stock Seeder'),
(2, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 18, 0, 6500.00, 18, 'Initial Opening Balance Stock Seeder'),
(3, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 30, 0, 2800.00, 30, 'Initial Opening Balance Stock Seeder'),
(4, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 45, 0, 1400.00, 45, 'Initial Opening Balance Stock Seeder'),
(5, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 35, 0, 2600.00, 35, 'Initial Opening Balance Stock Seeder'),
(6, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 16, 0, 3100.00, 16, 'Initial Opening Balance Stock Seeder'),
(7, 'Main Store', 'OPENING', 'INITIAL_SEED', 'INIT-001', 22, 0, 2900.00, 22, 'Initial Opening Balance Stock Seeder');

-- 15. Suppliers
INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `mobile`, `email`, `gstin`, `address`, `city`, `state`) VALUES
(1, 'CompTech Hardware Wholesalers', 'Suresh Patel', '9892011223', 'sales@comptechhardware.com', '27AAACC4455P1Z8', 'Lamington Road, Grant Road East', 'Mumbai', 'Maharashtra'),
(2, 'Silicon Peripherals Ltd', 'Ramesh Shah', '9892099887', 'info@siliconperipherals.in', '27AAACS1122Q1Z4', 'Prime Mall, Irla, Vile Parle West', 'Mumbai', 'Maharashtra')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 16. Service Calls (Live diverse workflow states)
INSERT INTO `calls` (`id`, `call_number`, `call_type`, `priority`, `customer_id`, `location_id`, `machine_id`, `contract_id`, `problem_category_id`, `problem_id`, `caller_name`, `caller_mobile`, `caller_email`, `reported_issue`, `assigned_engineer_id`, `status`, `sla_response_deadline`, `sla_resolution_deadline`, `created_at`) VALUES
(1, 'CALL-2026-000001', 'AMC', 'HIGH', 1, 1, 2, 1, 2, 3, 'Rajesh K (Accounts)', '9811122233', 'ithead@techcorp.com', 'Machine boots with clicking noise and throws SMART drive warning. Accounting software unable to write ledger.', 5, 'ARRIVED', DATE_ADD(NOW(), INTERVAL -2 HOUR), DATE_ADD(NOW(), INTERVAL 4 HOUR), DATE_ADD(NOW(), INTERVAL -2 HOUR)),
(2, 'CALL-2026-000002', 'AMC', 'CRITICAL', 1, 1, 1, 1, 2, 2, 'Vikram Mehta', '9811122233', 'ithead@techcorp.com', 'Primary Database Server memory error on slot B2. System restarted unexpectedly.', 5, 'ON_THE_WAY', DATE_ADD(NOW(), INTERVAL -1 HOUR), DATE_ADD(NOW(), INTERVAL 3 HOUR), DATE_ADD(NOW(), INTERVAL -1 HOUR)),
(3, 'CALL-2026-000003', 'PAID', 'MEDIUM', 4, 4, NULL, NULL, 5, 7, 'Arun Nair (Logistics)', '9844455566', 'admin@metrologistics.com', 'HP LaserJet Paper feed roller is slipping, pulling multiple pages continuously.', 6, 'NEW', DATE_ADD(NOW(), INTERVAL 2 HOUR), DATE_ADD(NOW(), INTERVAL 10 HOUR), NOW()),
(4, 'CALL-2026-000004', 'PM', 'LOW', 2, 3, 5, 2, 6, 8, 'Priya Deshmukh', '9822233344', 'cto@zenithfin.com', 'Q1 Scheduled Quarterly Preventive Maintenance inspection for trading workstations.', 7, 'ASSIGNED', DATE_ADD(NOW(), INTERVAL 4 HOUR), DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
ON DUPLICATE KEY UPDATE `call_number` = VALUES(`call_number`);

-- Call Status History
INSERT INTO `call_status_history` (`call_id`, `old_status`, `new_status`, `changed_by`, `remarks`, `latitude`, `longitude`) VALUES
(1, NULL, 'NEW', 4, 'Call registered via Fast Search Call Center desk', 19.1764, 72.8347),
(1, 'NEW', 'ASSIGNED', 3, 'Assigned to Senior Hardware Engineer Rahul Sharma', NULL, NULL),
(1, 'ASSIGNED', 'ENGINEER_ACCEPTED', 5, 'Accepted on Engineer Mobile App', 19.1750, 72.8340),
(1, 'ENGINEER_ACCEPTED', 'ON_THE_WAY', 5, 'En route to Mindspace HQ', 19.1755, 72.8342),
(1, 'ON_THE_WAY', 'ARRIVED', 5, 'Arrived at client site, starting diagnosis', 19.1764, 72.8347);

-- 17. Spare Request for Call 1
INSERT INTO `spare_requests` (`id`, `request_number`, `call_id`, `engineer_id`, `machine_id`, `status`, `approval_remarks`, `approved_by`, `approved_at`) VALUES
(1, 'REQ-2026-000001', 1, 5, 2, 'APPROVED', 'Approved under Comprehensive AMC Contract CON-2026-000001 (Zero Charge)', 2, NOW())
ON DUPLICATE KEY UPDATE `request_number` = VALUES(`request_number`);

INSERT INTO `spare_request_items` (`spare_request_id`, `spare_item_id`, `requested_qty`, `approved_qty`, `issued_qty`, `is_chargeable`, `unit_price`, `status`) VALUES
(1, 1, 1, 1, 1, 0, 0.00, 'ISSUED')
ON DUPLICATE KEY UPDATE `requested_qty` = VALUES(`requested_qty`);

-- 18. PM Checklists
INSERT INTO `pm_checklists` (`id`, `asset_type_id`, `title`, `is_active`) VALUES
(1, 1, 'Standard Desktop Computer 10-Point PM Checklist', 1),
(2, 2, 'Commercial Laptop Diagnostic PM Checklist', 1),
(3, 3, 'Enterprise Server & RAID Health Inspection Checklist', 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `pm_checklist_items` (`checklist_id`, `task_description`, `sort_order`, `is_mandatory`) VALUES
(1, 'Clean interior dust from CPU heatsink, SMPS fan and cabinet air filters using blower', 1, 1),
(1, 'Inspect motherboard capacitors and SMPS voltages (12V, 5V, 3.3V rails)', 2, 1),
(1, 'Run memory test (Windows Memory Diagnostic / MemTest) for bit errors', 3, 1),
(1, 'Inspect SSD/HDD SMART health parameters and test read/write surface', 4, 1),
(1, 'Verify CPU/GPU thermal temperatures under load (Check if re-pasting required)', 5, 1),
(1, 'Verify OS patch levels, pending Windows updates and Antivirus signature status', 6, 1),
(1, 'Check LAN cabling, RJ45 jack clip and network throughput', 7, 1),
(1, 'Inspect connected UPS voltage output and battery backup duration test', 8, 1);
