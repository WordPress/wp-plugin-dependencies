# WordPress Feature Project: Plugin Dependencies

* Contributors: afragen, costdev
* Description: Parses 'Requires Plugins' header, add plugin install dependencies tab, and information about dependencies.
* License: MIT
* Network: true
* Requires at least: 6.0
* Requires PHP: 5.6

## Description

Parses a 'Requires Plugins' header and adds a Dependencies tab in the plugin install page. If a requiring plugin does not have all its dependencies installed and active, it will not activate.

[Make post for Plugin Dependencies Feature Project](https://make.wordpress.org/core/2022/02/24/feature-project-plugin-dependencies/)

My solution to [#22316](https://core.trac.wordpress.org/ticket/22316). Feature plugin version of [PR #1724](https://github.com/WordPress/wordpress-develop/pull/1724)

* Parses the **Requires Plugins** header that defines plugin dependencies using a comma separated list of wp.org slugs.
* Displays a single admin notice with link to **Plugins > Add New > Dependencies** if not all plugin dependencies have been installed.
* Adds a new view/tab to plugins install page ( **Plugins > Add New** ) titled **Dependencies** that contains plugin cards for all plugin dependencies.
* This view also lists which plugins require which plugin dependencies in the plugin card, though that feature requires the filter below to function. 😅
* In the plugins page, a dependent plugin is unable to be deleted or deactivated if the requiring plugin is active.
* Plugin dependencies can be deactivated or deleted if the requiring plugin is not active.
* Messaging in the plugin row description is inserted; as is data noting which plugins require the dependency.
* If the dependency API data is not available a generic plugin card will be displayed in the Dependencies tab.

* Ensures that plugins with unmet dependencies cannot be activated.

There are several single file plugins that may be used for testing in `test-plugins/`.

## Pull Requests

PRs should be made against the `develop` branch.

## Phase 1 - Architecture & Design Discussion

Discussion of architecture and design elements and features. This will be done via [issues](https://github.com/WordPress/wp-plugin-dependencies/issues).

Please review them, comment on them, and create new issues.

## Phase 2 - Actual code review

If you are more comfortable looking at actual code, below are the 2 PRs currently available for [#22316](https://core.trac.wordpress.org/ticket/22316). Please comment on the [trac ticket](https://core.trac.wordpress.org/ticket/22316).

Implement WP admin UI for plugin dependencies
[PR #1547](https://github.com/WordPress/wordpress-develop/pull/1547)

Plugin Dependencies Tab
[PR #1724](https://github.com/WordPress/wordpress-develop/pull/1724)

Currently the above PRs have been closed.
