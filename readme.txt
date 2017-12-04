=== WP Smart Export (Free) ===
Author: SebeT
Contributors: SebeT, freemius
Tags: export, csv, excel, tabular data, readable data, user data, export
Requires at least: 3.5
Tested up to: 4.8
Stable tag: 1.4.2

A smarter and highly customizable data exporter for outputting posts data in human readable form.

== Description ==

> Free Trial the Premium version for 7 days!

> [DEMO site](http://bruno-carreco.com/wpuno/demo/wp/wp-smart-export/?demo=1)

*WP Smart Export* is a smart and highly customizable data exporter for outputting posts and user data that you can read. It is not a replacer for WordPress’s export tool used for exporting data between sites. It is instead, a tool that can be used by anyone wanting to export readable content to analyze the data.

Easily export any post types*** and users content with related taxonomies (posts types only) and/or custom fields. Instead of a raw file with unreadable category terms ID’s, user ID’s, time stamps, etc, WP Smart Export can generate files with readable content since it can replace user ID’s with user names, time stamps with readable dates, categories or any other taxonomy term ID’s with readable labels and also, unserialize serialized data.

You can always skip the smart features and still export raw data using WP Smart Export. This makes it ideal to export customized tabular files to be imported on external systems or services. A good example would be using it to export your lists of users to the [AWeber Email Marketing Service](http://www.aweber.com/).

*** Custom post types support is only available in the Pro version.

> #### Free Features
>
> * Drag&Drop Fields Re-Ordering
> * Customize Field Names
> * Export Readable Posts and Users Data
> * Export Taxonomies Fields
> * Export Custom Fields
> * Export Content Between Dates
> * Save Export Templates

> #### Pro Version Features
>
> *All Free features, plus:*
>
> * Export any Custom Post Type
> * Scheduled Exports (uses WP Cron)
> * Preset Schedules: Daily, Weekly, Monthly
> * Custom Schedules: Export Past n Days
> * Notify Multiple Recipients per Schedule
> * Automatically Attach Exported Files to Email Recipients

== Installation ==
1. Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.
2. A new Menu named 'WP Smart Export' will be available on the left sidebar.

== Screenshots ==

1. Simple UI
2. Templates Support & Options
3. Re-Order/Rename and specify Fields Output
4. Export Options / Save/Update Template

== Changelog ==

= 1.4.2 =
Fixed: Security and performance fixes (update is highly recommended!)

= 1.4.1 =
Fixed: Plugin update notifications being blocked

= 1.4 =
* Fixed:   Missing core users fields
* Fixed:   Correctly decode specials chars like '&amp;'
* Changes: Added guided help tour
* Changes: Added help tabs
* Changes: Added option to remove HTML tags from content output
* Changes: Added support for exporting 'Pages'
* Changes: Optimized sample content/fields loading performance
* Changes: Show 'Cancel' button when content type is loading

= 1.3 =
* Changes: Replaced clickable tooltips with hover tooltips
* Fixed: Mixed content JS errors
* Fixed: Users main fields not being displayed on multi-site installs

= 1.2 =
* Changed: Files and folders reorganization
