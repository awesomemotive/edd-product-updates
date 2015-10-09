## 1.1.2 (October 9th, 2015)
* Fix: Redundancy for situations where a user's email may not be fetched from the right part of the metadata
* Fix: Database compatibility with network activated multisite installations
* Fix: Issues with emails not sending even with test mode disabled
* Fix: Issue of customer updates not recognized due to serialized string on return
* Fix: 404 error on stripes.png file
* Tweak: Style change for "Product Updates Settings" header in Settings->Emails tab

## 1.1.1 (September 11th, 2015)
* Fix: Detection and alert for customers trying to send an email only to bundle customers without a bundle selected
* Fix: Removes email templates from being accessible on the front end
* Fix: Sanitize "From Name" and "Subject" fields properly
* Fix: Some customers without updates being added to the email queue
* Fix: Switch to `edd_get_option()` for determining if test mode is enabled to fix PHP undefined index notice
* Fix: Remove `boolval()` for greater PHP compatibility

## 1.1 (February 19th, 2015)
* Feature: Advanced options for bundle products when creating and sending emails
* Feature: Set default from name, from email address, subject, and message in settings
* Feature: Option to disable log notes when customers are either sent emails, unsubscribe, or resubscribe
* Feature: Toggle between "Preview" of sent emails or the original message on the view email page
* Feature: Ability to duplicate emails
* Enhancement: "Same Template as Purchase Receipt" option in Product Update settings
* Enhancement: Specify which user is processing an email in the alert on the view email page
* Tweak: Test emails are now sent using full scope of EDD_emails class for EDD versions 2.1+
* Tweak: Change "Send New Email" to "Add New Update Email" on main list view page
* Added: "Test Mode" option in settings for simulation of sending emails without actually sending them (good for debugging)
* Added: `edd_pup_valid_license_statuses` filter to optionally expand which type of licensed customers get emails
* Fix: Links not outputting from {updated\_products\_links} when customers have purchased only a bundled product
* Fix: Customers with statuses other than "Complete" still receiving emails (though unable to download products)
* Fix: Customers with an inactive website, but otherwise active/valid EDD Software Licensing license key not receiving emails
* Fix: Issue with email trigger function causing extension to use transients as primary source of info instead of as a fallback
* Fix: Javascript error on preview and test emails when visual editor is disabled by user

## 1.0 (December 10th, 2014)

RC5 - Final Release
* Fix: Add max-width for plaintext emails on preview and confirmation popup so they don't run off the page
* Fix: Remove variable pricing dash that is serving no current purpose
* Enhancement: Add sample unsubscribe/resubscribe page when following the unsubscribe link from preview and test emails
* Enhancement: Add plaintext versions of {updated\_products}, {updated\_products\_links}, and {unsubscribe\_link} tags
* Enhancement: Improve how previews handle EDD Product Updates tags when plaintext email is chosen


RC4
* Tweak: Make unsubscribe and resubscribe pages more descriptive
* Tweak: Properly decode email from URL on unsubscribe and resubscribe pages

RC3
* Fix: sending function that was commented out for testing.
* Fix: All emails in queue showing as processing when only one email is processing


RC2
* Tweak: Moved settings from extensions tab to emails tab


RC1
* Fix: PHP error when previewing an email without explicitly choosing an email template in settings
* Fix: PHP error when no products have been chosen yet on the edit email page
* Fix: Users in multi-user environments could see popup notices about emails needing to be sent even if another user was currently sending them
* Tweak: Change default email message when creating a new product update email
* Tweak: Change instances of "Product Update Emails" plugin name to "Product Updates"
* Tweak: Move JS and CSS files into respective folders within "assets" to conform to EDD Extension Boilerplate
* Tweak: Rename "inc" folder to "includes" to conform to EDD Extension Boilerplate
* Tweak: Moved `edd_pup_get_email_templates` and `edd_pup_template` from main plugin file to "includes/misc-functions"
* Integration of EDD Extension Boilerplate with `EDD_Extension_Activation` class

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