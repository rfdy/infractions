phpBB Extension - Infractions System
====================================

This is an infractions extension for phpBB 3.1 and up. The extension allows administrators to define default infractions with set points such that users can be automatically assigned to groups when a points threshold is reached.

Installation instructions
--------------------------
- create $phpbb_root/ext/rfd folder
- cd $phpbb_root/ext/rfd
- git clone https://github.com/rfdy/infractions.git
- Edit $phpbb_root/ext/rfd/infractions/language/en/common.php, set FORUM_NAME and RULES_URL to meaningful values for your forum
- Go to admin panel -> customize -> extensions -> install infractions

Permissions
-----------
Once installed the extension makes available a single admin permission which can be applied to administrators to manage infractions a rules.
It also makes available two moderator permissions one for issuing an infraction and another for removing existing infractions.
