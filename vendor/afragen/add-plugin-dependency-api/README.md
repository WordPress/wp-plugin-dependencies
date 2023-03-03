# Add Plugin Dependency API

* Contributors: [Andy Fragen](https://github.com/afragen), [contributors](https://github.com/afragen/add-plugin-dependency-api/graphs/contributors)
* Tags: plugin dependency
* Requires at least: 6.0
* Requires PHP: 5.6
* Stable tag: main
* Donate link: <https://thefragens.com/git-updater-donate>
* License: MIT

Composer library for adding a plugin dependency response based upon use with Git Updater v12 or greater.

## Description

This is an example to developers of non dot-org plugins that wish to take advantage of the Plugin Dependencies feature from the `Requires Plugins` header.

You can use **composer** to install this package within your WordPress plugin / theme.

**Please ensure you are using the latest version of this framework in your `composer.json`**

1. Within your plugin or theme root folder, run the following command:

```shell
composer require afragen/add-plugin-dependency-api
```

2. Add a filter to your plugin that requires a dependency that returns an array or JSON response containing a REST endpoint that returns a valid `plugins_api()` response.

A query arg of the plugin slug, `?slug=my-plugin-dependency`, will be passed to the endpoint returned from the filter. The slug originates from the `Require Plugins` header.

```php
// Add the sites with REST enpoints that return plugins_api() data when passed `slug` query arg.
// You can also return URL to a JSON file containing the appropriate data.
add_filter(
	'plugin_dependency_endpoints',
	function () {
		return [ 'https://git-updater.com/wp-json/git-updater/v1/plugins-api/' ];
	}
);
```

To see an example REST endpoint return view <https://git-updater.com/wp-json/git-updater/v1/plugins-api/?slug=git-updater> in the browser.

## Development

PRs are welcome against the `develop` branch.
