# Advanced SEO Audit Tool

This is a web-based SEO Audit Tool built with PHP and PostgreSQL. It allows users to analyze any website URL and get a comprehensive SEO report, including an overall score, page speed, meta description check, heading audit, SSL verification, and social media preview.

## Features

*   **Overall SEO Score:** Calculates a score out of 100 based on various SEO metrics.
*   **Fast Analysis (AJAX & cURL):** Uses AJAX for seamless background analysis without page reloads, and optimized cURL for fast HTML fetching.
*   **Database Caching:** Caches results in PostgreSQL for 10 minutes to significantly speed up repeated analyses of the same URL.
*   **Core Web Vitals:** Measures Page Speed (load time).
*   **Content Analysis:** Checks Meta Description, Keyword Coverage, and Image Count.
*   **Accessibility & SEO:** Identifies images missing the `alt` attribute.
*   **Heading Audit:** Scans and lists H1, H2, and H3 tags, warning if H1 is missing or duplicated.
*   **Security Check:** Verifies if the website uses HTTPS (SSL).
*   **Social Media Preview:** Extracts Open Graph tags (`og:title`, `og:image`) to show how the link will look when shared on social media.
*   **PDF Export:** Generates a professional, clean PDF report of the analysis (with Emoji stripping to prevent rendering issues).
*   **History & Charts:** Displays a history of past searches and a Chart.js bar chart of recent page speeds.
*   **Responsive UI:** Built with Bootstrap 5 for a clean, mobile-friendly dashboard.

## Prerequisites

Before running this project, ensure you have the following installed:

*   **PHP** (version 7.4 or higher recommended)
*   **PostgreSQL** (pgAdmin4 recommended for database management)
*   **Composer** (PHP dependency manager)

## Installation & Setup

Follow these steps to get the project running on your local machine:

### 1. Database Setup

1.  Open **pgAdmin4**.
2.  Create a new database named `SEO_Project` (or any name you prefer).
3.  Open the Query Tool for your new database.
4.  Copy the contents of the `final_database_setup.sql` file (or `update_v3.sql` if you are upgrading an existing database) and execute it. This will create the necessary `pages` table and insert some sample data.

### 2. Configure Database Connection

1.  Open the `db.php` file in your code editor.
2.  Update the connection details with your actual PostgreSQL credentials:
    ```php
    $host = 'localhost';
    $port = '5432';
    $dbname = 'SEO_Project'; // Your database name
    $user = 'postgres';      // Your PostgreSQL username
    $password = 'YourPassword'; // Your PostgreSQL password
    ```

### 3. Install Dependencies

This project uses `mPDF` for PDF generation and `Symfony DomCrawler` for HTML parsing. Install them using Composer:

1.  Open your terminal or command prompt.
2.  Navigate to the project directory (`cd /path/to/SEO_Project`).
3.  Run the following command:
    ```bash
    composer install
    ```
    *(Note: If you don't have a `composer.json` file yet, run `composer require mpdf/mpdf symfony/dom-crawler` instead).*

### 4. Run the Application

You can use PHP's built-in development server to run the project:

1.  In your terminal, ensure you are in the project directory.
2.  Run the following command:
    ```bash
    php -S localhost:8081
    ```
3.  Open your web browser and go to: `http://localhost:8081`

## How It Works (Architecture)

1.  **Frontend (`index.php`):**
    *   The user enters a URL in the Bootstrap-styled form.
    *   JavaScript intercepts the form submission and uses the **Fetch API (AJAX)** to send a POST request to `analyze.php`.
    *   A loading spinner is displayed while waiting for the response.
    *   Once the JSON response is received, JavaScript dynamically updates the DOM (cards, tables, lists) to display the results without reloading the page.

2.  **Backend Analysis (`analyze.php`):**
    *   **Caching Check:** It first queries the PostgreSQL database to see if the exact URL was analyzed within the last 10 minutes. If yes, it returns the cached data immediately.
    *   **Fetching:** If not cached, it uses **cURL** with a 5-second timeout to quickly download the HTML content of the target URL.
    *   **Parsing:** It uses `Symfony\Component\DomCrawler` to parse the HTML and extract specific elements (meta tags, headings, images, links).
    *   **Scoring:** It calculates an overall SEO score based on predefined rules (e.g., penalties for slow speed, missing H1, no SSL).
    *   **Database Save:** It saves the new analysis results into the `pages` table in PostgreSQL.
    *   **Response:** It returns the data back to the frontend as a JSON object.

3.  **PDF Export (`export_pdf.php`):**
    *   When the user clicks "Export to PDF", a form submits the `report_id` to this script.
    *   It fetches the specific report data from the database.
    *   It uses a custom `remove_emojis()` function to strip out emojis and special symbols from the text (like meta descriptions) to prevent rendering issues (the '□□□' box problem).
    *   It uses the `mPDF` library, configured with UTF-8 and the `dejavusans` font, to generate a formatted PDF and prompts the user to download it.

## Technologies Used

*   **Backend:** PHP 8+
*   **Database:** PostgreSQL
*   **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
*   **UI Framework:** Bootstrap 5
*   **Charting:** Chart.js
*   **Libraries:**
    *   `symfony/dom-crawler`: For parsing HTML.
    *   `mpdf/mpdf`: For generating PDF reports.
