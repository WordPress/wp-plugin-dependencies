# Plugin Dependencies

Contributors: afragen, costdev
Plugin URI:  https://github.com/WordPress/wp-plugin-dependencies
Description: Parses 'Requires Plugins' header, add plugin install dependencies tab, and information about dependencies.
License: MIT
Network: true
Requires at least: 6.0
Requires PHP: 5.6
Tested up to: 6.0
Stable tag: 1.0.0

## Description

Parses a 'Requires Plugins' header and adds a Dependencies tab in the plugin install page. If a requiring plugin does not have all it's dependencies installed and active, it will not activate.

[Make post for Plugin Dependencies Feature Project](https://make.wordpress.org/core/2022/02/24/feature-project-plugin-dependencies/)

My solution to [#22316](https://core.trac.wordpress.org/ticket/22316). Feature plugin version of [PR #1724](https://github.com/WordPress/wordpress-develop/pull/1724)

* Parses the **Requires Plugins** header that defines plugin dependencies using a comma separated list of wp.org slugs.
* Adds a new view/tab to plugins install page ( **Plugins > Add New** ) titled **Dependencies** that contains plugin cards for all plugin dependencies.
* This view also lists which plugins require which plugin dependencies in the plugin card, though that feature requires the filter below to function. 😅
* In the plugins page, a dependent plugin is unable to be deleted or deactivated if the requiring plugin is active.
* Plugin dependencies can be deactivated or deleted if the requiring plugin is not active.
* Messaging in the plugin row description is inserted; as is data noting which plugins require the dependency.
* Displays a single admin notice with link to **Plugins > Add New > Dependencies** if not all plugin dependencies have been installed.
* Ensures that plugins with unmet dependencies cannot be activated.
* If the dependency API data is not available a generic plugin card will be displayed in the Dependencies tab.

## Screenshots

1. Plugins page
2. Plugin Dependencies tab

## Changelog

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
