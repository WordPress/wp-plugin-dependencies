# WordPress Feature Project: Plugin Dependencies

* Contributors: afragen, costdev, pbiron
* Description: Parses 'Requires Plugins' header, add plugin install dependencies tab, and information about dependencies.
* License: MIT
* Network: true
* Requires at least: 6.0
* Requires PHP: 5.6

## Description

Parses a 'Requires Plugins' header. If a requiring plugin does not have all its dependencies installed and active, it will not activate.

[Make post for Plugin Dependencies Feature Project](https://make.wordpress.org/core/2022/02/24/feature-project-plugin-dependencies/)

My solution to [#22316](https://core.trac.wordpress.org/ticket/22316). Feature plugin version of [PR #3032](https://github.com/WordPress/wordpress-develop/pull/3032)

* Parses the **Requires Plugins** header that defines plugin dependencies using a comma separated list of wp.org slugs.
* In the plugins page, a dependent plugin is unable to be deleted or deactivated if the requiring plugin is active.
* Plugin dependencies can be deactivated or deleted if the requiring plugin is not active.
* Messaging in the plugin row description is inserted; as is data noting which plugins require the dependency.
* Circular dependencies cannot be activated and an admin notice noting the circular dependencies is displayed.
* Ensures that plugins with unmet dependencies cannot be activated.

There are several single file plugins that may be used for testing in `test-plugins/`.

## Pull Requests

PRs should be made against the `develop` branch.
