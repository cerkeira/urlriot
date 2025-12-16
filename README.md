# URLRIOT

A simple, fast, multi‑engine **URL safety checker** built with **Laravel 12**.

URLRIOT aggregates the results of multiple well‑known URL scanning services into a **single, easy‑to‑understand verdict**, allowing users to quickly assess whether a link is safe before opening it.

---

## ✨ Features

-   🔍 Scan URLs using multiple security providers
-   🧠 Normalize very different API responses into a common format
-   ⭐ Converge results into a **1–5 safeness rating**
-   🟢 Clear visual feedback (Safe / Unsafe / Unknown)
-   📊 Expandable raw API results for transparency
-   ⚡ No database required
-   🧩 Clean service‑based architecture (easy to extend)

Currently supported providers:

-   **Google Safe Browsing**
-   **VirusTotal**
-   **urlDNA**

---

## 🛠 Tech Stack

-   **Laravel 12**
-   **PHP 8.4**
-   **Blade** (views)
-   **Tailwind CSS**
-   **Laravel HTTP Client** (Guzzle)

No database, queues, or background workers are required.

---

## 🚀 Getting Started

### 1. Clone the repository

```bash
git clone https://github.com/cerkeira/urlriot.git
cd urlriot
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment setup

Copy the example environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Add your API keys to `.env`:

```env
GOOGLE_KEY=your_google_safe_browsing_key
VIRUSTOTAL_KEY=your_virustotal_key
DNA_KEY=your_urldna_key
```

> APIs are optional — missing keys will disable the corresponding service gracefully.

---

## ▶️ Run the project

```bash
php artisan serve
```

Then open:

```
http://127.0.0.1:8000
```

---

## 🧠 How It Works

### 1. URL normalization

User input such as:

```
google.com
```

Is automatically normalized to:

```
https://google.com
```

This avoids browser validation issues and improves UX.

---

### 2. Scanning services

Each provider has its own **dedicated method** that:

-   Calls the external API
-   Parses the raw response
-   Determines whether the result is safe, unsafe, or unknown

All results are returned in a common structure:

```php
[
  'safe' => true|false|null,
  'rating' => 1–5,
  'raw' => [...]
]
```

---

### 3. Final safeness rating (1–5)

The app combines all provider ratings into a single score:

| Rating | Meaning     |
| ------ | ----------- |
| 5/5    | Very safe   |
| 4/5    | Likely safe |
| 3/5    | Uncertain   |
| 2/5    | Suspicious  |
| 1/5    | Dangerous   |

If providers disagree or return inconclusive data, the rating is automatically lowered.

---

## 🖥 UI Behavior

-   Immediate visual verdict per provider
-   Click **“See detailed results”** to inspect raw API responses
-   Clear color coding:

    -   Green → Safe
    -   Red → Unsafe
    -   Yellow → No definitive result

---

## 📁 Project Structure (simplified)

```
app/
 ├─ Http/Controllers/
 │   └─ ScanController.php
 ├─ Services/
 │   └─ ScannerService.php
resources/
 ├─ views/
 │   └─ home.blade.php
routes/
 └─ web.php
```

---

## ➕ Adding a New Provider

1. Add a new method to `ScannerService`
2. Normalize its response to:

```php
['safe' => bool|null, 'rating' => int]
```

3. Include it in the scan pipeline

No frontend changes required.

---

## ⚠️ Disclaimer

This tool is **not a substitute for professional security analysis**.
Results depend entirely on third‑party services and should be treated as **advisory only**.
