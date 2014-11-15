![Alt text](/assets/img/edd_pup_banner_v1.png?raw=true "EDD Product Updates Extension")

EDD Product Updates Extension
===================

Version 0.9.5 – *This is an early version that has been tested in a limited WordPress environment*

This extension allows you to send specialized product update emails to your customers when using [Easy Digital Downloads](http://easydigitaldownloads.com/). For instance, if you release an updated edition of an eBook, instead of confusing customers by resending their purchase receipts – with purchase language, downloads to all the products they bought, and no context for the email – you can use this extension to send them an email with a message tailored to the update while taking full advantage of Easy Digital Download's powerful download link expiration system.

## Features
**Customizable Email Message**. Craft a message separate from purchase receipt emails so you can engage easily with your customers on a more targeted level while delivering updates to your digital products – whether that's a new edition of your eBook or a fix for a corrupted file.

**Additional Email Tags**. The {updated_products} and {updated_products_links} tags drop into emails easily to show, respectively, a plain list of updated products and a list of updated products with download links. A third tag, {unsubscribe}, allows customers to opt-out of future updates.

**Complete Unsubscribe System**. The added {unsubscribe} email tag outputs a link customers can click on to be removed from future product updates. If clicked on accident, it's easy to re-subscribe via single button click. Additionally, admins have the ability to unsubscribe/resubscribe customers on their payment history page.

**Customer History Logs**. Major actions are logged to each customer's payment history page including when they are sent an update email and when they unsubscribe/resubscribe from updates.

**Batch Sending of Emails**. The extension breaks up emails into batches and sends them in the background (with a front-end UI to show you the progress) so you don't have to worry about the process timing out PHP on your server.

**Complete Email Management UI**. Edit multiple email drafts or view sent, cancelled, and queued emails all from the Wordpress Dashboard. Perfect for planning email updates in advance or remembering when previous updates were sent.

**Email Preview Confirmation**. See a preview of your email, with templates and tags processed, before saving changes or sending the email.

**Send Test Emails**. If you'd rather preview your email message inside different email clients, you can easily send test emails to up to five different email addresses – with tags interpreted and displayed just as your customers will see them.

**EDD Software Licensing Integration**. Choose whether to send product update emails to those customers who have active subscription licenses for products using EDD Software Licensing. Customers with expired or inactive licenses won't receive update emails when enabled.


## Installation
1. Download plugin as .zip
2. Go to Plugins -> Add New in WordPress Dashboard
3. Select "Upload"
4. Upload the downloaded .zip file
5. Activate plugin (Must have Easy Digital Downloads installed and activated)
6. Go to Downloads -> Settings -> Emails and scroll to the bottom
7. Customize your product update emails!

## Known Issues
* Needs to be tested with more email templates
* Filters and actions need to be added throughout

## Limitations
* SMTP limits are different depending on host. Possible solution: Recommend users to Mandrill (free for up to 12,000 emails per month). Most users who don't already have a robust email sending platform will never cross the free threshold. Or have Advanced Settings that will throttle emails on an hourly basis.
* Requires Javascript and AJAX heavily. Possible solutions: Build in alternative save methods on edit page. Add option to send emails via wp_cron in background.
* Limited variable pricing support.

**Tested With:**
* **Wordpress:** 4.0 / **EDD:** 2.1.7 / **PHP:** 5.5.17
* **Wordpress:** 4.0 / **EDD:** 2.1.7 / **PHP:** 5.2.17
* **Wordpress:** 3.9.1 / **EDD:** 2.0.4 / **PHP:** 5.4.33

**Requirements:**
* **Wordpress:** 3.0+
* **EDD:** 2.0
* **PHP:** 4.3 (depending on WP version)

## Questions?
Email evan [at] theblackandblue.com