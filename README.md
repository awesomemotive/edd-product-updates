EDD Product Updates Extension
===================

Version 0.9.2 – *This is a very early version that has been tested in a limited WordPress environment*

**Testing Environment:**
* **Wordpress:** 3.9.1
* **EDD:** 2.0.4
* **PHP:** 5.4.26

This extension allows you to send specialized product update emails to your customers when using [Easy Digital Downloads](http://easydigitaldownloads.com/). For instance, if you release an updated edition of an eBook, instead of confusing customers by resending their purchase receipts – with purchase language, downloads to all the products they bought, and no context for the email – you can use this extension to send them an email with a message tailored to the update while taking full advantage of Easy Digital Download's powerful download link expiration system.

## Features
**Customizable Email Message**. Craft a message separate from purchase receipt emails so you can engage easily with your customers on a more targeted level while delivering updates to your digital products – whether that's a new edition of your eBook or a fix for a corrupted file.

**Additional Email Tags**. The {unsubscribe_products} and {unsubscribe_products_links} tags drop into emails easily to show customers, respectively, a plain list of updated products and a list of updated products with refreshed download links. A third tag, {unsubscribe}, allows customers to opt-out of future updates.

**Complete Unsubscribe System**. The added {unsubscribe} email tag outputs a link customers can click on to be removed from future product updates. If clicked on accident, it's easy to re-subscribe via single button click. Additionally, admins have the ability to unsubscribe/resubscribe customers on their payment history page.

**Customer History Logs**. Major actions are logged to each customer's payment history page. This includes log notes when they are sent an update email and when they unsubscribe/resubscribe from updates.

**Email Preview Confirmation**. The "Send Emails" button triggers an AJAX call that saves any changes made to your email message and then shows you a preview of it along with essential info like products slated for updates and number of recipients. This ensures you don't accidentally send an email blast before it's ready!

**EDD Software Licensing Integration**. Choose whether to send product update emails to those customers who have active subscription licenses for those products in which EDD Software Licensing is being used. Customers with expired or inactive licenses won't receive update emails when enabled.

**Batch Sending of Emails**. For those with large customer lists, the extension breaks up emails into batches and sends them in the background (with a front-end UI to show you the progress) so you don't have to worry about the process timing out PHP on your server.

## Installation
1. Download plugin as .zip
2. Go to Plugins -> Add New in WordPress Dashboard
3. Select "Upload"
4. Upload the downloaded .zip file
5. Activate plugin (Must have Easy Digital Downloads installed and activated)
6. Go to Downloads -> Settings -> Emails and scroll to the bottom
7. Customize your product update emails!

## Questions?
Email evan [at] theblackandblue.com