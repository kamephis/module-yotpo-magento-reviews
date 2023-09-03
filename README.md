# Yotpo Importer

## Overview
This Magento module imports reviews from Yotpo and creates corresponding reviews in Magento. It features a cron job that fetches Yotpo reviews and saves them to Magento's reviews table.

### Features

- Fetches reviews from Yotpo using Yotpo's API.
- Saves fetched reviews into Magento's reviews table.
- Handles errors gracefully and logs them for debugging.
- Utilizes Magento's native Curl client and Laminas Logger.

### How do I get set up?

* Requires: Magento 2.4.5 / PHP 8.1^
* Install via Composer.
* Configure Yotpo API keys within the module.

#### Installation

1. Add the custom repository to Composer:

    ```bash
    composer config repositories.kamephis-yotpo-import vcs https://github.com/kamephis/module-yotpo-magento-reviews.git
    ```

2. Require the package:

    ```bash
    composer require kamephis/module-yotpo-magento-reviews
    ```

3. Run the following Magento 2 commands:

    ```bash
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    bin/magento cache:flush
    ```

### Configuration

After installing the module, make sure to:

- Enter your Yotpo API keys for the module to function correctly.

### Version History

- 1.0.0:
    - Base module.

### Requirements

- Magento 2.4.5
- PHP 8.1

### Support

For any issues or enhancements, please open a ticket in the Bitbucket repository or contact the module creator.

### License

Open Source License. Please see the LICENSE file for full details.
