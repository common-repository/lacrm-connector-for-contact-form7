=== CF7 LACRM Connector ===
Contributors: sooskriszta, westerndeal, julius1986
Donate link: http://tinyurl.com/oc2pspp
Author URL: http://profiles.wordpress.org/sooskriszta
Tags: CF7, Contact Form 7, Contact Form 7 Integrations, Contact Forms, Contact Forms, LACRM, Less Annoying CRM, LACRM Integrations, LessAnnoyingCRM, CRM
Requires at least: 3.4.1
Tested up to: 4.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Send your Contact Form 7 data directly to your Less Annoying CRM account. 

== Description ==

This plugin is a bridge between your [WordPress](https://wordpress.org/) [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) forms and [Less Annoying CRM](https://www.lessannoyingcrm.com/Tour)  

When a visitor submits his/her data on your website via a Contact Form 7 form, upon form submission, such data are also sent to your Less Annoying CRM account.  

The plugin creates a new contact in LACRM, and can create new pipelines, tasks, and notes as necessary.  

= How to Use this Plugin =
Please see [screenshots](https://wordpress.org/plugins/lacrm-connector-for-contact-form7/screenshots/) to see settings details.  
- Create or Edit the Contact Form 7 form from which you want to capture the data. Set up the form as usual in the Form and Mail etc tabs. Thereafter, go to the new LACRM tab.  
- On the LACRM tab you need to map the form fields on to LACRM fields. For each LACRM field, provide the corresponding Contact Form 7 mail tag, the same way you do on the Mail tab.  
- If you'd like to create a Pipeline in LACRM for this newly created contact, you can provide LACRM Pipeline info.  
- If you'd like to create a Task in LACRM attached to this newly created contact, you can provide details of the task, including when it is due and which LACRM user it is for.  
- Test your form submit and verify that the data shows up in your LACRM.
- Have a beer and celebrate! 

= Important Notes =
- You must pay very careful attention to your naming and entering proper ID information. This plugin will not give you results if ID’s is mismatch.  
- We value your feedback. Let us know if there are other creative ways you want to use this.

== Installation ==

Prerequisite: [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)  
Make sure this is installed and active before installing CF7 LACRM Connector.

1. Upload `lacrm-connector-for-contact-form7` to the `/wp-content/plugins/` directory, OR `Site Admin > Plugins > New > Search > CF7 LACRM Connector > Install`.
2. Activate the plugin through the 'Plugins' screen in WordPress.  
3. Use the `Admin Panel > Contact form 7 >LACRM` screen to connect to LACRM by entering the LACRM User Code and User Token.
Enjoy!

== Frequently Asked Questions ==

= What is an Less Annoying CRM? =

- [Less Annoying CRM](https://www.lessannoyingcrm.com/Tour) is an easy-to-use CRM system.  

= Is Contact form 7 required? =

- Yes [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) is a prerequisite.

= Why to use this plugin? =

- If you use Less Annoying CRM as your customer relationship management system, and have a WordPress website, then this plugin can automate the process of capturing website leads directly to the CRM.

= How do I know it's working? =

- Try submitting a form, and then search for those details in Less Annoying CRM. If the details appear in the search results, it works!

= The submitted information was not sent to LACRM. Now what? =

- Most often the reason is that the Site Admin skipped step 3 of Installation. Step 3 tells this plugin the details it needs to connect to LACRM. If the plugin doesn't have these details, it's like not having the username and password for an LACRM account...hence the plugin can not log into LACRM and add any info.

= Where can I find the LACRM user code and token required in step 3 of Installation? =

- On the [LACRM API page](https://www.lessannoyingcrm.com/account/api/)

== Screenshots ==

1. Installation step 3 - LACRM Connect Page.
2. Edit form - LACRM tab.

== Changelog ==

= 1.2 =
* Added .pot file for easier translations

= 1.1 =
* Added image assets
* Changed plugin name.
* Changed plugin description etc.

= 1.0 =
* Release of LACRM Addon Plugin which Connects with contact form 7
* API Integration
* Custom Tab under Existing Contact forms to connect with LACRM


