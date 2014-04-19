=== St Category Email Subscribe ===
Contributors: dharashah
Donate link: http://sanskrutitech.in/index.php/wordpress-plugins/
Tags: subscribe, email, category
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin to allow visitors to subscribe based on category of posts

== Description ==

Surprisingly there is no plugin available to allow users to subscribe for posts on a wordpress website based on category.
 A subscriber for one category might not want to receive posts of another category. This plugin will help you to do that.
 Once a subscriber is added for a particular category, he/she will receive emails as soon as a post is published in that category.


**Features**
1. Add Subscribers for their desired category.
2. Use Widget or Short Code to display Subscriber Form.
3. Add the Subscribers manually, or upload in batch from a CSV file.
4. Email will be sent to all subscribers as soon as a post is published in that category.

== Installation ==

1. Download the Plugin using the Install Plugins 
   OR 
   Upload folder `st-category-email-subscribe` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add Subscribers in  St Category Email Subscribe > Subscribers (See How to use in Other Notes)
3. Place [st_category_subscribe_form] in your page/post where you want to display the subscriber form
4. You may also use the Widget : Category Email Subscribe Form to display subscriber form

== How To Use ==
1. Go To **St Category Email Subscribe** In Side Menu
2. Enter the Send Email from Email and Name in Settings
2. Add **Subscribers** by :
a. Allow users to Subscribe using Subscription Form
   You can either use the widget to display the Subscription from
   Or use shortcode [st_category_subscribe_form] to display subscription form.
b. Upload a Subscriber Manually
   Go to **St Category Email Subscribe > Subscriber **
   Go to **Add a Subscriber**
   Enter the details and press button *Subscribe*
c. Upload using CSV File
   The Format of CSV File must be as below :
     *The First line must be headers as it is ignored while uploading.*
     From the second line, the data should begin in following order :
		**Name,Email,Category ID**
         *Category ID* : 0 for all categories, Category ID for a particular category.
3.  The Added Subscribers will be shown in the table 
5. 	You can Unsubscribe the Subscriber by select the emails and using the **Unsubscribe** button 

== Changelog ==



== Upgrade Notice ==










