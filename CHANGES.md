[unreleased]
#### 3.0.4 / 2024-02-09
* update kill switch

#### 3.0.1 / 2023-11-21
* fix for multisite, too many `%s`

#### 3.0.0 / 2023-10-06
* override `WP_Plugins_List_Table` to add filter and restructuring of PR
* convert to static class
* update plugin card description for clarity
* update Requires WP to 6.4 due to `wp_admin_notice()` use
* many more updates to coincide with refactoring of PR

#### 2.0.2 / 2023-08-18
* add single file plugin to `$plugin_dirnames`

#### 2.0.1 / 2023-08-16
* cleanup

#### 2.0.0 / 2023-08-08
* remove Dependencies tab, Manage Dependencies link, etc, per @azaozz
* skip associated PHPUnit tests
* increase scope to protected for many things
* remove `class Init`, not needed
* deactivate buttons, don't change text

#### 1.14.3 / 2023-70-30
* add null coalesce
* require PHP 7.0
* make commit guard more permissive

#### 1.14.2 / 2023-07-20
* update guard in `get_dependency_filepaths()`

#### 1.14.1 / 2023-07-20
* update modal button on plugin-install.php

#### 1.14.0 / 2023-07-19
* update _More details_ link
* fixed strange error between slug from different sources in PD part 2
* update JS to correctly display Plugin Card button, thanks @costdev

#### 1.13.0 / 2023-07-10
* update version check
* simplify plugin card notice

#### 1.12.1 / 2023-07-01
* extra life to 6.4-beta1

#### 1.12.0 / 2023-05-21
* change plugin card button to 'Cannot Install' if dependencies not met
* override `WP_Plugin_Install_List_Table::display_rows()` to use our refactored `wp_get_plugin_action_button()`

#### 1.11.0 / 2023-05-21
* add **Requires:** data to plugin cards of uninstalled plugins where repo plugins have `Requires Plugins` header set
* add temporary style kludge to above
* add caching to uninstalled plugin data
* abstract code to create plugin install action buttons

#### 1.10.0 / 2023-04-29
* show `Cannot Install` button in Dependencies tab for dependencies with no package
* return of generic plugins_api() response to it's own hook, avoids having to hide items in plugin card
* add more data to generic plugin card
* update for WP-CLI
* no need to start on hook

#### 1.9.0 / 2023-04-10
* ensure WP 6.0 compatibility with `move_dir()`
* use JSON in plugin root for non-dot org dependencies _acceptable_ for dot org 🤞
* update test plugins
* run hooks during AJAX in case you really want an Install to happen
* update regex to strictly follow plugin repository slug format with tests

#### 1.8.0 / 2023-04-07
* update to work natively with `<slug>|<URI>` format in `Requires Plugins` header
* split PD and PDv2 into different classes
* add more tests

#### 1.7.9 / 2023-04-05
* update action link to keep `Cannot Activate | Manage Dependencies` together
* fix for multisite plugin card

#### 1.7.8 / 2023-03-03
* composer update

#### 1.7.7 / 2023-02-11
* add a11y that I (@afragen) clearly forgot, it's a start
* fix circular dependency test plugins to have containing folder, dependencies must have a containing folder

#### 1.7.6 / 2023-02-11
* update `Name` header of test plugins so they can't be mistaken for core plugin after AJAX Install

#### 1.7.5 / 2023-02-09
* cleanup docblocks
* initialize during class loading

#### 1.7.4 / 2023-02-08
* composer update

#### 1.7.3 / 2023-01-30
* composer update using Composer 2.5.0 to avoid bug

#### 1.7.2 / 2023-01-02
* add unresolvable circular dependency example
* update for PHP standards

#### 1.7.1 / 2022-10-27
* remove "improved visibility" of `Dependencies` link

#### 1.7.0 / 2022-10-25
* notification of circular dependencies
* add info text under Dependencies tab, I found a hook 🙌
* display admin notices on specific pages
* added some code improvements, thanks Colin
* add `Requires:` data to plugin card
* modify plugin card action links if dependency not met
* improve visibility of `Dependencies` link

#### 1.6.2 / 2022-10-18
* composer update better checking in `afragen/add-plugin-dependency-api`

#### 1.6.1 / 2022-10-18
* more precise check of dependency slug for file path
* don't show admin notice to users who are unable to act upon them
* update composer dependencies
* add skeleton JSON response for Gravity Forms

#### 1.6.0 / 2022-10-15
* move `plugin_dependency_endpoints` hook outside of class
* composer update
* add filter `wp_plugin_dependencies_slugs` to modify slugs in cases of non-premium plugin replaced with premium plugin
* keep checking plugins API for plugin with generic response
* update conditional for generic response
* update testing plugins

#### 1.5.1 / 2022-09-02
* fix for actual `gravityforms` slug

#### 1.5.0 / 2022-09-02
* add `afragen/add-plugin-dependency-api` as composer requirement
* update test plugins removing `hello-dolly` and adding `git-updater` as non-dot org example
* check empty plugin response for error

#### 1.4.1 / 2022-08-18
* oops, fixed typo in one of the testing plugins

#### 1.4.0 / 2022-07-28
* bring more inline with PR
* remove action on class requires, use hook
* fix multisite compatibility

#### 1.3.0 / 2022-07-04 🎆
* fix `get_requires_plugin_names()` to account for empty header
* update regex to allow for some non-ascii languages and symbols as slugs

#### 1.2.1 / 2022-06-23
* added several single file testing plugins to `test-plugins/`

#### 1.2.0 / 2022-06-10
* don't display admin notice link to Dependencies tab when on Dependencies tab
* be more specific about only removing dependency plugin row checkbox when a requiring plugin is active

#### 1.1.1 / 2022-06-06
* limit scope of class methods where we can
* update screenshots

#### 1.1.0 / 2022-06-02
* change 'Activate' plugin action link to 'Cannot Activate' text when plugin has unmet dependencies
* remove checkbox from plugin row when plugin has unmet dependencies
* use _View details_ link for plugins listed in **Requires:** in plugin row

#### 1.0.0 / 2022-05-31 🎂
* fix typo
* initial dot org release

#### 0.16.2 / 2022-05-27
* update requirements to WP 6.0

#### 0.16.1 / 2022-05-24
* add auto-deactivate for when committed to trunk, will need updating later

#### 0.16.0 / 2022-05-08
* rename `parse_headers()` to `parse_plugin_headers()`, future proofing
* update unit tests

#### 0.15.1 / 2022-04-29
* minor cleanup

#### 0.15.0 / 2022-04-28
* refactor with `get_requires_plugins_names()`
* update admin notice for multisite

#### 0.14.0
* updated required plugin data expiration

#### 0.13.1 / 2022-04-25
* fix `parse_headers()`

#### 0.13.0 / 2022-04-23
* prep for initial release

#### 0.12.9 / 2022-04-19
* add plugin cards for slugs with no API data
* hide action links and bottom of card in plugin cards for slugs with no API data

#### 0.12.3
*rename and reschuffle some functions

#### 0.12.2 / 2022-04-06
* harden a bit
* clean up some testing stuff
* `plugin_install_description` filter committed to core

#### 0.12.0 / 2022-04-03
* readme.txt
* fix PHP error if no plugins with `Requires Plugins` header found
* only show single, relevant admin notice

#### 0.11.6.4
* plugin to date with new changelog
