=== CREA API ===
Contributors: justinhrahb
Tags: real estate, api, directory, listings
Requires at least: 6.0
Tested up to: 6.6.2
Requires PHP: 8.0
Stable tag: 0.3.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays a comprehensive, searchable directory of office locations using the CREA Board Data API.

== Description ==

**CREA API** is a WordPress plugin that displays a comprehensive, searchable directory of office locations using the [CREA Board Data API](https://boardapi-docs.realtor.ca/#tag/Office/paths/~1Office/get).

### Features

– **Responsive Office Directory**: Displays offices in a grid of interactive cards, showing essential information such as name, address, phone, email, and website.
– **Advanced Search**: Users can search for offices by name, address, phone number, or email with instant feedback thanks to client-side filtering and debounce optimization.
– **Infinite Scroll**: Offices are loaded automatically as users scroll, enhancing the user experience without traditional pagination.
– **Automated Data Sync**: Full and incremental synchronization with the [CREA Board Data API](https://boardapi-docs.realtor.ca/#tag/Office/paths/~1Office/get) to keep office data current, including handling of inactive records.
– **Custom Database Storage**: Utilizes a custom WordPress database table for efficient storage and retrieval, optimized for large datasets.
– **User-Friendly Admin Interface**: Intuitive settings page for configuring API access, synchronization intervals, and managing data directly from the WordPress admin dashboard.

### Benefits

**For Website Visitors**

– **Enhanced User Experience**: Easily find and contact the nearest office with an intuitive and responsive interface.
– **Quick Access to Information**: Advanced search functionality reduces the time needed to locate specific offices.

**For Administrators**

– **Operational Efficiency**: Automates the updating of office information, reducing manual workload and minimizing errors.
– **Accurate Data**: Ensures the directory reflects the most current office statuses and details.
– **Scalable Solution**: Designed to handle growth as more offices are added without significant redevelopment.

== Installation ==

1. **Upload the Plugin**:
   – Upload the `crea-api` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. **Activate the Plugin**:
   – Activate the plugin through the 'Plugins' screen in WordPress.
3. **Configure Plugin Settings**:
   – Navigate to `Settings` -> `CREA API` in the WordPress admin dashboard.
   – **Client ID and Secret**: Enter your CREA Board Data API access credentials.
   – **Sync Interval**: Set how often (in hours) to perform incremental syncs. Default is every 24 hours.
   – **OfficeAOR Filter**: (Optional) Add a board name filter for API requests.
4. **Data Synchronization**:
   – Click the **Full Sync** button to initiate the initial data synchronization.
5. **Add Office Directory to Pages or Posts**:
   – In the WordPress block editor, add the **CREA Office List** block to your page or post.

== Frequently Asked Questions ==

= How do I obtain an API access token? =

You can obtain an API access token by signing up on the Bridge Interactive website and requesting API access.

= How often does the plugin synchronize data? =

By default, the plugin synchronizes data every 24 hours. You can change the synchronization interval in the plugin settings.

= Can I customize the displayed fields? =

Currently, the plugin displays predefined fields. Future updates may include customization options.

= How do I add the office directory to my site? =

You can add the **Bridge Office List** block in the WordPress block editor.

= What is the OfficeAOR Filter? =

The OfficeAOR Filter allows you to filter the data fetched from the API by board name. This is an optional setting.

== Changelog ==

= 0.3.4 =
* Initial version of fork from Bridge Directory plugin.

== License ==

This plugin is licensed under the GPLv2 or later.

== Additional Notes ==

For support and additional information, please visit the [GitHub repo](https://github.com/RAHB-REALTORS-Association/CREA-API-WP).