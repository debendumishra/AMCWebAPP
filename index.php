<?php
/**
 * ServicEngine Front Controller Entry Point
 */

defined('APP_INIT') or define('APP_INIT', true);

// Include Core Configurations
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';

// Include Core Classes
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/NumberGenerator.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Router.php';

// Include Base Models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Setting.php';

// Initialize Session
Auth::init();

// ==========================================
// DEFINE ROUTE MAPPINGS
// ==========================================

// Root Landing Route
Router::get('', 'AuthController@showLogin');
Router::get('login', 'AuthController@showLogin');
Router::post('login', 'AuthController@processLogin');
Router::get('captcha', 'AuthController@captcha');
Router::get('captcha-code', 'AuthController@getCaptchaCode');
Router::get('logout', 'AuthController@logout');
Router::get('forgot-password', 'AuthController@showForgotPassword');

// Installation & Database Setup
Router::get('install', 'InstallController@index');
Router::post('install/migrate', 'InstallController@runMigration');
Router::post('install/purge', 'InstallController@purgeDemoData');

// Dashboards
Router::get('dashboard', 'DashboardController@index');

// Fast Search AJAX
Router::get('ajax/search', 'CallController@fastSearch');
Router::get('ajax/search-machines', 'MachineController@ajaxSearch');
Router::get('ajax/customer-locations', 'CustomerController@ajaxLocations');

// Customers & Locations
Router::get('customers', 'CustomerController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('customers/create', 'CustomerController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('customers/create', 'CustomerController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('customers/{id}', 'CustomerController@show', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('customers/{id}/edit', 'CustomerController@update', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);
Router::post('customers/{id}/locations', 'CustomerController@addLocation', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);

// Machines / IT Assets & QR Codes
Router::get('machines', 'MachineController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER, ROLE_ENGINEER]);
Router::get('machines/create', 'MachineController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('machines/create', 'MachineController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('machines/{id}', 'MachineController@show');
Router::get('machines/qr/{token}', 'MachineController@viewByQrToken');

// AMC Contracts
Router::get('contracts', 'ContractController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('contracts/create', 'ContractController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('contracts/create', 'ContractController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('contracts/{id}', 'ContractController@show', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('contracts/{id}/assign-machines', 'ContractController@assignMachines', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);

// Service Calls & Dispatch
Router::get('calls', 'CallController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('calls/create', 'CallController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('calls/create', 'CallController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('calls/{id}', 'CallController@show');
Router::get('calls/{id}/print-jobcard', 'CallController@printJobCard');
Router::post('calls/{id}/status', 'CallController@updateStatus', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_ENGINEER]);
Router::post('calls/{id}/assign', 'CallController@assignEngineer', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('calls/{id}/message', 'CallController@addMessage');

// Field Engineer Workflows
Router::get('engineer', 'EngineerController@index', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/calls', 'EngineerController@calls', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/calls/create', 'EngineerController@createCall', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::post('engineer/calls/create', 'EngineerController@storeCall', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/calls/{id}', 'EngineerController@callDetail', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/calls/{id}/print-jobcard', 'EngineerController@printJobCard', [ROLE_ENGINEER, ROLE_SUPER_ADMIN, ROLE_ADMIN]);
Router::post('engineer/calls/{id}/journey', 'EngineerController@updateJourney', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::post('engineer/calls/{id}/request-spare', 'EngineerController@requestSpare', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/calls/{id}/jobcard', 'EngineerController@jobCard', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::post('engineer/calls/{id}/submit-jobcard', 'EngineerController@submitJobCard', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/spares', 'EngineerController@spares', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);
Router::get('engineer/history', 'EngineerController@history', [ROLE_ENGINEER, ROLE_SUPER_ADMIN]);

// Inventory & Spare Requisitions
Router::get('inventory', 'InventoryController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('inventory/create', 'InventoryController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('inventory/create', 'InventoryController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('inventory/requisitions', 'InventoryController@requisitions', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('inventory/requisitions/{id}/approve', 'InventoryController@approveRequisition', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('inventory/requisitions/{id}/reject', 'InventoryController@rejectRequisition', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('inventory/ledger', 'InventoryController@ledger', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);

// Purchases & Suppliers
Router::get('purchases', 'PurchaseController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('purchases/create', 'PurchaseController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('purchases/create', 'PurchaseController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('purchases/suppliers', 'PurchaseController@suppliers', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('purchases/suppliers', 'PurchaseController@storeSupplier', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('purchases/{id}', 'PurchaseController@show', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);

// Billing, Invoicing & GST
Router::get('billing', 'BillingController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::get('billing/create', 'BillingController@create', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('billing/create', 'BillingController@store', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('billing/{id}/print', 'BillingController@printInvoice');
Router::post('billing/{id}/payment', 'BillingController@recordPayment', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('billing/{id}', 'BillingController@show', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);

// Preventive Maintenance (PM)
Router::get('pm', 'PMController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('pm/sync-schedules', 'PMController@syncSchedules', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('pm/generate-calls', 'PMController@generateDueCalls', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::post('pm/generate-single/{id}', 'PMController@generateSingleCall', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('pm/checklists', 'PMController@checklists', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT, ROLE_CALL_CENTER]);
Router::post('pm/checklists', 'PMController@storeChecklist', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);

// Reports & Analytics
Router::get('reports', 'ReportController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN, ROLE_SUPPORT]);
Router::get('reports/export', 'ReportController@export', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);

// Customer Self-Service Portal
Router::get('customer', 'CustomerPortalController@index', [ROLE_CUSTOMER]);
Router::get('customer/machines', 'CustomerPortalController@machines', [ROLE_CUSTOMER]);
Router::get('customer/calls', 'CustomerPortalController@calls', [ROLE_CUSTOMER]);
Router::get('customer/calls/create', 'CustomerPortalController@createCall', [ROLE_CUSTOMER]);
Router::post('customer/calls/create', 'CustomerPortalController@storeCall', [ROLE_CUSTOMER]);
Router::get('customer/invoices', 'CustomerPortalController@invoices', [ROLE_CUSTOMER]);
Router::get('customer/calls/{id}', 'CustomerPortalController@callDetail', [ROLE_CUSTOMER]);

// System Settings & Masters
Router::get('settings', 'SettingsController@index', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);
Router::post('settings/update', 'SettingsController@update', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);
Router::get('settings/masters', 'SettingsController@masters', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);
Router::post('settings/masters', 'SettingsController@saveMaster', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);
Router::get('settings/audit', 'SettingsController@auditLogs', [ROLE_SUPER_ADMIN, ROLE_ADMIN]);

// Dispatch Request
Router::dispatch();
