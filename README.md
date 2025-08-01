# MU Plugin Installer
This is a Composer plugin that installs mu plugins into the `wp-content/mu-plugins` directory. This differs from other solutions as each mu-plugin has it's own dedicated entrypoint file. This means that mu-plugins screen in WordPress has a more accurate representation of which plugins are installed and at what version they are running.

## Installation
You can install this package as part of your mu-plugin's `composer.json` file.

```bash
composer require creode/mu-plugin-installer
```

## Usage
Add the following to your `composer.json` file of your mu-plugin:

```json
{
    "extra": {
        "wordpress-muplugin-entry": "{an-entrypoint-file-name}.php"
    }
}
```

## Supported Package Types

- `wordpress-muplugin`

## Available Placeholders

- `:PLUGIN_VERSION:` - The version of the plugin which will be pulled in dynamically from composer at install time.

### Example
Below is an example of a mu-plugin entrypoint file that will be installed into the `wp-content/mu-plugins` directory. This demonstrates the use of the `:PLUGIN_VERSION:` placeholder.

```php
/**
 * Plugin Name: WordPress Blocks
 * Description: WordPress Blocks plugin used by Creode to assist in the development of WordPress blocks.
 * Version: :PLUGIN_VERSION:
 *
 * @package Creode Blocks
 */

// This is a loader for the plugin from the parent directory to handle MU Plugin installation.
require_once __DIR__ . '/wordpress-blocks/plugin.php';
```

The above example will install the plugin into the `wp-content/mu-plugins` directory and create a file called `wordpress-blocks.php` in the `wp-content/mu-plugins` directory. The file will contain the following content:

```php
/**
 * Plugin Name: WordPress Blocks
 * Description: WordPress Blocks plugin used by Creode to assist in the development of WordPress blocks.
 * Version: 1.9.0
 *
 * @package Creode Blocks
 */

// This is a loader for the plugin from the parent directory to handle MU Plugin installation.
require_once __DIR__ . '/wordpress-blocks/plugin.php';
```