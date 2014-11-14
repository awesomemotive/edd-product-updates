![Alt text](/assets/img/edd_pup_banner_v1.png?raw=true "EDD Product Updates Extension")

EDD Product Updates Extension Documentation
===================

## Known Issues
* Multiple emails can potentially be sent at once which would screw with the `edd_pup_sending_email` transient and several functions
* Needs to be tested with more email templates
* Filters and actions need to be added throughout

## Limitations
* Multiple email messages cannot be sent at the same time (meaning multiple users cannot be sending campaigns all at once). Possible solution: have a queue of emails waiting to be sent.
* SMTP limits are different depending on host. Possible solution: Recommend users to Mandrill (free for up to 12,000 emails per month). Most users who don't already have a robust email sending platform will never cross the free threshold. Or have Advanced Settings that will throttle emails on an hourly basis.
* Requires Javascript and AJAX heavily. Possible solutions: Build in alternative save methods on edit page. Add option to send emails via wp_cron in background.
* Limited variable pricing support.

**Requirements:**
* **Wordpress:** 3.0+
* **EDD:** 2.0
* **PHP:** 4.3 (depending on WP version)

## Settings
There are several important settings for the EDD Product Updates extension which are found in "Product Updates Email Settings" section within the "Emails" tab of the main settings page of Easy Digital Downloads (Downloads -> Settings -> Emails). Below is an explanation of what each setting does:

**Disable automatic queue removal**. When an email message does not finish sending, it is stored in an email queue. By default, EDD Product Updates automatically removes any emails that have been in the queue for 48 hours. By checking this box and disabling the automatic queue removal, emails will remain in the queue indefinitely until you either manually clear them from the queue or finish sending/processing them.

**Easy Digital Downloads Software Licensing Integration**. When enabled, only customers with active software licenses will receive updates for products which have EDD Software Licensing enabled. As an example, if Product A has Software Licensing Enabled and Product B does not, a customer will receive 

**Email Template**. Selects which template to apply only to emails sent using the EDD Product Updates extension. This setting **only** affects product update emails.

## The Main Product Updates Emails Page

### Email Name
Here is a description

### Statuses
Here is a description
**Draft**
**Processing**
**In Queue**
**Sent**. Note: This does not guarantee that all email messages were received. Rather it means that the extension processed the messages successfully. It is still possible that a message may not reach a customer because of technical issues that aren't caused by the extension. See troubleshooting below for more details.

###Subject
Here is a description

###Recipients
Here is a description

###Last Modified/Date Sent
Here is a description

## Sending a Product Update Email
Here is a description

## Troubleshooting
Here is a description
