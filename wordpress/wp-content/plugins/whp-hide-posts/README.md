=== Wordpress Hide Posts ===
Contributors: martin7ba,conschneider
Tags: hide posts, hide, show, visibility, filter, woocommerce, hide products, rss feed
Requires at least: 4.0
Tested up to: 5.4.1
Requires PHP: 5.6
Stable tag: 0.5.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows you to hide any posts on the home page, category page, search page, tags page, authors page and RSS Feed
Also you can hide Woocommerce products from Shop page as well as from Product category pages.

To enable it for Woocommerce products or any other Custom Post type go to Settings -> Hide Posts and select the post type.

When you create new or edit post, you can choose on which archive page to hide that post as well as on the home page.

NOTE: The option to hide post from REST API added in version 0.4.3 is now removed in version 0.5 due to conflict with Guttenberg save / update post funcionality.
It will stay removed until a permanent fix is found for this issue.

== Installation ==

1. Upload the `whp-hide-posts` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enable Hide post functionality for additional post types by going to Settings -> Hide Posts

== Changelog ==

= 0.5.1 =
*Release Date - 19 May 2020*

* Added option to hide posts default WordPress post navigation
* Fix for hiding menu items bug

= 0.5.0 =
*Release Date - 17 April 2020*

* Removed option to hide post from REST API added in version 0.4.3 due to conflict with Guttenberg save / update post.
The conflict happens because Guttenberg is using the REST API to save post and load additional data the hide post on REST API was causing conficts with the data.

= 0.4.4 =
*Release Date - 14 April 2020*

* Added option to hide post on date archive page

= 0.4.3 =
*Release Date - 06 April 2020*

* Added option to hide posts from REST API all posts query: /wp-json/wp/v2/posts/
Note: Single post entry in REST API remains available /wp-json/wp/v2/posts/[post_id]

= 0.4.2 =
*Release Date - 13 February 2020*

* Bug fix

= 0.4.1 =
*Release Date - 13 February 2020*

* Workaround added for issue showing warnings when is_front_page() function is called in pre_get_posts filter. This is related to wordpress core and can be tracked at [#27015](https://core.trac.wordpress.org/ticket/27015) and [#21790](https://core.trac.wordpress.org/ticket/21790)

= 0.4.0 =
*Release Date - 21 December 2019*

* Added option to hide posts on the blog page as selected in Settings -> Reading (Posts Page)

= 0.3.2 =
*Release Date - 13 December 2019*

* Bug fix for checking if Woocommerce is active on mutlinetwork site

= 0.3.1 =
*Release Date - 07 December 2019*

* Added option to hide posts in RSS Feed
* Added options to hide Woocommece products on Store (Shop) page and on Product category pages

= 0.2.1 =
*Release Date - 13 November 2019*

* Compatibility checkup with Wordpress 5.3
* Added option to enable the Hide Post functionality for additional post types (Check Settings -> Hide Posts)
* Added uninstall.php file to clean up options and post meta added by the plugin

= 0.1.1 =
*Release Date - 05 September 2019*

* Code and compatibility updates.
* Added translateable strings.

= 0.0.1 =
*Release Date - 11 October 2017*

* Public release of 'Wordpress Hide Posts'