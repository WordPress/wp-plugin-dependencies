# Plugin Dependencies

Contributors: afragen, costdev
Description: Parses 'Requires Plugins' header, add plugin install dependencies tab, and information about dependencies.
License: MIT
Network: true
Requires at least: 6.0
Requires PHP: 5.6
Tested up to: 6.1
Stable tag: 1.7.1

## Description

Parses a 'Requires Plugins' header and adds a Dependencies tab in the plugin install page. If a requiring plugin does not have all its dependencies installed and active, it will not activate.

[Make post for Plugin Dependencies Feature Project](https://make.wordpress.org/core/2022/02/24/feature-project-plugin-dependencies/)

Please open issues at [WordPress/wp-plugin-dependencies issues](https://github.com/WordPress/wp-plugin-dependencies/issues)

My solution to [#22316](https://core.trac.wordpress.org/ticket/22316). Feature plugin version of [PR #1724](https://github.com/WordPress/wordpress-develop/pull/1724)

* Parses the **Requires Plugins** header that defines plugin dependencies using a comma separated list of wp.org slugs. To test, you will need to add the header and content to a plugin.
* Adds a new view/tab to plugins install page ( **Plugins > Add New** ) titled **Dependencies** that contains plugin cards for all plugin dependencies.
* This view also lists which plugins require which plugin dependencies in the plugin card. 😅
* In the plugins page, a dependent plugin is unable to be deleted or deactivated if the requiring plugin is active.
* Plugin dependencies can be deactivated or deleted if the requiring plugin is not active.
* Messaging in the plugin row description is inserted; as is data noting which plugins require the dependency.
* Displays a single admin notice with link to **Plugins > Add New > Dependencies** if not all plugin dependencies have been installed.
* Ensures that plugins with unmet dependencies cannot be activated.
* If the dependency API data is not available a generic plugin card will be displayed in the Dependencies tab.

There are several single file plugins that may be used for testing in `test-plugins/`.

## Pull Requests

PRs should be made against the `develop` branch.

## Screenshots

1. Plugin is a Dependency and Plugin needing Dependencies
2. Plugin with Dependencies
2. Plugin Dependencies tab

## Changelog

#### 1.7.1
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
* update test plugins removing `hello-dolly` and adding `git-updater` as non dot org example
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
