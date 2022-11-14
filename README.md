<p align="center">
  <img src="https://www.buckaroo.nl/media/3478/magento2_googleanalytics_icon.png" width="200px" position="center">
</p>

# Buckaroo Magento2 Analytics extension
[![Latest release](https://badgen.net/github/release/buckaroo-it/Magento2_Analytics)](https://github.com/buckaroo-it/Magento2_Analytics/releases)

### Index
- [Installation and configuration](#installation-and-configuration)
- [Usage](#usage)
- [Serverside](#serverside)
- [Contribute](#contribute)
- [Versioning](#versioning)
- [Additional information](#additional-information)
---

### Installation and configuration
```
composer require buckaroo/magento2analytics
php bin/magento module:enable Buckaroo_Magento2Analytics
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
### Usage
#### General information

GA Tracking does not allocate properly the conversion source when the transaction happens cross-browser or cross-device. Visitor lands on the website from an ad campaign, goes through the order process, but the payment process takes place on a different device or browser and success page is also displayed in a different device
In order to handle this situation, we track the Google Client ID against the order and we can trigger an enriched version of the tracking code in the success page, by adding the clientId parameter.

### Javascript / GTM

`clientId` value is passed as a parameter in the URL of the success page, part of the redirect process. This can be extracted and used in the javascript code that triggers the GA/UA/GTM/other event for the conversion.
Standard structure of the URL is the following:
`/checkout/onepage/success/?clientId=****/`

**and the clientId can be extracted:**
```
    try{
        currentPageUrl = window.location.href;
        myClientId = currentPageUrl.split('clientId=')[1].split('/')[0];
    } catch(error) {
        myClientId = '';
    }
```

**and sent to GA/UA part of the tracking code:**

```
    ga('create', 'UA-XXXXX-Y', {
        'storage': 'none',
        'clientId': myClientId
    });
```
### Serverside

The information related to clientId is also stored in the database. And this can be used on the server side level, via the Model repository `Buckaroo\Magento2Analytics\Model\AnalyticsRepository` using the `quoteId`:

```
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
        $clientId = null;
        try {
            $clientIdData = $analyticsRepo->getByQuoteId($quote->getId());
            return $clientIdData->getClientId();    
        } catch(\Exception $e) {
            throw new NoSuchEntityException(__('ClientId not found for quoteId' . $quoteId ));
        }
        
    }
}
```

### Contribute

We really appreciate it when developers contribute to improve the Buckaroo plugins.
If you want to contribute as well, then please follow our [Contribution Guidelines](CONTRIBUTING.md).

### Versioning 
<p align="left">
  <img src="https://www.buckaroo.nl/media/3480/magento_versioning.png" width="500px" position="center">
</p>

- **MAJOR:** Breaking changes that require additional testing/caution.
- **MINOR:** Changes that should not have a big impact.
- **PATCHES:** Bug and hotfixes only.


### Additional information
- **Support:** https://support.buckaroo.eu/contact
- **Contact:** [support@buckaroo.nl](mailto:support@buckaroo.nl) or [+31 (0)30 711 50 50](tel:+310307115050)

<b>Please note:</b><br>
This file has been prepared with the greatest possible care and is subject to language and/or spelling errors.
