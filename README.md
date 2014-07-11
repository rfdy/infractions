phpBB Extension - Infractions System
====================================

This is an infractions extension for phpBB 3.1 and up. The extension allows administrators to define default infractions with set points such that users can be automatically assigned to groups when a points threshold is reached.

Installation instructions
--------------------------
- create $phpbb_root/ext/rfd folder
- cd $phpbb_root/ext/rfd
- git clone https://github.com/rfdy/infractions.git
- Go to admin panel -> customize -> extensions. Click on Enable for infractions
- Go to user and groups -> infractions. Set rules URL to the correct value (used in the PM sent on an infraction)
- Make sure your site name is set in General -> Board settings

Permissions
-----------
Once installed the extension makes available a single admin permission which can be applied to administrators to manage infractions a rules.
It also makes available two moderator permissions one for issuing an infraction and another for removing existing infractions.
