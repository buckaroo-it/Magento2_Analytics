<p align="center">
  <img src="https://www.buckaroo.nl/media/osphsp1u/magento2_googleanalytics_icon.png" width="200px" position="center">
</p>

# Buckaroo Magento2 Analytics extension
[![Latest release](https://badgen.net/github/release/buckaroo-it/Magento2_Analytics)](https://github.com/buckaroo-it/Magento2_Analytics/releases)

### Index
- [Installation and configuration](#installation-and-configuration)
- [Usage](#usage)
- [Serverside](#serverside)
- [Features](#features)
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

**Prerequisites:**
- This module requires the main [Buckaroo Magento2](https://github.com/buckaroo-it/Magento2) payment module to be installed and enabled
- The module integrates directly with Buckaroo's payment redirect flow

**How the integration works:**
- The Analytics module extends `Buckaroo\Magento2\Controller\Redirect\Process` to add URL parameters to the success redirect
- It hooks into `Buckaroo\Magento2\Gateway\Http\TransactionBuilder\AbstractTransactionBuilder` via a plugin to modify return URLs
- Cookie values are automatically captured during checkout and appended to the redirect URL after payment
### Usage
#### General information

GA Tracking does not allocate properly the conversion source when the transaction happens cross-browser or cross-device. Visitor lands on the website from an ad campaign, goes through the order process, but the payment process takes place on a different device or browser and success page is also displayed in a different device
In order to handle this situation, we track the Google Client ID against the order and we can trigger an enriched version of the tracking code in the success page, by adding the clientId parameter.

**How it works:**
1. During checkout, the module captures the `_ga` cookie (Google Analytics Client ID) via the `checkout_submit_all_after` event
2. The clientId is stored in the database associated with the quote_id
3. When Buckaroo redirects back after payment, the module automatically appends cookie values (including clientId) as URL parameters to the success page
4. The success page URL will contain parameters like: `/checkout/onepage/success/?clientId=1234567890.0987654321&gclid=xxx&msclkid=yyy`
5. You can then extract these values and use them in your analytics implementation (GA/UA/GTM/GA4)

### Javascript / GTM

The module automatically appends cookie values as URL parameters to the success page during Buckaroo's payment redirect process. The `clientId` value (and other configured parameters) are passed in the URL and can be extracted for use in your analytics tracking.

Standard structure of the URL:
`/checkout/onepage/success/?clientId=1234567890.0987654321&gclid=xxx&msclkid=yyy`

**Note:** The actual parameters depend on your configuration in the Buckaroo Analytics settings. By default, the module tracks:
- `clientId` (from `_ga` cookie)
- `gclid` (from `_gcl_aw` cookie - Google Ads)
- `msclkid` (from `_uetmsclkid` cookie - Microsoft Ads)
- `sqzllocal` (custom tracking)

**Extracting the clientId from URL:**
```javascript
try {
    currentPageUrl = window.location.href;
    myClientId = currentPageUrl.split('clientId=')[1].split('/')[0];
} catch(error) {
    myClientId = '';
}
```

#### Implementation Options:

**Option 1: Universal Analytics (GA/UA) - Direct Implementation**

If you're using the traditional Universal Analytics `ga()` function:

```javascript
ga('create', 'UA-XXXXX-Y', {
    'storage': 'none',
    'clientId': myClientId
});
```

**Option 2: Google Tag Manager (GTM) - Recommended Approach**

For GTM implementations, you have two main approaches:

**A) Using GTM Data Layer (Recommended)**

Push the clientId to the dataLayer before your purchase event:

```javascript
// Extract clientId from URL
try {
    var currentPageUrl = window.location.href;
    var myClientId = currentPageUrl.split('clientId=')[1].split('/')[0];
    
    // Push to dataLayer
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        'clientId': myClientId
    });
} catch(error) {
    console.error('Error extracting clientId:', error);
}
```

Then in GTM:
1. **Create a Data Layer Variable:**
   - Variables → New → Data Layer Variable
   - Name: `DLV - Client ID`
   - Data Layer Variable Name: `clientId`

2. **Add to GA4 Configuration Tag:**
   - Go to your GA4 Configuration tag
   - Under "Fields to Set", add:
     - Field Name: `client_id`
     - Value: `{{DLV - Client ID}}`

3. **OR Add to Purchase Event Tag:**
   - Go to your GA4 Event tag (purchase/conversion)
   - Under "Event Parameters", add:
     - Parameter Name: `client_id`
     - Value: `{{DLV - Client ID}}`

**B) Using GTM Custom JavaScript Variable**

1. **Create a Custom JavaScript Variable in GTM:**
   - Variables → New → Custom JavaScript
   - Name: `JS - Extract Client ID from URL`
   - Code:
   ```javascript
   function() {
       try {
           var currentPageUrl = window.location.href;
           var clientId = currentPageUrl.split('clientId=')[1].split('/')[0];
           return clientId;
       } catch(error) {
           return '';
       }
   }
   ```

2. **Use in GA4 Tags:**
   - In your GA4 Configuration or Event tag
   - Add Field/Parameter:
     - Name: `client_id`
     - Value: `{{JS - Extract Client ID from URL}}`

**Option 3: Google Analytics 4 (GA4) - Direct Implementation**

If you're using gtag.js directly:

```javascript
// Extract clientId from URL
try {
    var currentPageUrl = window.location.href;
    var myClientId = currentPageUrl.split('clientId=')[1].split('/')[0];
    
    // Send to GA4
    gtag('config', 'G-XXXXXXXXXX', {
        'client_id': myClientId
    });
} catch(error) {
    console.error('Error extracting clientId:', error);
}
```

**Important Notes for GTM Users:**
- The clientId should be set **before** your purchase/conversion event fires
- For cross-device tracking, ensure the clientId is passed to both your Configuration tag AND your Purchase event
- Test your implementation using GTM Preview mode to verify the clientId is being captured correctly
- You can view the clientId value in GA4's DebugView to confirm it's being sent

---

### Technical Implementation Details

#### How the Module Works with Buckaroo Magento2

This module integrates seamlessly with the main Buckaroo Magento2 payment module through several key components:

**1. Event Observer (`checkout_submit_all_after`):**
- Captures the `_ga` cookie during checkout
- Stores the clientId in the database with the associated quote_id
- File: `Buckaroo\Magento2Analytics\Observer\Analytics`

**2. Plugin on Return URL Builder:**
- Intercepts the return URL generated by Buckaroo's payment gateway
- Appends configured cookie values as URL parameters
- File: `Buckaroo\Magento2Analytics\Plugin\AbstractTransactionBuilderAfterGetUrl`
- Target: `Buckaroo\Magento2\Gateway\Http\TransactionBuilder\AbstractTransactionBuilder`

**3. Controller Override:**
- Extends Buckaroo's redirect controller
- Ensures URL parameters are preserved during the success redirect
- File: `Buckaroo\Magento2Analytics\Controller\Redirect\Process`
- Extends: `Buckaroo\Magento2\Controller\Redirect\Process`

**4. Cookie Parameter Service:**
- Reads configured cookie/parameter pairs from admin settings
- Extracts values from cookies and applies regex transformations
- Builds the query string for the success URL
- File: `Buckaroo\Magento2Analytics\Service\CookieParamService`

**Flow:**
```
Checkout → Observer captures cookies → Payment redirect → 
Plugin adds params to return URL → Buckaroo processes payment → 
Controller redirects to success page with parameters
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

### Features

#### Dynamic URL Parameters on Success Page Based on Cookies:
• This new feature enables you to add unlimited URL parameters on the success page, utilizing information stored in cookies. This is a significant addition that enables more granular tracking of customer activity and success page interactions, leading to more precise and actionable analytics data.

• The new module is designed for easy use: simply add another pair of cookie name, URL parameter, and the replace regex if you want to extract only a portion of the text. This flexibility allows for precise control over what information is captured and used in your URL parameters.

**How to Use:**<br>
To utilize this feature:<br>
<b>1.</b> Go to the Buckaroo Magento2_Analytics module settings (Stores → Settings → Configuration → Sales → Buckaroo → GA Tracking Options).<br>
    
![Google_Analytics_Configuration](https://github.com/buckaroo-it/Magento2_Analytics/assets/105488705/c2308408-46ff-4a66-8252-f224739e53de)

<b>2.</b> Enable GA Tracking.<br>
<b>3.</b> Add a new pair consisting of the cookie name and the URL parameter that you wish to set based on the cookie's value.<br>
<b>4.</b> (Optional) If you only need to extract a specific part of the text, provide a replace regex.<br>
<b>5.</b> Save the settings.<br>
<b>6.</b> The module will automatically handle the rest, setting the URL parameters on your success page based on the specified cookies.
<br>

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
