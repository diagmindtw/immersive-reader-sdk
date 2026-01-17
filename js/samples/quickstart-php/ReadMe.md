# Immersive Reader - PHP Sample

## Prerequisites

* An Immersive Reader resource configured for Azure Active Directory authentication. Follow [these instructions](https://docs.microsoft.com/azure/applied-ai-services/immersive-reader/how-to-create-immersive-reader) to get set up. You will need some of the values created here when configuring the sample project properties. Save the output of your session into a text file for future reference.
* PHP 7.0 or higher with cURL extension enabled
* A web server (Apache, Nginx, or PHP's built-in web server)

## Installation

### Windows Installation

1. Install [PHP](https://www.php.net/downloads.php) for Windows. You can use XAMPP, WAMP, or install PHP directly.

1. Ensure the cURL extension is enabled in your `php.ini` file. Uncomment the line:
   ```
   extension=curl
   ```

1. If using XAMPP or WAMP, place this sample project in the `htdocs` or `www` directory.

### Linux/OSX Installation

1. PHP usually comes pre-installed on Linux and OSX. Check your version with:
   ```bash
   php -v
   ```

1. If PHP is not installed, install it using your package manager:
   
   **Ubuntu/Debian:**
   ```bash
   sudo apt-get update
   sudo apt-get install php php-curl
   ```
   
   **OSX (using Homebrew):**
   ```bash
   brew install php
   ```

1. Ensure the cURL extension is enabled. Check with:
   ```bash
   php -m | grep curl
   ```

## Usage

1. Navigate to the **immersive-reader-sdk/js/samples/quickstart-php** directory

1. Create a file called **.env** in the quickstart-php directory and add the following, supplying values as appropriate:

    ```text
    TENANT_ID={YOUR_TENANT_ID}
    CLIENT_ID={YOUR_CLIENT_ID}
    CLIENT_SECRET={YOUR_CLIENT_SECRET}
    SUBDOMAIN={YOUR_SUBDOMAIN}
    ```

1. Start a local web server:

   **Using PHP's built-in web server (recommended for testing):**
   ```bash
   php -S localhost:8000
   ```
   
   **Or, if using Apache/Nginx:**
   Configure your web server to serve the quickstart-php directory.

1. Open a web browser and navigate to:
   - [http://localhost:8000](http://localhost:8000) (if using PHP's built-in server)
   - Or your configured Apache/Nginx URL

1. Click the "Immersive Reader" button to launch the Immersive Reader with the sample content.

## How It Works

This PHP sample demonstrates how to:

1. **Authenticate with Azure Active Directory**: The `index.php` file handles authentication by requesting an access token from Azure AD using your credentials (Tenant ID, Client ID, and Client Secret).

2. **Provide Token to Frontend**: When the frontend JavaScript requests a token (via AJAX), the PHP backend fetches a fresh token from Azure AD and returns it along with the subdomain.

3. **Launch Immersive Reader**: The frontend JavaScript uses the token and subdomain to launch the Immersive Reader SDK with your content.

## Troubleshooting

* **"Missing required environment variables" error**: Ensure your `.env` file is in the same directory as `index.php` and contains all required values.

* **"Failed to acquire Azure AD token" error**: Check that your Azure credentials are correct and that your Immersive Reader resource is properly configured.

* **cURL errors**: Ensure the PHP cURL extension is installed and enabled. Check with `php -m | grep curl`.

* **Blank page**: Check your PHP error logs. You may need to enable error reporting in development by adding this to the top of `index.php`:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

## License

Copyright (c) Microsoft Corporation. All rights reserved.

Licensed under the MIT License.
