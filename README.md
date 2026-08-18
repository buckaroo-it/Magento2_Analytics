<p align="center">
  <a href="https://www.buckaroo.nl">
    <img src="https://raw.githubusercontent.com/buckaroo-it/Media/main/Buckaroo/README.md%20Headers/buckaroo-magento2-analytics-header-rounded.png" alt="Buckaroo — Analytics for Magento 2" width="100%">
  </a>
</p>

<h1 align="center">Buckaroo Analytics for Magento 2</h1>

---

> [!WARNING]
> **The Analytics module is now part of the Buckaroo Magento 2 plugin.** This separate module is no longer needed and is no longer developed. Do not install `buckaroo/magento2analytics` in a new project — install the [Buckaroo Magento 2 plugin](https://github.com/buckaroo-it/Magento2), which includes the GA tracking support out of the box.

---

## About

Google Analytics misattributes the conversion source when a purchase spans more than one browser or device. A visitor arrives from an ad campaign, starts the order, completes the payment on their phone, and the success page loads somewhere other than where the session began. The conversion is then credited to the wrong source, or to none at all.

This module solves that by storing the Google Client ID against the order, so the success page can fire an enriched tracking call that ties the conversion back to the original session. It also sets URL parameters on the success page from cookie values, which allows for more granular tracking.

The functionality used to live in this repository as a separate module. It has since been merged into the main [Buckaroo Magento 2 plugin](https://github.com/buckaroo-it/Magento2).

This repository is kept online for reference and for merchants who are still on an older setup.

---

## Migrating to the main plugin

If you currently have the separate module installed, remove it and make sure you are on a plugin version that includes GA tracking. Run the following from your Magento 2 root folder:

```bash
composer remove buckaroo/magento2analytics
composer update buckaroo/magento2
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

> [!IMPORTANT]
> Test this on a staging environment first. Check your GA tracking settings afterwards and confirm that conversions still register correctly before you rely on the data.

---

## Configuration

GA tracking is configured in the main plugin, under **Stores → Configuration → Sales → Buckaroo → GA Tracking Options** in the Magento admin.

To set a URL parameter from a cookie:

1. Enable GA tracking.
2. Add a pair consisting of the cookie name and the URL parameter you want to set from it.
3. Optionally add a replace regex, if you only need part of the cookie value.
4. Save the settings.

The parameters are then applied to the success page automatically.

---

## Usage

### Reading the Client ID in JavaScript or GTM

The `clientId` is passed as a URL parameter on the success page during the redirect, in the form `/checkout/onepage/success/?clientId=****/`. Extract it and hand it to your tracking code:

```javascript
try {
    currentPageUrl = window.location.href;
    myClientId = currentPageUrl.split('clientId=')[1].split('/')[0];
} catch (error) {
    myClientId = '';
}
```

Then pass it into the tracking call:

```javascript
ga('create', 'UA-XXXXX-Y', {
    'storage': 'none',
    'clientId': myClientId
});
```

### Reading the Client ID server-side

The Client ID is also stored in the database, and can be looked up by `quoteId` through the repository:

```php
use Buckaroo\Magento2Analytics\Model\AnalyticsRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class MyCustomViewModel
{
    public function __construct(
        AnalyticsRepository $analyticsRepo
    ) {
        $this->analyticsRepo = $analyticsRepo;
    }

    public function getClientId($quoteId)
    {
        try {
            $clientIdData = $this->analyticsRepo->getByQuoteId($quoteId);
            return $clientIdData->getClientId();
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('ClientId not found for quoteId ' . $quoteId));
        }
    }
}
```

> [!NOTE]
> The namespace above is the one used by the separate module. After migrating to the main plugin, check the current namespace in the plugin source before wiring this into your own code.

---

## Support

Questions about GA tracking belong with the main plugin, since that is where the code now lives.

- **Bug reports and feature requests:** [open an issue on the main plugin](https://github.com/buckaroo-it/Magento2/issues)
- **Technical support:** [support@buckaroo.nl](mailto:support@buckaroo.nl)
- **Phone:** +31 (0)30 711 50 50
- **Gateway status:** [status.buckaroo.io](https://status.buckaroo.io/)

---

<p align="center">
  <sub>Made with care by <a href="https://www.buckaroo.nl">Buckaroo</a>.<br>
  This document is subject to change; typos and language errors are possible.</sub>
</p>
