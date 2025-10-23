# Laravel Iran Payment

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](#license)

A developer-friendly Laravel package that simplifies integration with Iranian payment gateways. It offers a consistent API, secure flows, and an extensible driver system so you can plug in multiple gateways with minimal effort.

> **Why this exists:** to provide a secure, configurable, and developer-friendly way to accept payments via Iranian gateways in Laravel. (Source: repository description & license.)  

---

## Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick start](#quick-start)
- [Usage](#usage)
  - [Process Payment](#create-a-transaction)
  - [Custom drivers](#custom-drivers)
- [Testing](#testing)

---

## Features

- ✅ Unified, fluent API across supported gateways  
- ✅ Secure purchase → process → verify flow  
- ✅ First-class Laravel experience (config, facades, DI, routes, workbench)  
- ✅ Extensible driver contract for adding new gateways  
- ✅ Well-structured tests scaffold (PHPUnit)

> **Note:** List the exact gateways you ship with under “Supported gateways” below.

**Supported gateways (update this list):**

- [x] Zarinpal
- [x] Payping
- [ ] …add more

---

## Requirements

- PHP **8.1+** (recommended: 8.2/8.3)
- Laravel **10+** or **11**

> If your code requires different versions, update this section.

---

## Installation

Install via Composer:

```bash
composer require a-sabagh/laravel-iran-payment
```

## Configuration

This package merges its own config and loads translations out-of-the-box:

- Config is merged from: `vendor/a-sabagh/laravel-iran-payment/config/irpayment.php`
- Translations are loaded from: `vendor/a-sabagh/laravel-iran-payment/lang` (namespace: `irpayment`)
- Views are loaded from: `vendor/a-sabagh/laravel-iran-payment/resources/views` (namespace: `irpayment`)  

```bash
php artisan vendor:publish --tag=irpayment
```
