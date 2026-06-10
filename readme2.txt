คุณคือ Senior System Architect และ Full Stack Laravel Developer ผู้เชี่ยวชาญด้านการออกแบบระบบบริหารจัดการแรงงานต่างด้าวระดับ Enterprise

เป้าหมาย:
สร้างระบบ “บริหารจัดการเอกสารแรงงานต่างด้าว” แบบครบวงจร ด้วย:

- Laravel 12
- Filament 3.3
- MySQL 8
- TailwindCSS

ระบบต้องมีความ Professional, Modern, Premium UI/UX และรองรับการใช้งานจริงในระดับบริษัทแรงงานต่างด้าว

==================================================
SYSTEM OVERVIEW
==================================================

ระบบนี้คือ:

Foreign Worker ERP + Workflow Management System

รวมความสามารถ:

- Worker Management
- Employer CRM
- Document Management System (DMS)
- Visa / Work Permit Workflow
- Payment Tracking
- Expiry Alert System
- Employer Portal
- Staff Operation System
- Dashboard Analytics
- Activity Timeline
- Multi User / Multi Company

==================================================
SYSTEM ARCHITECTURE
==================================================

# 1. USERS

เก็บผู้ใช้งานทั้งหมดของระบบ เช่น:

- Super Admin
- Admin
- Staff
- HR บริษัท
- Accounting
- Manager

Table: users

Fields:
- id
- name
- email
- password
- role
- email_verified_at
- remember_token
- created_at
- updated_at

==================================================

# 2. EMPLOYERS

เก็บข้อมูลบริษัทลูกค้า

Table: employers

Fields:
- id
- company_code
- company_name
- contact_name
- phone
- email
- tax_id
- address
- logo
- is_active
- notes
- created_at
- updated_at

==================================================

# 3. EMPLOYER_USER (Pivot Table)

เชื่อมผู้ใช้งานกับบริษัท

รองรับ:
1 บริษัท → หลาย User

Table: employer_user

Fields:
- id
- employer_id
- user_id
- role
- created_at
- updated_at

Roles:
- owner
- hr
- accounting
- viewer

==================================================

# 4. NATIONALITIES

Master Data สัญชาติ

Table: nationalities

Fields:
- id
- name_th
- name_en
- country_code
- icon_flag
- is_active
- created_at
- updated_at

==================================================

# 5. WORKERS

หัวใจหลักของระบบ

Table: workers

Fields:
- id
- employer_id
- nationality_id

# ชื่อไทย
- prefix_th
- first_name_th
- last_name_th

# ชื่ออังกฤษ
- prefix_en
- first_name_en
- last_name_en

# ข้อมูลส่วนตัว
- birth_date
- gender

# Passport
- passport_number
- passport_expiry

# Work Permit
- wp_number
- wp_expiry

# Visa
- visa_expiry

# 90 Days Report
- report_90_days_due

# Files
- passport_file
- wp_file
- visa_file
- report_90_days_file

# Status
- is_active

- created_at
- updated_at

==================================================

# 6. DOCUMENT_MASTERS

Master เอกสารกลาง

Table: document_masters

Fields:
- id
- name
- code
- description
- is_active
- created_at
- updated_at

ตัวอย่าง:
- Passport
- Visa
- Work Permit
- TM30
- Photo

==================================================

# 7. WORKER_DOCUMENTS

เอกสารของแรงงาน

Table: worker_documents

Fields:
- id
- worker_id
- document_master_id
- file_path
- expiry_date
- note
- created_at
- updated_at

==================================================

# 8. SERVICES

ประเภทบริการ

Table: services

Fields:
- id
- name
- code
- description
- alert_days_before_expiry
- is_active
- created_at
- updated_at

ตัวอย่าง:
- Visa Extension
- Work Permit Renewal
- 90 Days Report
- New Registration

==================================================

# 9. SERVICE_CHECKLISTS

Template Checklist ของแต่ละบริการ

Table: service_checklists

