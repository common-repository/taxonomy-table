=== Taxonomy (Category, Tag, ...) Table by Eyga.net ===
Contributors: DSmidgy
Donate link: http://blog.slo-host.com/wp-plugins/
Tags: taxonomy, categories, tags
Requires at least: 2.8
Tested up to: 4.9
Stable tag: 0.6.0

Creates a HTML table form subitems of a specific taxonomy (category, tag, etc.) ID.

== Description ==

You specify one item in a taxonomy hierarchy and a HTML table gets created for its subitems. Only taxonomies which actually have posts attached are added into a HTML table. For now, the only supported taxonomies are categories.

== Installation ==

Standard installation, no customization needed.

On the "Taxonomy Table Options" page, found under "Settings" in "Admin site", you must enter specific IDs (numbers) that can be found on:<br>
* edit a page or post and find an ID in the URL: http://yoursite.com/wp-admin/post.php?post=<b>18123</b>&action=edit<br>
* choose the taxonomy type for which you will enter the taxonomy ID for<br>
* edit a taxonomy (like category) which has subitems attached and find an ID in the URL: http://yoursite.com/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=<b>2595</b>&post_type=post<br>
* specify how many columns the rendered HTML table will have<br>
* display records from top to bottom (vertical) or from left to right (horizontal)<br>

== Frequently Asked Questions ==

To do:<br>
* support for other taxonomy types, not just categories<br>
* for now, the options support only one taxonomy HTML table per Wordpress installation - this will be enhanced only on a request<br>
* make options page prettier<br>

== Screenshots ==

1. Available options in the latest version.
2. Example of rendered subcategories in otherwise empty page.

== Changelog ==

= 0.3 =
* First customizable version.
= 0.3.1 =
* No change to the code.
= 0.3.2 =
* Post content didn't show.
= 0.4 =
* Added option to render above or below page content.

== Upgrade Notice ==

= 0.3 =
* First version.
= 0.3.1 =
* Changed readme.txt and added screenshots.
= 0.3.2 =
* Major problem - content of the posts were ignored.
