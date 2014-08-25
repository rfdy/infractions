phpBB Extension - Infractions System
====================================

This is an infractions extension for phpBB 3.1 and up.

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


Details
-------

- The extension allows administrators to define default infractions (through the Administrator Control Panel)
with set points and duration such that users can be automatically assigned to groups when a points threshold is reached.

- Users will receive points when they are issued an infraction by a moderator for forum misconduct.
- In the ACP, you can create Rules which contain a point threshold and group, so that when a user receives a certain
number of points, they will be placed in that group.
- Ideally, you would select a group which takes away forum privileges by removing permissions. The permissions that you
 remove in each group should be increasingly harsh as the user gains more infraction points.

- From forum posts and in the Moderator Control Panel, a moderator can issue infractions to users. A moderator will
be able to select from default infractions (which can be managed in the ACP), modify the points being given and duration
as they see fit for a particular incident, and provide an optional message to the recipient of the infraction.

- If an infraction was given to a user by mistake or a particular infraction given needs to be readjusted, a moderator
with the cancel permission can cancel that infraction via the MCP and then give new a new infraction with custom points
and duration if necessary.


