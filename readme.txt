=== Yahoo BOSS ===
Contributors: picklewagon 
Donate link: http://picklewagon.com/wordpress/yahoo-boss/donate
Tags: search, yahoo
Requires at least: 2.7
Tested up to: 3.0.2
Stable tag: 0.7

The Yahoo BOSS plugin makes it easy to create a search engine in your WordPress install. This plugin does not replace the default search.

== Description ==

The Yahoo BOSS plugin makes it easy to create a search engine in your WordPress install. Simply activate and configure and you're off and running. This plugin does not replace the default search within WordPress.

[Yahoo Search BOSS][1] (Build your Own Search Service) allows you to create a search engine using Yahoo technologies via a REST API. Currently, only web content search (images and news coming soon) is supported. You will need to get your own [BOSS application ID][2] to create your search engine. Use of this plugin subjects you to the [BOSS API Terms of Use][3]. Please read carefully.

In order for your search engine to function correctly, you need to define where you want both the search form and the search results to show. This is done with simple shortcodes that you need to add to a page or pages.

To create the search form add [boss\_search\_form] to a page.

To define where you want your search results use [boss\_search\_results]. Nothing will show if a query hasn't been performed.

The page contents would be the following if you wanted a search form followed by the search results.

[boss\_search\_form]

[boss\_search\_results]

Use [pw\_search] to combine both shortcodes.

Alternatively, there are numerous template functions available if you prefer to use those over shortcodes.

That is all you need to do to create your custom search engine.

 [1]: http://developer.yahoo.com/search/boss/
 [2]: http://developer.yahoo.com/wsregapp/
 [3]: http://info.yahoo.com/legal/us/yahoo/search/bosstos/bosstos-2317.html
	
== Installation ==

1.  Download this plugin.
2.  Upload the plugin to the wp-content/plugins directory on the server where your site is located.
3.  Activate the plugin through the 'Plugins' menu in WordPress
4.  Add your Yahoo BOSS application ID on the configuration page or to wp-config.
5.  Configure as needed.
6.  Create the page(s) where you want the search form and search results using the shortcodes defined.
7.  Search away!

== Frequently Asked Questions ==

= How many sites can this plugin limit searches to? =

You can add as many as you would like, but the Yahoo BOSS API will only support a limited amount. Each site url is added to the URL when performing the search, so there is a definite limit to the API. More sites can be added by using YQL.

= When a user misspells a search term, why aren't any suggestions offered? =

This is not supported by the API. So this support would need to be built using another service.

== Screenshots ==

1. The admin panel

== Changelog ==

= 0.7 =
* wrap options in a metabox
* add sidebar with some meta widgets (support, share, donate, etc.)
* add some descriptors to the options page
* use WP_Http to make the API call instead of WP_Http_Curl
* clean up the options form
* add a nonce for the form update
* use manage_options instead of the deprecated level 10 on the options page
* add localization

= 0.6 =
* remove plugin constants that have been defined since WordPress 2.6
* added FAQ to the readme.txt file
* added a changelog to the readme.txt file
* updated styles on search form
* add filter for result info
* more templating functions
* correctly handle search if no results retrieved
* use the WP_Http_Curl instead of directly making curl requests
* remove PHP 5 required message when using JSON
* use wp_enqueue_style to load style sheet
* rename default stylesheet to search-styles.css
* create a pw_search shortcode that combines the search form and results
* compatible with WordPress 2.9.1

= 0.5 =
* initial version

== Other Notes ==

### Administration

You need to modify a few options for the plugin to work correctly.

<span style="text-decoration: underline;">Yahoo App ID</span>: add your [Yahoo BOSS application ID][1] here. This option is required.

<span style="text-decoration: underline;">Results per Page</span>: use to configure how many search results you want to show on each page. Default is 10.

<span style="text-decoration: underline;">Format</span>: the format of the search results. This would be useful if you want to use with AJAX. Default is XML.

<span style="text-decoration: underline;">Domains to Search</span>: add site urls here if you want to limit your search to certain sites (one per line).

### Extending

Yahoo App ID can be overridden with a defined constant in wp-config.

    define('YAHOO_APP_ID', 'your_app_id_goes_here');

There are some filters available to use.

pw\_boss\_title - applied to the title of the search engine in the search form. Filter function arguments: title.

pw\_boss\_button - applied to the button to initiate the search in the search form. Filter function arguments: button text.

pw\_boss\_stylesheet - applied to the search engine stylesheet to provide your own custom stylesheet. Filter function arguments: default stylesheet.

pw\_boss\_result\_info - modify the result meta information (1-10 of 201321 results for WordPress)

 [1]: http://developer.yahoo.com/wsregapp/