// README.md
# Subscription Management Application

This is a PHP and MySQL-based subscription management web application with member and admin portals, Stripe integration for payments, and Microsoft Graph API for email sending.

## Table of Contents
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
  - [1. Clone the Repository](#1-clone-the-repository)
  - [2. Composer Dependencies](#2-composer-dependencies)
  - [3. Environment Configuration](#3-environment-configuration)
  - [4. Database Setup](#4-database-setup)
  - [5. Web Server Configuration (Nginx/Apache)](#5-web-server-configuration-nginxapache)
  - [6. Tailwind CSS Setup](#6-tailwind-css-setup)
  - [7. Run the Application](#7-run-the-application)
- [Admin Panel Access](#admin-panel-access)
- [Stripe Webhook Setup](#stripe-webhook-setup)
- [Microsoft Graph API Setup](#microsoft-graph-api-setup)
- [Cron Jobs (Optional but Recommended)](#cron-jobs-optional-but-recommended)

## Features
- Responsive UI with Tailwind CSS
- Member Portal (`/`)
- Admin Portal (`/admin`)
- Granular Role-Based Access Control (RBAC)
- Fixed-length, Recurring, and Session-based Subscriptions
- Pro-rata calculation and Admin Fees
- Free Trial options for Subscriptions and Classes
- Age and Capacity-based restrictions
- Class Management (single & recurring series)
- Class Booking (auto-booking for subscriptions, manual for others)
- Payment Management (Stripe, Cash, Bank Transfer)
- PCI Compliant Stripe Integration (using Elements/Tokens)
- Member Payment Method Management
- In-app Notifications & Email Notifications (via Microsoft Graph API)
- Member-Admin Messaging
- Customizable Site Colors

## System Requirements
- PHP >= 8.1
- MySQL >= 8.0
- Composer
- A web server (Apache with mod_rewrite or Nginx)
- Node.js & npm (for Tailwind CSS compilation)

## Installation Guide

### 1. Clone the Repository
```bash
git clone <repository-url> subscription-app
cd subscription-app