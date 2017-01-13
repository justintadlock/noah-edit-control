# Edit control

This is a custom plugin for control editing permissions.

## Page contributors

The page contributors feature allows administrators and editors to assign contributors to pages.  In order for a user to be a contributor, they must have at least one of the following capabilities:

* `edit_pages`
* `create_pages`
* `publish_pages`

Contributors to a page can be assigned via the "Page Contributors" meta box on the edit page screen in the WordPress admin.

**Note #1:** _To allow other users to assign page contributors, you can assign them the `manage_page_contributors` capability via the Members plugin._

**Note #2:** _In order to make this specific plugin function correctly, it was necessary to introduce an additional capability named `create_pages`.  Only users with that capability can create new pages._

## User post categories

The user post categories feature allows administrators to assign post categories to specific users.  This will appear as a section titled "Post Categories" on the edit user screen.  There'll be a box with all available categories.  The administrator need only tick the checkboxes they want for the user.

This box will only appear on user's profile screen if they have one of the following capabilities (otherwise, it's unnecessary):

* `edit_posts`
* `publish_posts`

When the user publishes a post (or if someone else is editing their post), the only categories that will appear in the "Categories" meta box will be the categories assigned to the specific user.

**Note #1:** _To allow other users to assign post categories, you can give them the `manage_user_categories` capability via the Members plugin.  Of course, they'd need to be able to edit users as well._
