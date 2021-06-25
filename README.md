# Custom module

In this example custom module is installed already. Just need to check link in admin panel or in main menu to check completed task.
If you want to install this module on other drupal enviroment, make sure that subtheme /file is not blocked by the theme. If opposite form will result in error. Also you need to add 
    - core/drupal.dialog.ajax
    - core/jquery.form
    as dependencies for theme libraries.yml so delete button would work as intended.