Fields:
- id
- service_id
- document_name
- sort_order
- is_required
- created_at
- updated_at

==================================================

# 10. JOB_ORDERS

Workflow งาน

Table: job_orders

Fields:
- id
- job_number
- employer_id
- worker_id
- service_id
- assigned_user_id

# Financial
- service_fee
- paid_amount
- payment_status

# Workflow
- status
- priority

# Dates
- due_date
- started_at
- completed_at

# Notes
- notes

- created_at
- updated_at

payment_status:
- pending
- partial
- paid
- cancelled

status:
- pending
- processing
- waiting_document
- approved
- completed
- cancelled
- rejected

priority:
- low
- medium
- high
- urgent

==================================================

# 11. JOB_ORDER_CHECKLISTS

Checklist เอกสารของงาน

Table: job_order_checklists

Fields:
- id
- job_order_id
- document_master_id
- is_required
- status
- received_at
- attached_file_path
- verified_by
- verified_at
- remark
- created_at
- updated_at

status:
- pending
- received
- verified
- rejected
- missing

==================================================

# 12. JOB_ORDER_PAYMENTS

การชำระเงิน

Table: job_order_payments

Fields:
- id
- job_order_id
- amount
- payment_date
- payment_method
- payment_reference
- slip_path
- status
- received_by
- note
- created_at
- updated_at

payment_method:
- cash
- transfer
- promptpay
- credit_card

status:
- pending
- verified
- rejected

==================================================

# 13. JOB_ORDER_LOGS

Timeline Activity

Table: job_order_logs

Fields:
- id
- job_order_id
- user_id
- action
- description
- created_at

==================================================

# 14. NOTIFICATIONS

ระบบแจ้งเตือน

Table: notifications

Fields:
- id
- user_id
- title
- message
- is_read
- created_at

==================================================

# 15. ACTIVITY_LOGS

Audit Log

Table: activity_logs

Fields:
- id
- user_id
- action
- model_type
- model_id
- description
- created_at

==================================================
SYSTEM FEATURES
==================================================

ระบบต้องรองรับ:

✅ Multi Company
✅ Multi User
✅ Employer Portal
✅ Staff Panel
✅ Role & Permission
✅ File Upload
✅ Document Preview
✅ Expiry Alerts
✅ Dashboard Analytics
✅ Payment Tracking
✅ Workflow Tracking
✅ Activity Timeline
✅ Search & Filters
✅ Status Badges
✅ Responsive UI
✅ Premium Admin Dashboard

==================================================
FILAMENT REQUIREMENTS
==================================================

ใช้ Filament 3.3

ทุก Resource ต้องมี:

- Professional Form Layout
- Section
- Grid
- Tabs
- Relation Manager
- Filters
- Searchable Table
- Status Badge
- File Upload
- Soft Delete
- Navigation Group
- Navigation Badge
- Table Actions
- Bulk Actions

==================================================
UI/UX REQUIREMENTS
==================================================

Design:
- Modern
- Premium
- Enterprise Style
- Luxury Dashboard
- Clean Layout
- Minimal but Professional

ใช้:
- TailwindCSS
- Heroicons
- Filament Widgets
- Stats Overview
- Charts
- Progress Indicators

==================================================
DEVELOPMENT REQUIREMENTS
==================================================

ช่วยพัฒนาแบบ Step-by-Step

ทุกขั้นตอนต้องอธิบาย:
- ทำอะไร
- ทำไมต้องทำ
- Best Practice
- Professional Approach
- Laravel Best Structure

ต้องช่วยสร้าง:
- Migration
- Model
- Relationships
- Filament Resources
- Forms
- Tables
- Widgets
- Notifications
- Policies
- Dashboard
- Workflow

==================================================
IMPORTANT
==================================================

ระบบนี้ไม่ใช่ CRUD ธรรมดา

แต่เป็น:

Foreign Worker ERP + Document Workflow Platform

ต้องออกแบบแบบ Production Ready
รองรับการขยายระบบในอนาคต
และใช้ Architecture ระดับ Enterprise