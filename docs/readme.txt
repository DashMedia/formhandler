FormHandler
-----------

Plugin runs on every page render, checks for a $_POST variable of formhandler if this is set, it will then check for email_address and use system and template variables to decide what to do with it.

Two-Step Form Submissions
-------------------------

Set a $_POST variable of fh_2step to 1 (first step) on the inital submission, and then set it to 2 (second step) on the final submission.

the from_page variable on step one will be used for inital TV values, but the from_page variable on step two will override them.

Storing Variables in CM
-----------------------

Add the variables to the fh_cm_variables_to_store tv on the page which the from_page varible is pointing to. These values will be stored in CM as Text fields.

Author: Jason Carney <jason@dashmedia.com.au>
Copyright 2015

Official Documentation: https://github.com/DashMedia/formhandler