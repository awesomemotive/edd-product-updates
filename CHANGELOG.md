## 0.9.5 (November 23rd, 2014)

* Fix: Email sending transients are now user-specific so multiple users can send and process emails simultaneously without mixing messages
* Fix: Database query was returning inconsistent results on number of recipients and customers eligible for updates depending on how customer was added to Payment History (i.e. regular purchase, manual purchase, CSV import)
* Fix: Tags were referencing older emails for customers that have had their email address changed in payment history. This caused errors on download links.
* Fix: Check whether the subject of an email matches the stored transient
* Fix: Older versions of EDD throwing an error due to misidentified user_id in `edd_pup_email_body_header` transient
* Tweak: Removed incompatible email templates from the list of options on the settings page
* Tweak: Switch to `EDD()->html->product_dropdown()` for selecting products to be updated to help mitigate scalability issues
* Tweak: Add scrollbars to overflowing message previews with some email templates within certain browser sizes
* Feature: Added which user sent/published an email to the "View Email" page
* New banner image

## 0.9.4.3 (November 12th, 2014)

* Fix: Register JS and CSS files so they load even if plugin folder is renamed from default

## 0.9.4.2 (November 12th, 2014)

* Tweak: Only delete critical extension data if user has opted-in for deletion in EDD main settings
* Tweak: Minor language changes to improve clarity of inline text help
* Tweak: Remove EDD_License() handler that's included in EDD core
* Fix: Use minified JS

## 0.9.4.1 (November 4th, 2014)
The first entry in the changelog!

* Tweak: Add nonce checks to several functions
* Tweak: Add information on whether EDD Software Licensing integration was active during an email send to the "View Email" page
* Tweak: Make sending email popup slightly larger in height
* Optimization: `edd_pup_ajax_start` function
* Optimization: `edd_pup_confirm_html` function
* Optimization: Combine `edd_pup_prepare_data` with `edd_pup_ajax_save` into new `edd_pup_sanitize_save` function
* Fix: Typo on sending email popup for completion time
* Fix: Sending popup success message provides more details on a restart
* Fix: Number of recipients is accurate across all views when saving an email
* Fix: WP_Cron correctly adheres to settings on when to auto-delete queued emails
* Add EDD_License handler