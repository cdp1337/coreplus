# User Permissions

User permissions allow arbiturary permissions to be applied to certain groups throughout the site, wherein users can be assigned to the groups.  This provides a relatively simple way of managing roles throughou the entire application.

## Permission

A permission is a rather arbiturary item; it has no database representation and is not saved anywhere.  It's loaded in with the component.xml information when the respective component is loaded.  These strings should have a description so the user manager knows what each means.  The specific components can utilize each of their permissions in any way, but calling \Core\user()->checkAccess('p:myrandompermission') will go through and see if the current user's groups have the "myrandompermission" assigned to any of them.  If true, the check returns true; false else.

The user manager has the list of groups, and upon editing the permissions for each group, the loaded permissions will be displayed, allowing them to be toggled as necessary.

## Changes

If a component is updated and the permission name is changed, that user manager must go back and re-enable all permissions for the requested groups.  Please make notes of permission name changes for this reason!

## Admin

As always, users flagged as "admins" inherit all permissions automatically.

## "Not" Permissions

Due to how users can inherit multiple groups, having a permission that is designed to "Not" allow something is not recommended.  This is because if user1 is a member of GroupA and GroupB, the permissions of each two groups are combined.  If GroupB has "p:cannot-post-something", that permission is applied to the user since they're simply combined.  If this is desired behaviour however, feel free to use the system like this.

## Lookups

The assigned permissions for each group is simply stored within a json-encoded array in the group table.  As such, lookups to see which groups have a given permission are expensive and not recommended.
