[unreleased]
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
