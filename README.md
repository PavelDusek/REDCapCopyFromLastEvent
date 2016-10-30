# REDCapCopyFromLastEvent
A hook for REDCap (https://projectredcap.org/) that enables you to use @COPYFROMLASTEVENT action tag. It adds a button that enables you to copy value of a parameter from a different event.

# Installation
If you are not using any other hook, put hooks.php file to the redcap root directory on the web server and set the path to it in the Control Center -> General Configuration -> Other system settings -> REDCap Hooks.

If you are using other hooks, rename the file hooks.php and make sure the function redcap_data_entry_form_top() is defined and this file is included in its body.
