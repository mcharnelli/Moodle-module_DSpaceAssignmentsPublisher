
* Place the sword folder into the /mod folder of the moodle
  directory.

* Go to Settings > Site Administration > Development > XMLDB editor
  and modify the module's tables.
  Make sure, that the web server has write-access to the db/ folder.
  You need at least one table, even if your module doesn't use it.

* Modify version.php and set the initial version of you module.

* Visit Settings > Site Administration > Notifications, you should find
  the module's tables successfully created

* Go to Site Administration > Plugins > Activity modules > Manage activities
  and you should find that this sword has been added to the list of
  installed modules.

* You may now proceed to run your own code in an attempt to develop
  your module. You will probably want to modify mod_form.php and view.php
  as a first step. Check db/access.php to add capabilities.

We encourage you to share your code and experience - visit http://moodle.org

Good luck!
