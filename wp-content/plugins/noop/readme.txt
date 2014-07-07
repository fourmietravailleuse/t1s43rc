=== Noop ===

Contributors: ScreenfeedFr
Tags: framework, settings, options
Requires at least: 3.3
Tested up to: 4.0-alpha
Stable tag: trunk
License: GPLv3
License URI: http://www.screenfeed.fr/gpl-v3.txt
Added: 2013-08-01
Plugin URI: http://www.screenfeed.fr/blog/
Contributors profile: http://profiles.wordpress.org/greglone/

Noop is a framework to use within other plugins or themes, it does nothing by itself.


== Description ==

Noop's aim is to provide a secure and robust foundation, and also powerful tools, to build great settings pages and manage options.
It can be used for a plugin or a theme, and help you with your options pages, options, and post metas.
Another thing about Noop, it doesn't use a shiny-custom-awesome-wow layout to build the settings pages, it simply looks like... WordPress.

= Translations =

* English
* French

= Multisite =

Yes!

= Multilang =

Yes! If the multilingual plugin uses (and modifies) the value of the WordPress locale (which is the best way in my opinion), it will work. For example, the plugins WPML and Polylang do that.
Moreover, if the user uses WPML or Polylang, the language switchers are in sync in the settings page.


== Installation ==

1. Extract the plugin folder from the downloaded ZIP file.
1. Upload the `noop` folder to your `/wp-content/plugins/` directory.
1. Activate the plugin from the "Plugins" page.
1. Noop can also be used as a "Must Use" plugin, but of course, the updater won't work anymore.


== Changelog ==

= 1.0.8 =

* 2014/06/26
* New: Polylang support in your settings pages. Now the Polylang switcher and the one from Noop are synced. All the filtering functions have been renamed (previously used for WPML only).
* Bugfix for WPML support in the settings pages: removed some Warnings and Notices if WPML is activated but not configured yet.
* Bugfix: WordPress has removed some translated strings. Add them back with the plugin text domain. Updated `noop_upload_field()`.
* Bugfix in `noop_find_item_field()` and `wp_ajax_noop_find_posts()`: added `suppress_filters` (false) parameter in the posts query arguments.
* Improvement: added 4 hooks in `wp_ajax_noop_find_posts()` and 2 hooks in `noop_find_item_field()`.
* Improvement: for `noop_find_item_field()`, now the select window closes after selection if `multiple` is false.
* Improvement: `noop_find_item_field()` now also accepts an array of IDs (previously, only a string comma-separated list of IDs).
* Improvement: since WordPress 4.0 seems to make some changes AGAIN in the JavaScript files, it feels more reliable to not use media.js anymore, but my own file instead (script used for the "find posts / find users" window). Also, a custom function `noop_find_posts_div()` is used now: the aim is to get rid of some duplicate IDs (but some of them are still duplicates if the original window is present, because some css styles are needed).
* Improvement: small tweak to add a <code>&lt;br/&gt;</code> tag between a textarea and its label (and add a margin between the label and the textarea).
* Improvement: min-height for "metaboxable areas" from 100px to 58px.
* Code improvement: added lots of parenthesis here and there, according to the new WordPress coding standards.
* Small line-height bugfix for select/input/textarea in Firefox 30 (I guess it will be added in WordPress later).
* Updated Leaflet to 0.7.3 and Leaflet.zoomslider to 0.6.1.

= 1.0.7 =

* 2014/04/28
* Hotfix: hook `Noop_Admin::register_settings()` to `admin_init` to avoid a fatal error.

= 1.0.6 =

* 2014/04/27
* New: Import / Export settings via a file. See the new tab in the Help panel. The name of the file and its content can be filtered.
* New: Now we can build "settings" pages with only "action" tabs (no options management). Just don't use the `option_group` parameter.
* New hook in `Noop_Settings::settings_page()`: `{$page_name}_tab_form`, that's where you should add your "action" forms. Previously, `{$page_name}_after_form` was used (hook still available). The aim of this change is to allow the use of the "find items" field in "action" forms.
* New public method `Noop_Options::has_blog_option()`: tells if something is already saved in the database.
* Bugfix in `wp_ajax_noop_find_posts()`: the WordPress version comparison was badly handled (╯°□°）╯︵ ┻━┻
* Bugfix for `Noop_Admin::register_settings()`: previously hooked to `admin_menu`, now to `wp_loaded`. Since `admin_menu` is not triggered in every admin area, `wp_loaded` is a better choice.
* CSS fixes.
* Some other minor internal changes.
* Updated the `.po` and `.mo` files.

= 1.0.5 =

* 2014/04/16
* Small code improvements in `Noop_Fields`.
* Bugfix in `sf_updates_exclude()`: the updater didn't remove Noop from the normal updates sometimes.
* Compatibility fixes for the "Find Posts" modal in WP 3.9: updated `wp_ajax_noop_find_posts()` and `settings(.min).js`.
* Small bugfix in `noop_find_item_field()`: a php notice on rare occasion.

= 1.0.4 =

* 2014/02/22
* Split the `Noop::is_settings_page()` method into `$instance->is_instance_settings_page()` and `Noop::is_settings_page()` to avoid a php notice. Changed it in `Noop_Admin::__construct()` && `Noop_Admin::register_menu_and_help()`. If you used Noop in your own plugin or theme, be sure to check your code.
* Small improvement in `Noop_Options::add_option_to_history()`: replaced 'mktime()' with `time()`.

= 1.0.3 =

* 2014/01/24
* Bugfix in `Noop_Settings::get_instance()`: compatibility with php 5.2.
* Improvement: don't add *admin-* before the dashboard icon class.

= 1.0.2 =

* 2014/01/11
* New: a new year :)
* New: new value `'posts-thumb'` for the parameter `what` in `noop_find_item_field()`: posts will be displayed (and ordered) as thumbnails (like in `noop_upload_field()`). Only posts with a post thumbnail and image attachments can be used this way.
* New: added `Noop_Utils::esc_js()` (like `esc_js()` but unslash the output).
* New: added a `$icon16` parameter to `Noop_Fields::section_icon()` and `Noop_Fields::add_section_icon()` to force the "old" icon16 classes.
* New: added a function `noop_uninstall()` in `includes.php`.
* Improvement: if a mime type is provided in `Noop_Post_Metas::get_metaboxes()` (metabox parameters), the mime type will be checked in `Noop_Post_Metas::save_metas()`.
* Small CSS improvements.
* Bugfix in `Noop_Post_Metas::__construct()`: init regression.
* Bugfix in `Noop_Post_Metas::metabox_form()`: some nonce fields were not printed when running multiple instances of `Noop_Post_Metas`.
* Bugfix in `Noop_Post_Metas::get_metas()` when `multiple_metas` is true and the metas does not exist.
* Bugfix in JavaScript when open/close metaboxes.

= 1.0.1 =

* 2013/12/21
* Updater improvements.
* Important bugfix in `Noop_Post_Metas::sanitization_functions()`: removed the $post_type parameter in `$this->escape_functions()`.
* Init improvements in `Noop_Post_Metas`, changed some actions.
* Added an action hook "noop_before_save_metas" in `Noop_Post_Metas::save_metas()`, before dealing with the sent data.
* Bugfix in `noop_upload_field()`: added parameter in `wp_enqueue_media()` to avoid to break the library in post edition.
* CSS improvements for metaboxes in sidebar.

= 1.0 =

* 2013/12/16
* After many months, the first stable version is finally out!

= TODO =

* Build demos and documentation (ehr...).
* Release the kraken on github.