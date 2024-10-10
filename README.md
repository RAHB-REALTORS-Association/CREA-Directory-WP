# CREA Board Data API Directory for WordPress

[![License](https://img.shields.io/badge/license-GPLv2-green.svg)](LICENSE)

> [!IMPORTANT]
> This project is not affiliated with CREA. Please do not contact them for support related to issues with this plugin.

A WordPress plugin that displays a comprehensive, searchable directory of office locations using the [CREA Board Data API](https://boardapi-docs.realtor.ca/#tag/Office/paths/~1Office/get).

## Table of Contents

- [Features](#features)
- [Benefits](#benefits)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Technical Highlights](#technical-highlights)
- [License](#license)

## Features

- **Responsive Office Directory**: Displays offices in a grid of interactive cards, each showing essential information such as name, address, phone, email, and website.
- **Advanced Search**: Users can search for offices by name, address, phone number, or email with instant feedback thanks to client-side filtering and debounce optimization.
- **Infinite Scroll**: Offices are loaded automatically as users scroll, enhancing the user experience without traditional pagination.
- **Automated Data Sync**: Full and incremental synchronization with the [CREA Board Data API](https://boardapi-docs.realtor.ca/#tag/Office/paths/~1Office/get) to keep office data current, including handling of inactive records.
- **Custom Database Storage**: Utilizes a custom WordPress database table for efficient storage and retrieval, optimized for large datasets.
- **User-Friendly Admin Interface**: Intuitive settings page for configuring API access, synchronization intervals, and managing data directly from the WordPress admin dashboard.

## Benefits

### For Website Visitors

- **Enhanced User Experience**: Easily find and contact the nearest office with an intuitive and responsive interface.
- **Quick Access to Information**: Advanced search functionality reduces the time needed to locate specific offices.

### For Administrators

- **Operational Efficiency**: Automates the updating of office information, reducing manual workload and minimizing errors.
- **Accurate Data**: Ensures the directory reflects the most current office statuses and details.
- **Scalable Solution**: Designed to handle growth as more offices are added without significant redevelopment.

## Installation

1. **Install Plugin**:
   - **Option A**: Download the latest plugin zip file from the [Releases](https://github.com/RAHB-REALTORS-Association/CREA-API-WP/releases) Page and install it through the WordPress `Plugins -> Add New Plugin` screen directly.
   - **Option B**: Upload the `crea-api` folder from the repository to the `/wp-content/plugins` directory on your WordPress installation, then install the dependencies (see Development Setup below).
2. **Activate Plugin**: Activate the plugin through the `Plugins -> Installed Plugins` screen in WordPress.

### Development Setup

If you chose Option B and are setting up the plugin for development purposes, from the `crea-api` folder:

3. **Install Dependencies**:
   - Run `composer install` to set up autoloading.
   - Run `npm install` to install Node.js dependencies.
   - Run `npm run build` to build the block editor scripts.

## Usage

1. **Configure Plugin Settings**:
   - Navigate to `Settings -> CREA API` in the WordPress admin dashboard.
   - **Client ID and Secret**: Enter your CREA Board Data API access credentials.
   - **Sync Interval**: Set how often (in hours) to perform incremental syncs. Default is every 24 hours.
   - **OfficeAOR Filter**: (Optional) Add additional query parameters for API requests.
2. **Data Synchronization**:
   - Click the **Full Sync** button to initiate the initial data synchronization.
   - The plugin will automatically perform incremental syncs based on the configured interval.
3. **Add Office Directory to Pages or Posts**:
   - In the WordPress block editor, add the **CREA Office List** block to your page or post.
   - Customize the block settings in the editor sidebar to adjust the number of columns and rows.
4. **Manage Data and Cache**:
   - You can manually clear the cache if needed from the settings page.

## Configuration

### API Access

- **Client ID and Secret**: Required. Obtain this from CREA by emailing support.
  - Refer to the [Bridge API Explorer and Documentation](https://boardapi-docs.realtor.ca/#tag/Office/paths/~1Office/get) for more information on obtaining an access token and available datasets.
- **OfficeAOR Filter**: Optional. Add additional query parameters to filter the data fetched from the API.

### Sync Settings

- **Sync Interval**: Optional. Set the interval (in hours) for automatic incremental synchronization. Default is 24 hours.
- **Advanced Query Filter**: Optional. Add custom query parameters to refine the data fetched from the API.

### Display Settings

- **Columns and Rows**: Customize the number of columns and rows in the directory grid via the block editor settings.

## Technical Highlights

- **Integration with CREA Board Data API**:
  - Real-time data fetching ensures accurate and up-to-date information.
  - Selective data retrieval optimizes performance and reduces data transfer.
- **Modern Development Practices**:
  - Object-oriented programming (OOP) enhances code maintainability and scalability.
  - Uses namespaces and PSR-4 autoloading via Composer.
  - Adheres to WordPress coding standards for compatibility and reliability.
- **Performance Optimization**:
  - Debounce implementation improves search performance by minimizing unnecessary server requests.
  - Client-side filtering reduces server load by handling search within the user's browser.
  - Custom database table optimizes data storage and retrieval speeds for large datasets.
- **Security Measures**:
  - Data sanitization of all user inputs to prevent vulnerabilities.
  - Nonce verification for AJAX requests and form submissions to prevent unauthorized access.
  - Prepared statements for database queries to prevent SQL injection attacks.

## License

The code in this repository is licensed under the GPLv2 License - see the [LICENSE](LICENSE) file for details.