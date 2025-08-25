# 🛠️ Dev Notes for What's Next (Legacy PHP App)

This file is a living document designed to help orient developers (human or AI) to the legacy codebase. It includes structure, commentary, and behavioral summaries to assist with updates, debugging, and modernization.


------------------------------------------------------------------------------------------------------------------------------

## 📋 Table of Contents

- [🧠 Project Abstract](#project-abstract)
- [📂 Admin Pages (`admin/`)](#admin-pages-admin)
  - [Overview](#overview)
  - [Features](#features)
  - [Notes](#notes)
- [🔑 Class: Session](#class-session)
  - [Purpose](#purpose)
  - [Key Methods](#key-methods)
  - [Notes](#notes)
- [👥 Admin Clients Page](#admin-clients-page)
  - [Purpose](#purpose)
  - [Flow](#flow)
- [🧑‍💼 Admin Agents Page](#admin-agents-page)
  - [Purpose](#purpose)
  - [Filtering](#filtering)
- [📄 Admin Conditions Page](#admin-conditions-page)
  - [Purpose](#purpose)
  - [Features](#features)
- [✅ Usage of Conditions](#usage-of-conditions)
  - [Purpose](#purpose)
  - [Key Concepts](#key-concepts)
  - [Example Rendering](#example-rendering)
  - [⏱️ `admin/timeline_items.php` – Timeline Items Manager for Templates](#admintimeline_itemsphp--timeline-items-manager-for-templates)
- [📂 Pages Section (`pages/`)](#pages-section-pages)
  - [📄 `pages/content.php`](#pagescontentphp)
  - [📄 Agent Pages](#agent-pages)
  - [📄 Coordinator Pages](#coordinator-pages)
  - [📄 User Pages](#user-pages)
- [🔐 Admin Session Bootstrap: `include/_admin.php`](#admin-session-bootstrap-include_adminphp)
- [🧰 Bootstrap File: `include/common.php`](#bootstrap-file-includecommonphp)
- [🧱 Core Classes (`include/classes/`)](#core-classes-includeclasses)
- [⚙️ Core Libraries (`include/lib/`)](#core-libraries-includelib)
- [⏰ Scheduled Tasks (`cron/`)](#scheduled-tasks-cron)
- [🧩 Shared Page Modules (`/modules/`)](#shared-page-modules-modules)
- [📤 Uploads](#uploads)
- [📧 Templates / Messaging](#templates--messaging)
- [🎨 Stylesheets (`/css/`)](#stylesheets-css)
- [🧠 JavaScript (`/js/`)](#javascript-js)
- [🧱 Legacy Patterns & Gotchas](#legacy-patterns--gotchas)
- [🧮 Database Schema Overview](#database-schema-overview)



------------------------------------------------------------------------------------------------------------------------------


## 🧠 Project Abstract

*What’s Next* is a web-based SaaS platform that streamlines the real estate transaction process for agents, clients (buyers/sellers), coordinators, and brokers. It functions as a compliance-friendly transaction tracker and collaboration hub, offering a structured, step-by-step roadmap for every deal. There is a long checklist of items that details all the steps that both agent and clients need to complete for a successfull transaction.  Some items are not date specific and are before the property goes under contract. Once a property goes under cotnract, the website displays the checklist items and their due dates. Agents and clients check off each item that they complete so they can track their progress throughout the process and transaction.  

### 👩‍💼 For Agents
- Assign clients to customizable transaction templates composed of editable timeline items (e.g., “Submit Deposit,” “Order Appraisal”).
- Track client progress via live timelines with due dates, conditions, and contract-driven logic.
- Receive alerts and manage multiple transactions through a role-based dashboard.

### 🧾 For Clients
- View a clear, chronological timeline of required steps with checkboxes, file uploads, and explanatory tooltips.
- Automatically receive email/SMS reminders for upcoming or overdue items.
- Export timelines to iCal-compatible calendars.

### 🤝 For Coordinators & Admins
- Coordinators can oversee client timelines and assist agents with updates or messaging.
- Admins manage system-wide templates, conditions, content blocks, and agents.
- Built-in activity logs and cron-based automation support compliance and task reminders.

### 🔧 Technical Highlights
- Built in legacy PHP using custom database abstraction (`DBRowSetEX`) for dynamic form rendering, pagination, and filtering.
- Role-specific modules (admin, agent, coordinator, user) provide modular separation.
- jQuery-based frontend with drag-and-drop sorting, progress indicators, autocomplete, and form interactivity.
- Templated content and emails, with content stored in a CMS-style `content` table.
- Cron jobs automate overdue reminders, cleanup, and feature-based messaging.
- Authentication is session-based, and conditional logic drives visibility of timeline items based on contract dates and user-specific conditions.

### 🧱 Real-World Workflow
- A broker or admin signs up and adds agents to the system.
- Agents onboard clients and assign them a transaction template (e.g., “Standard Home Purchase”).
- Clients progress through the transaction steps; agents and coordinators monitor and update timelines.
- Admins can create templates, define conditions (e.g., “if VA loan, show termite report step”), and ensure regulatory compliance across all transactions.

While the architecture is legacy, the system is actively used in production, modularly designed, and purpose-built for real estate transaction management with extensibility in mind.



------------------------------------------------------------------------------------------------------------------------------



## 📂 Admin Pages (`admin/`)



📄 `include/ex/dbrowset_ex.php`

### Overview

- Custom legacy extension of `DBRowSet`, purpose-built to manage table rows for CRUD via PHP backend + HTML forms.
- Reused across admin views like `clients.php`, `agents.php`, etc.
- Abstracts:
  - Sorting (via session and URL GET params)
  - Pagination
  - Multi-upload
  - Drag-and-drop sorting (optional)
  - HTML rendering and inline editing of list entries

### Features

- `Header()`: Renders sortable table column headers.
- `Paginate()`: Navigation links: first, prev, next, last.
- `DrawPages()`: Pagination visual block (numeric page links).
- `Edit()`: Full rendering/editing of items in list.
- `MultiUpload()`, `jMultiUpload()`: AJAX file upload support.
- `ProcessAction()`: Central request handler for edit/delete actions.

### Notes

- Old-school PHP: mixes logic and rendering.
- Session-based sorting and paging.
- Often used in conjunction with `SetHTML()` for custom section headers.

------------------------------------------------------------------------------------------------------------------------------

## 🔑 Class: Session

📄 `include/lib/session.php`

### Purpose

- Lightweight wrapper for `$_SESSION`, with convenience functions.
- Manages session lifecycle and key-based access.
- Ensures session is started (`Start()`) before interacting.

### Key Methods

- `Session::Start()` → starts PHP session if not already started.
- `Session::Get($key)` / `Set($key, $val)` → get/set session data.
- `Session::Dump()` → debug helper to `var_dump($_SESSION)`.
- `Session::GetID()` / `SetID()` → access session ID directly.
- `Session::GetIDName()` / `SetIDName()` → name of the session cookie.

### Notes

- Used by `DBRowSetEX` for storing:
  - Sort field
  - Sort order
  - Pagination start index

------------------------------------------------------------------------------------------------------------------------------

## 👥 Admin Clients Page

📄 `admin/clients.php`

### Purpose

- Backend admin interface for managing a specific agent's clients (buyers/sellers).
- Lists active users associated with the selected agent.

### Flow

1. Checks login status via `$admin->IsLoggedIn()`.
2. Creates an `agent` object based on `agent_id` from the GET request.
3. Builds `$where` clause to filter users by `agent_id` and `user_active = 1`.
4. Creates a `DBRowSetEX` list of `users`:
   - 5-column layout.
   - Configures HTML headers via `SetHTML()`.
   - Defaults to 1 new blank user row.
5. Enables row and button highlight effects.
6. Calls `$list->Edit()` to draw the editable user list (add/edit/delete users).
7. Provides "Back to All Agents" link.

------------------------------------------------------------------------------------------------------------------------------

## 🧑‍💼 Admin Agents Page

📄 `admin/agents.php`

### Purpose

- Displays a searchable, filterable, editable list of all active/inactive agents.
- Admins can:
  - View agent details (name, email, last login)
  - Filter agents by name or status
  - Add/edit agents using the `DBRowSetEX` inline editor

### Filtering

- Stores filters in session:
  - `manage_agent_type` (Active / Inactive / All)
  - `manage_agent_name` (search term)
- Filters are applied using a dynamic `WHERE` clause.

------------------------------------------------------------------------------------------------------------------------------

## 📄 Admin Conditions Page

📄 `admin/conditions.php`

### Purpose

- Backend interface to manage a master list of **Conditions** (likely contract clauses or checklists).
- Each condition has:
  - an order
  - a label
  - a display string
  - a default state
- Admins can create, edit, reorder, and highlight conditions.

### Features

- Uses `DBRowSetEX` to render a sortable, editable list.
- Default values (`condition_default`) used to prepopulate user records.
- Likely referenced dynamically using `condition_id` in user-facing modules.


------------------------------------------------------------------------------------------------------------------------------

## ✅ Usage of Conditions

📄 Related files:  
- `admin/conditions.php`  
- Possibly `admin/users.php`, `timeline.php`, or `user_edit.php`

### Purpose

- The `conditions` table defines customizable checkboxes or dropdowns—likely for compliance, contract clauses, or task tracking.
- Dynamically rendered in admin/user forms and stored per-user.

### Key Concepts

- **Dynamic Field Injection**: Each condition is rendered as a dropdown (`Yes/No`) using `user_condition_<id>` naming.
- **Admin-Driven Config**: Conditions can be added without modifying database schema or front-end.
- **Default Values**: New users are initialized with `condition_default` values from the condition list.

### Example Rendering

php
<?php include('modules/footer_scripts.php');?>
<?php include('../modules/footer_scripts.php');?>




### ⏱️ `admin/timeline_items.php` – Timeline Items Manager for Templates

**Purpose:**  
Manages individual *timeline items* (steps/tasks) within a specific transaction template.

**How It Works:**
- Tied to a specific `template_id` passed via `$_GET`
- Uses the `template` class to:
  - Load template details
  - Retrieve its name for display
- Targets global templates (`agent_id = 0`) only
- Displays only `timeline_item_active = 1`

**DB Listing:**
- Table: `timeline_items`
- Primary Key: `timeline_item_id`
- Class: `DBRowSetEX`
- Sort Field: `timeline_item_order`
- New rows auto-assigned order and `template_id`

**Displayed Columns:**
- ORDER
- HEADLINE
- TIMING
- CONDITIONS
- MODIFIED
- ACTION

**UI/UX Details:**
- Page title: _"Manage Timeline Items For {template_name}"_
- Allows creation of 1 new item at a time (`num_new = 1`)
- Drag-and-drop ordering enabled (`DROPSORT`)
- Buttons and rows highlighted visually

**Additional Admin Tools:**
Uses a dummy agent (`id=0`, flagged as ADMIN) to render auxiliary editing components:
- `CustomCSS()` – Loads default admin styles
- `EditTemplateIntro()` – Editable template intro section
- `AgentTools()` – Side tool list (desktop)
- `AgentToolsButtons()` – Control buttons (desktop)
- `AgentToolsXS()` – Mobile-friendly tools
- `EditTimeline()` – Visual timeline editor block
- `DrawFlares()` – Likely renders visual indicators or tooltips
- Sidebar area present but not populated (`EditSidebar()` commented out)

**Navigation:**
- Includes a back-link: `Back To Templates`


------------------------------------------------------------------------------------------------------------------------------


## 📂 Pages Section (`pages/`)


### 📄 `pages/content.php`

**Purpose:**  
Serves as the dynamic front-end content renderer. It loads CMS-style page content based on `content_id`, `content_area`, `content_url`, or `content_url_slug`, then displays it using the `content` class.

**Key Features:**
- Parses multiple GET parameters: `content_id`, `content_area`, `content_url`, `content_url_slug`
- Redirects to `content_external_url` if one exists
- Uses the `content` class to:
  - Fetch and apply SEO metadata
  - Display banner/header with `DisplayBanner()`
  - Render main content via `Display()`
- Integrates layout components (`head.php`, `header.php`, `nav.php`, `footer.php`)
- Conditionally hides the banner based on the `content_file` field

------------------------------------------------------------------------------------------------------------------------------


### 📄 `pages/agents/activity_log.php`

**Purpose:**  
Displays logs of admin/user activity.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/chat.php`

**Purpose:**  
Interface for chat or messaging system.

**Key Features:**
- Basic admin utility.


### 📄 `pages/agents/demo-chat.php`

**Purpose:**  
Admin interface for demo or test purposes.

**Key Features:**
- Processes user input via form or POST.
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/demo.php`

**Purpose:**  
Admin interface for demo or test purposes.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/edit_timeline.php`

**Purpose:**  
Admin interface for editing specific records or data fields.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).
- Contains custom rendering or admin tools logic.


### 📄 `pages/agents/edit_user.php`

**Purpose:**  
Admin interface for editing specific records or data fields.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).
- Contains custom rendering or admin tools logic.


### 📄 `pages/agents/edit_user_dates.php`

**Purpose:**  
Admin interface for editing specific records or data fields.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/ical.php`

**Purpose:**  
Exports or integrates with iCal calendar feeds.

**Key Features:**
- Basic admin utility.


### 📄 `pages/agents/index.php`

**Purpose:**  
Likely the admin panel entry point or dashboard.

**Key Features:**
- Processes user input via form or POST.
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/optout.php`

**Purpose:**  
Handles unsubscribe or opt-out actions.

**Key Features:**
- Basic admin utility.


### 📄 `pages/agents/past.php`

**Purpose:**  
Displays past users, transactions, or logs.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/register.php`

**Purpose:**  
Handles user registration workflows.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/register_iframe.php`

**Purpose:**  
Handles user registration workflows.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/register_iframe_demo.php`

**Purpose:**  
Admin interface for demo or test purposes.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/reset.php`

**Purpose:**  
Handles password reset functionality.

**Key Features:**
- Basic admin utility.


### 📄 `pages/agents/settings.php`

**Purpose:**  
Page for editing user or system-wide settings.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/templates.php`

**Purpose:**  
Displays and manages templates, likely used across agents or workflows.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/test.php`

**Purpose:**  
Developer test page, not part of production logic.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).
- Contains custom rendering or admin tools logic.


### 📄 `pages/agents/timeline_items.php`

**Purpose:**  
Manages timeline-related functionality such as task items or visual editors.

**Key Features:**
- Uses `DBRowSetEX` for table management.
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/user_contacts.php`

**Purpose:**  
Admin page to manage user-specific data, timelines, or contacts.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/user_timeline.php`

**Purpose:**  
Manages timeline-related functionality such as task items or visual editors.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/users.php`

**Purpose:**  
Admin page to manage user-specific data, timelines, or contacts.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/vendors.php`

**Purpose:**  
Interface for managing vendors or third-party service providers.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).


### 📄 `pages/agents/view_tasks.php`

**Purpose:**  
Displays tasks or to-dos assigned to users.

**Key Features:**
- Includes WYSIWYG editor (TinyMCE).

------------------------------------------------------------------------------------------------------------------------------

## 📂 Coordinator Pages (`pages/coordinators/`)

### 📄 `pages/coordinators/activity_log.php`

**Purpose:**  
Displays activity logs tied to coordinators.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/agents.php`

**Purpose:**  
Coordinator view of agents, filtered or editable.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/edit_timeline.php`

**Purpose:**  
Allows coordinators to edit user or timeline details.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.
- Implement advanced rendering or coordinator tools.


### 📄 `pages/coordinators/edit_user.php`

**Purpose:**  
Allows coordinators to edit user or timeline details.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.
- Implement advanced rendering or coordinator tools.


### 📄 `pages/coordinators/edit_user_dates.php`

**Purpose:**  
Allows coordinators to edit user or timeline details.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/ical.php`

**Purpose:**  
Handles calendar feed integrations (iCal).

**Key Features:**
- Basic coordinator utility.


### 📄 `pages/coordinators/index.php`

**Purpose:**  
Main landing or dashboard page for coordinators.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/optout.php`

**Purpose:**  
Handles coordinator opt-out or unsubscribe logic.

**Key Features:**
- Basic coordinator utility.


### 📄 `pages/coordinators/past.php`

**Purpose:**  
Shows past records, clients, or logs.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/reset.php`

**Purpose:**  
Password reset interface.

**Key Features:**
- Basic coordinator utility.


### 📄 `pages/coordinators/settings.php`

**Purpose:**  
Coordinator settings and preferences page.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/templates.php`

**Purpose:**  
Used by coordinators to manage reusable templates.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/user_timeline.php`

**Purpose:**  
Manages timeline-related components for coordinator workflows.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


### 📄 `pages/coordinators/vendors.php`

**Purpose:**  
Interface to manage vendors linked to coordinators.

**Key Features:**
- Includes TinyMCE for WYSIWYG editing.


------------------------------------------------------------------------------------------------------------------------------


## 👤 User Pages (`pages/users/`)

### 📄 `pages/users/ical.php`

**Purpose:**  
Provides iCal calendar feed integration.

**Key Features:**
- Basic user utility.


### 📄 `pages/users/index.php`

**Purpose:**  
Likely the user's dashboard or main entry point.

**Key Features:**
- Implement user-specific tools or rendering.


### 📄 `pages/users/optout.php`

**Purpose:**  
Manages email or notification opt-out actions.

**Key Features:**
- Basic user utility.


### 📄 `pages/users/reset.php`

**Purpose:**  
Handles user password reset flows.

**Key Features:**
- Basic user utility.


### 📄 `pages/users/settings.php`

**Purpose:**  
Allows users to manage personal settings.

**Key Features:**
- Implement user-specific tools or rendering.


### 📄 `pages/users/timeline.php`

**Purpose:**  
Displays or manages the logged-in user's timeline.

**Key Features:**
- Implement user-specific tools or rendering.


------------------------------------------------------------------------------------------------------------------------------

## 🔐 Admin Session Bootstrap: `include/_admin.php`

This lightweight file is included in all `admin/` pages to initialize and authenticate the admin user.

### Responsibilities:
- Creates a new `admin` object using the current session ID:  
  `new admin(Session::Get('admin_id'))`
- Calls `$admin->ProcessLogin()` to:
  - Validate current login state
  - Redirect to login if session is invalid
  - Potentially refresh session or permissions

This file centralizes admin login handling, ensuring consistent security across the admin interface.


## 🧰 Bootstrap File: `include/common.php`

This file is included in nearly every PHP page and acts as the core bootstrap for the entire application. Its responsibilities include:

- **Error handling**: Enables basic error reporting (`E_ERROR | E_PARSE`) and optionally full error output during development.
- **Legacy global variables**: Reconstructs `$HTTP_GET_VARS`, `$HTTP_POST_VARS`, and `$HTTP_SERVER_VARS` from superglobals for compatibility with older code patterns.
- **Variable extraction**: Dynamically registers `$_GET` and `$_POST` keys as individual PHP variables (`$$k = $v;`).
- **Library includes**: Loads essential shared libraries:
  - `lib.php.inc` (core helpers)
  - `base_calendar.php`
  - `captcha.php`
  - `fupload.php`
- **Environment detection**: Sets `$__DEV__` based on whether the base URL contains `dev.` — used for conditional behavior in development mode.
- **Stripe integration**: Hardcodes public and private keys for Stripe payment processing (can be abstracted to config).

This file is the heart of the legacy global context and is required by nearly all pages.




------------------------------------------------------------------------------------------------------------------------------

## 🧱 Core Classes (`include/classes/`)

### 📄 `include/classes/c_activity_log.php`

**Purpose:**
Tracks user/admin activity and logs for auditing.

### 📄 `include/classes/c_admin.php`

**Purpose:**
Handles admin session, login checks, and permission logic.

### 📄 `include/classes/c_agent.php`

**Purpose:**
Represents agent users, including linked clients and templates.

### 📄 `include/classes/c_agent_link.php`

**Purpose:**
Associates agents with coordinators or accounts.

### 📄 `include/classes/c_animation.php`

**Purpose:**
Controls frontend animations and effects.

### 📄 `include/classes/c_colors.php`

**Purpose:**
Defines and manages color assignments (branding/UI).

### 📄 `include/classes/c_condition.php`

**Purpose:**
Defines compliance/task conditions shown to users.

### 📄 `include/classes/c_conditions_to_contract_dates.php`

**Purpose:**
Maps conditions to contract dates for automation.

### 📄 `include/classes/c_conditions_to_timeline_items.php`

**Purpose:**
Maps conditions to timeline tasks for logic rules.

### 📄 `include/classes/c_configuration.php`

**Purpose:**
Global configuration object, often loaded at boot.

### 📄 `include/classes/c_contact.php`

**Purpose:**
Legacy model class for `Contact`.

### 📄 `include/classes/c_content.php`

**Purpose:**
CMS-style content object with SEO, banners, and structure.

### 📄 `include/classes/c_contract_date.php`

**Purpose:**
Represents key transaction dates (e.g. close, inspections).

### 📄 `include/classes/c_coordinator.php`

**Purpose:**
Manages coordinator-level logic and access.

### 📄 `include/classes/c_coordinator_link.php`

**Purpose:**
Associates coordinators with agents or clients.

### 📄 `include/classes/c_discount_code.php`

**Purpose:**
Tracks and applies promotional/discount codes.

### 📄 `include/classes/c_fa.php`

**Purpose:**
Font Awesome icon mapping or utilities.

### 📄 `include/classes/c_feature.php`

**Purpose:**
Flags for enabling/disabling site features or modules.

### 📄 `include/classes/c_holiday.php`

**Purpose:**
Handles definition of holidays and their effects on scheduling.

### 📄 `include/classes/c_info.php`

**Purpose:**
Generic info block—often used for tips, reminders, etc.

### 📄 `include/classes/c_info_bubble.php`

**Purpose:**
Small pop-up or hover bubble with contextual help.

### 📄 `include/classes/c_performance_log.php`

**Purpose:**
Captures backend performance events or metrics.

### 📄 `include/classes/c_sound.php`

**Purpose:**
Handles sound playback or notifications.

### 📄 `include/classes/c_template.php`

**Purpose:**
Represents a transaction template made of timeline items.

### 📄 `include/classes/c_timeline_item.php`

**Purpose:**
Defines a single step or milestone in a transaction template.

### 📄 `include/classes/c_user.php`

**Purpose:**
Main class for buyer/seller user accounts and related data.

### 📄 `include/classes/c_user_conditions.php`

**Purpose:**
Tracks which condition values each user has selected.

### 📄 `include/classes/c_user_contact.php`

**Purpose:**
Stores and manages contact info for a specific user.

### 📄 `include/classes/c_user_contract_date.php`

**Purpose:**
Tracks contract dates specific to a user/transaction.

### 📄 `include/classes/c_user_link.php`

**Purpose:**
Links users to agents or coordinators.

### 📄 `include/classes/c_user_widget.php`

**Purpose:**
Manages dynamic dashboard widgets shown to users.

### 📄 `include/classes/c_vendor.php`

**Purpose:**
Defines vendors or service providers tied to transactions.

### 📄 `include/classes/c_vendor_type.php`

**Purpose:**
Describes categories of vendors (e.g. escrow, title).

### 📄 `include/classes/c_widget.php`

**Purpose:**
Reusable widget layout/content block for dashboards.



------------------------------------------------------------------------------------------------------------------------------


## ⚙️ Core Libraries (`include/lib/`)

### ⏱ Performance / Timing

- 1 related files

### ⚙️ Auto-Code / Template Tools

- Appears to generate PHP or HTML boilerplate templates (`auto/code.php`, etc.)

### ➗ Math Helpers

- 1 related files

### 📄 XML / Parsing

- 1 related files

### 📅 Date / Time Utilities

- `date.php`, `base_calendar.php` used to calculate durations, holidays, etc.

### 📊 Graphing / Visualization

- 1 related files

### 📓 Logging / Debugging

- `logger.php` and `performance_log.php` likely custom for tracking app performance

### 📝 Form + HTML Helpers

- `form.php` and `html.php` used widely to render UI elements

### 📤 Email / PHPMailer

- Uses PHPMailer for all outgoing mail
- Includes OAuth, SMTP, POP3, and multi-language support
- `email.php` likely wraps this for easier site-wide sending

### 📦 MIME Utilities

- Helps with file headers, content types, and email MIME encoding

### 📰 RSS / Feeds

- Contains the `magpie` RSS reader library and date formatters

### 🔐 Session / Auth

- `session.php` manages login/session lifecycle
- `cookie.php` for auth persistence or tracking

### 🔤 Text Utilities

- 1 related files

### 🖼 Image Handling

- Image resizing and format conversion using ImageMagick wrappers

### 🗂 File Uploads

- Handles legacy and AJAX-based file uploads (`fUpload`, `jUpload`)
- Contains `process.php` and `process_upload.php` scripts

### 🗄 Database Abstraction

- `DB.php`, `database.php`, `dbrow.php`, `dbrowset.php` = low-level database layers

### 🗺️ Maps

- 2 related files

### 🤖 CAPTCHA / Anti-Spam

- CAPTCHA rendering and Google reCAPTCHA support

### 🧭 Navigation / Routing

- `navigation.php` handles redirects, nav logic, and breadcrumbs

### 🧰 Misc / Other

- 6 related files


------------------------------------------------------------------------------------------------------------------------------


## ⏰ Scheduled Tasks (`cron/`)

These scripts are intended to run via cron jobs (scheduled tasks), automating email/SMS reminders, cleanup, and notifications.

### 📄 `cron/due_reminders_email.php`
Sends email reminders for timeline items or conditions that are due.

### 📄 `cron/due_reminders_sms.php`
Sends SMS reminders for items that are due — likely mirrors the email version but uses a messaging gateway.

### 📄 `cron/reminders_email.php`
Sends scheduled reminder emails for upcoming events (e.g. tasks due in a few days).

### 📄 `cron/reminders_sms.php`
Sends SMS reminders for upcoming events (not just those that are already due).

### 📄 `cron/notifications.php`
Handles general system notifications — may include status changes, user mentions, or cross-role alerts.

### 📄 `cron/housekeeping.php`
Performs maintenance tasks such as:
- Auto-completing expired timeline items
- Deleting or archiving stale records
- Clearing temp files or expired sessions

### 📄 `cron/test.php`
Likely a development-only script for testing the cron system or message outputs. Not intended for production use.


------------------------------------------------------------------------------------------------------------------------------

## 🧩 Shared Page Modules (`/modules/`)

These components form the common page layout for most frontend and user-facing views. Typically included in this order:

### 📄 `modules/head.php`
Sets up the HTML `<head>` with:
- Page title, metadata, and SEO tags
- CSS includes (stylesheets, fonts)
- JavaScript libraries (jQuery, TinyMCE, etc.)
Often dynamically populated based on context or content class properties.

### 📄 `modules/header.php`
Top navigation bar and branding/header region. May adjust display based on user role or login state.

### 📄 `modules/nav.php` *(referenced but not in this folder)*
Side or top navigation included after the header — varies based on role (agent, coordinator, etc.).

### 📄 `modules/footer.php`
Site-wide footer. May include links, legal notices, or version info.

### 📄 `modules/footer_scripts.php`
Injects JavaScript to be run after the page loads. Includes:
- Initialization of plugins (e.g., WYSIWYG editors, modals)
- Form validation or client-side logic

### 📄 `modules/popup.php`
Shared modal or pop-up handler used across the app. Often contains markup for reusable overlays triggered via JS.


------------------------------------------------------------------------------------------------------------------------------

## 📤 Uploads

The `/uploads/` folder stores user-uploaded files, such as PDFs, images, or attachments related to transactions. Uploads are handled via `fUpload` and `process_upload.php` and may be linked by user ID or transaction.



------------------------------------------------------------------------------------------------------------------------------

## 🎨 Stylesheets (`/css/`)

The site uses multiple stylesheets to support global styling, responsive layouts, and editor theming.

### Global + Base Styles
- `global.css`: Core site-wide CSS styles
- `pete.css`: Custom overrides or tweaks 

### Responsive Breakpoints
- `xsmall.css`, `small.css`, `medium.css`, `large.css`: Media query-based stylesheets targeting different device sizes

### Editors + UI Libraries
- `wysiwyg_new.css`: Styles for WYSIWYG editor content (likely TinyMCE)
- `fontawesome.min.css`: FontAwesome icon font (minified)

### Calendar Styles
- `calendar.css`: Styles for timeline or calendar views used in transaction tracking


------------------------------------------------------------------------------------------------------------------------------

## 🧠 JavaScript (`/js/`)

The JavaScript layer powers dynamic UI behavior, form interactivity, and some custom effects.

### Core + Utility Scripts
- `site.js`: Primary site-wide logic (often includes initializers and handlers)
- `util.js`: General-purpose utility functions
- `object_function.js`: Likely augments JS prototypes or builds object patterns

### Forms + UI Components
- `selectbox.js`: Enhances `<select>` elements for styling or interactivity
- `drop_sort.js`: Enables drag-and-drop sorting (likely used in template/timeline builder)
- `auto_complete.js`: Handles autocomplete inputs (e.g., agent or vendor names)
- `calendar.js`: Datepicker or calendar widget logic
- `listing_effects.js`: Visual effects for listings or dashboard cards
- `circularProgressBar.js`, `progresssbar.js`: Draw circular progress indicators (uses `progressbar/assets/`)

### AJAX + Backend Communication
- `AjaxRequest.js`: Custom XHR/AJAX wrapper
- `jquery_lib.js`: Possibly a wrapper or add-on for jQuery-based logic

### jQuery + Plugins
- `jquery.min.js`: jQuery core library
- `jquery.Jcrop.min.js`: Image cropping plugin (used in profile or logo uploads)

### Vendor / Dev Tools
- `progressbar/gulpfile.js`: Gulp config for building or minifying progress bar assets
- `progressbar/assets/circle.js`, `progressbar/dist/circle.js`: Third-party progress bar visual builder


------------------------------------------------------------------------------------------------------------------------------

## 🧱 Legacy Patterns & Gotchas

This codebase reflects a mix of older PHP practices and some modern abstractions. Below are a few common patterns, quirks, and things to watch for:

------------------------------------------------------------------------------------------------------------------------------

### 🌀 Mixed PHP + HTML
Most pages follow a legacy "page controller" style:
- Business logic and rendering are tightly coupled.
- Expect interleaved `<?php ?>` blocks within HTML templates.
- Complex logic may exist inside view files instead of dedicated controllers.

------------------------------------------------------------------------------------------------------------------------------

### 📦 Global State / GET & POST Exposure
- Pages use `$_GET`, `$_POST`, and session variables directly.
- Many pages rely on `common.php` to extract HTTP variables into loose-scope globals (e.g., `$template_id`).
- Use `Session::Get(...)` and `$HTTP_GET_VARS` interchangeably across older code.

------------------------------------------------------------------------------------------------------------------------------

### 🧩 DBRowSet / DBRow Abstraction
- These dynamic classes handle DB querying, pagination, form rendering, and upload processing.
- While powerful, they hide complexity behind magic method calls.
- Best to inspect the base class (`DBRowSetEX`, `DBRowEX`) when troubleshooting forms or filters.

------------------------------------------------------------------------------------------------------------------------------

### 🗃 Checkbox and Form Handling
- Forms often submit checkboxes via indirect mapping (e.g., naming schemes or JavaScript).
- Expect form element rendering to be abstracted in utility classes or loaded via `form.php`.

------------------------------------------------------------------------------------------------------------------------------

### 🔁 Manual Includes for Layout
- Pages manually include head, header, nav, footer in a fixed order.
- No template engine or layout inheritance — layout changes require editing `modules/*.php`.

------------------------------------------------------------------------------------------------------------------------------

### 🧷 jQuery & Custom JS Helpers
- The app uses jQuery heavily, along with homegrown scripts (`site.js`, `util.js`, etc.).
- Drag-and-drop, autocomplete, and progress bars all use custom handlers.
- Expect a mix of inline JS and custom widget logic.

------------------------------------------------------------------------------------------------------------------------------

### 🧪 Development Mode
- The app detects dev/staging mode using a `dev.` substring in the base URL (`$__DEV__`).
- Certain features or logging may behave differently in dev.

------------------------------------------------------------------------------------------------------------------------------

### 🛠 Tight Coupling Between Pages and DB Schema
- Field names, template IDs, and logic are often hardcoded.
- Changes to schema (e.g., renaming `timeline_item_due_date`) require tracking down usage in multiple files.

------------------------------------------------------------------------------------------------------------------------------

### 🔒 Session + Role Management
- `include/_admin.php` is used to enforce admin sessions on all admin pages.
- Role-specific headers, footers, and navs are included manually in user-specific folders (`pages/users/modules`, etc.).




------------------------------------------------------------------------------------------------------------------------------




## 🧮 Database Schema Overview

The application’s data model is centered around users, agents, coordinators, timeline items, and customizable templates. Below is a high-level summary of key tables and relationships.

------------------------------------------------------------------------------------------------------------------------------

### 👥 Users, Agents & Coordinators
- `users`: End users (likely buyers/sellers) using the platform
- `agents`: Real estate agents managing user timelines
- `coordinators`: Optional support role assigned to users or agents
- `coordinators_to_users`: Maps coordinators to specific users
- `agents_to_coordinators`: Maps coordinators to agents
- `agent_links`, `coordinator_links`: Store access tokens or invitation links

------------------------------------------------------------------------------------------------------------------------------

### 📅 Timeline & Templates
- `timeline_items`: Individual tasks, checklists, or deadlines (e.g. “Submit deposit”)
- `templates`: Pre-built sets of timeline items
- `conditions`: Rule-based modifiers or triggers for timeline items
- `conditions_to_timeline_items`: Associates conditions to individual timeline entries
- `contract_dates`: Key contract milestone fields (e.g. offer, close, inspection)
- `conditions_to_contract_dates`: Connects contract dates to condition logic

------------------------------------------------------------------------------------------------------------------------------

### 📦 Content & Config
- `content`: Dynamic CMS-style pages (Terms, FAQ, etc.)
- `configuration`: Global settings, such as Stripe keys or environment flags

------------------------------------------------------------------------------------------------------------------------------

### 🔊 UI Elements
- `animations`, `sounds`, `info_bubbles`: Enhance user experience or onboarding
- `features`: Feature toggles for testing or role-specific access

------------------------------------------------------------------------------------------------------------------------------

### 🔐 Admin + Logs
- `admins`: Admin users with control over templates, agents, and timeline items
- `activity_log`: User or system-generated logs for auditing
- `performance_log`: Tracks site performance events

------------------------------------------------------------------------------------------------------------------------------

### 🛠 Misc
- `discount_codes`: Optional promo codes or pricing overrides
- `holidays`: Used for adjusting date calculations (e.g., skip weekends)
- `cropped_images`: Stores metadata for user-uploaded, cropped images


