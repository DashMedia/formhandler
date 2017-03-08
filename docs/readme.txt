FormHandler
-----------

Plugin runs on every page render, checks for a $_POST variable of formhandler if this is set, it will then check for email_address and use system and template variables to decide what to do with it.

Two-Step Form Submissions
-------------------------

Set a $_POST variable of fh_2step to 1 (first step) on the inital submission, and then set it to 2 (second step) on the final submission.

the from_page variable on step one will be used for inital TV values, but the from_page variable on step two will override them.

Storing Variables in PHP SESSION
--------------------------------

Add the submitted form data to the PHP SESSION. Add fh_store_in_session tv to the page which the from_page is pointing to. They will be stored in $_SESSION['fh_data']

Storing Variables in CM
-----------------------

Add the variables to the fh_cm_variables_to_store tv on the page which the from_page varible is pointing to. These values will be stored in CM as Text fields.

You may create the field in Campaign Monitor field (note the name must match the field name exactly) and the created field type will be used instead of the basic text field type

Selecting send to address based on form value
-----------------------

Add a Email Destination input type to the form containing all your options

Sending via Postmark
-----------------------

If Postmark server token is included mail will be sent via the Postmark API's instead of the MODX mailer service


Custom HTML Email Handling
-----------------------

OnFormHanderEmailRender system event is fired before sending email's, this event passes a referance to $email_handler which contains three useful vars:

- $email_handler->fields: array of filds passed from form (have been processed already)
- $email_handler->rendered_html: contents of HTML which is going to be sent, you can modify this to send somthing different
- $email_handler->rendered_text_only: plain text version of email, you can modify this to send somthing different

Author: Jason Carney <jason@dashmedia.com.au>
Copyright 2015

Official Documentation: https://github.com/DashMedia/formhandler
