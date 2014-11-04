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