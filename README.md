# Symfony CSP Bundle

[![Latest Stable Version](http://poser.pugx.org/opctim/symfony-csp-bundle/v)](https://packagist.org/packages/opctim/symfony-csp-bundle) [![Total Downloads](http://poser.pugx.org/opctim/symfony-csp-bundle/downloads)](https://packagist.org/packages/opctim/symfony-csp-bundle) [![Latest Unstable Version](http://poser.pugx.org/opctim/symfony-csp-bundle/v/unstable)](https://packagist.org/packages/opctim/symfony-csp-bundle) [![License](http://poser.pugx.org/opctim/symfony-csp-bundle/license)](https://packagist.org/packages/opctim/symfony-csp-bundle) [![PHP Version Require](http://poser.pugx.org/opctim/symfony-csp-bundle/require/php)](https://packagist.org/packages/opctim/symfony-csp-bundle)

Ever fought with CSP headers? Me too. It always used to be a pain to configure CSP headers properly.

But setting CSP header directives is more important than ever! If you ever came across different tracking scripts, 
you probably also noticed how many additional fourth-party scripts are lazy loaded. 
This could lead to malicious JavaScript being loaded to your page, which could be catastrophic, 
especially when building payment gateways.

It even helps you with adding dynamic [Nonce-Tokens](https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/nonce) when **not** using the `unsafe-inline` directive **(which you should avoid)**

## Requirements

- PHP >= 7.4.33 with OpenSSL extension installed
- Symfony >= 5.4

## Installation

```shell
composer require opctim/symfony-csp-bundle
```

## Configuration

In your `config/` directory, add / edit `opctim_csp_bundle.yaml`:

```yaml
# config/packages/opctim_csp_bundle.yaml

opctim_csp_bundle:
    
    always_add: []
    
    report:
        url: null
        route: null
        chance: 100

    directives:
        default-src:
            - "'self'"
            - 'data:'
            - '*.example.com'
        base-uri:
            - "'self'"
        object-src:
            - "'none'"
        script-src:
            - "'self'"
            - "nonce(payment-app)" # For more info, see "Dynamic nonce tokens" section below!
            - '*.example.com'
        img-src:
            - "'self'"
            - '*.example.com'
        style-src:
            - "'self'"
            - "'unsafe-inline'"
        connect-src:
            - '*.example.com'
        font-src:
            - '*.example.com'
        frame-src:
            - "'self'"
            - '*.example.com'
        frame-ancestors:
            - "'self'"
            - '*.example.com'
```

[You can use any directives you want here!](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP) This is just a fancy way of writing the directives. 

So:

```yaml
default-src:
    - "'self'"
    - 'data:'
    - '*.example.com'
```

becomes

```text
Content-Security-Policy: default-src 'self' data: *.example.com;
```

### The always_add option

As the name implies, this option adds the specified origins to all directives. This can be useful with `when@dev`:
```yaml
# config/packages/opctim_csp_bundle.yaml

opctim_csp_bundle:
    always_add: []
    
    directives:
        default-src:
            - "'self'"
            - 'data:'
            - '*.example.com'
        base-uri:
            - "'self'"
        object-src:
            - "'none'"
        script-src:
            - "'self'"
            - "nonce(payment-app)"  # For more info, see "Dynamic nonce tokens" section below!
            - '*.example.com'
    
when@dev: 
    opctim_csp_bundle:
        always_add:
            - '*.example.local'
```

You also can use `when@dev` and the yaml anchor / alias syntax to add origins to specific directives conditionally:

```yaml
# config/packages/opctim_csp_bundle.yaml

opctim_csp_bundle:
    always_add: []
    
    directives: &csp_headers # <- This is an anchor, you can name it as you like
        default-src:
            - "'self'"
            - 'data:'
            - '*.example.com'
        script-src:
            - "'self'"
            - '*.motel-one.com'
    
when@dev:
    opctim_csp_bundle:
        directives:
            <<: *csp_headers # <- This alias "merges" the config in here (has to have the same name as above)  
            connect-src:
                - 'some.external.additional.host.com'
```

### The report option

This bundle provides you with an easy way to configure the report feature of CSP, 
which tells browsers to tell your backend if your CSP configuration denies specific resources.
There are currently two implementations in browsers - report-uri & report-to:
- https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-uri
- https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-to

So, according to the MDN docs, this bundle adds the report-uri directive & 
the Reporting-Endpoint header to support new Browsers in the future.

This bundle provides a backwards compatible implementation, which should be supported by all browsers.

```yaml
# config/packages/opctim_csp_bundle.yaml

opctim_csp_bundle:
    always_add: []
    
    report:
        url: null
        route: my_awesome_controller_action
        chance: 100

    directives:
        default-src:
            - "'self'"
            - 'data:'
            - '*.example.com'
```

- `url` - `optional` You can pass an external URL here, which the browsers should report to.
- `route` - `optional` If you want to use your controller action to receive reports. This will use the UrlGenerator to generate an absolute url for you.
- `chance` - `optional` This fields' unit is percent. It specifies how high the chance should be to add the report directives to the response.

**Here is some pseudocode explaining the change option:**

```php
if (random_int(0, 99) < $chance) {
    $someService->addReportHeaders();
}
```

This means, that for a chance of 100%, it will run every time. Depending on traffic of your app, it is recommended 
to set a chance of around 5-10%, to not get flooded by CSP log messages.

### Dynamic nonce tokens

Dynamic nonce tokens can be extremely useful, to allow specific inline script tags in your Twig templates,
without having to ignore security concerns, e.g. by not adding or hard-coding them ;)

#### Configuration syntax

```text
nonce(<handle>)
```

#### Example

In `opctim_csp_bundle.yaml`:

```yaml
opctim_csp_bundle:
    always_add: []
    
    directives:
        default-src:
            - "'self'"
            - 'data:'
            - '*.example.com'
        script-src:
            - "'self'"
            - '*.motel-one.com'
            - 'nonce(my-inline-script)' 
```

On request, `nonce(my-inline-script)` will be transformed to e.g. `nonce-25d2ec8bb6` and will later appear in the response CSP header.

Then, in your twig template you can simply use the `csp_nonce('my-inline-script')` function that is provided by this bundle:

```html
<script type="text/javascript" nonce="{{ csp_nonce('my-inline-script') }}">
    alert('This works!');
</script>
```

The rendered result:
```html
<script type="text/javascript" nonce="25d2ec8bb6">
    alert('This works!');
</script>
```

### Hooking into the CSP header generation

A key feature of this bundle is the dynamic nonce implementation. 
The bundle hooks into the Symfony event system and generates fresh nonce tokens for you - on every request!

On request, the bundle prepares the CSP header directives to be written to headers on response. 
Here, the `nonce()` expressions from `opctim_csp_bundle.yaml` are parsed.

The bundle will add this value to the Response in the following three headers for compatibility across browsers:

- Content-Security-Policy 
- X-Content-Security-Policy
- X-WebKit-CSP

If you want to modify the CSP header before it is written to the response, 
you can hook into the generation by subscribing to the `opctim_csp_bundle.add_csp_header` event:

```php
<?php # src/EventSubscriber/ModifyCspHeaderEventSubscriber.php

declare(strict_types=1);

namespace App\EventSubscriber;

use Opctim\CspBundle\Event\AddCspHeaderEvent;
use Opctim\CspBundle\Service\CspHeaderBuilderService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModifyCspHeaderEventSubscriber implements EventSubscriberInterface 
{
    public function __construct(
        private CspHeaderBuilderService $cspHeaderBuilderService
    ) 
    {}   

    public static function getSubscribedEvents(): array
    {
        return [
            AddCspHeaderEvent::NAME => 'modifyCspHeader'
        ];
    }
    
    public function modifyCspHeader(AddCspHeaderEvent $event): void
    {
        // Use the request if you like
        $request = $event->getRequest();
    
        $cspHeader = $this->cspHeaderBuilderService->build(
            [ // alwaysAdd options
                ...$this->cspHeaderBuilderService->getAlwaysAdd(), // Merge the existing ones...
                'some-conditional-always-to-be-added-origin'
            ], 
            [ // directive options
                ...$this->cspHeaderBuilderService->getDirectives(), // Merge the existing ones...
                'script-src' => [ // Override something here
                    'some-conditional-origin'
                ]
            ]
        );
        
        $cspHeader = str_replace('foo', 'bar', $cspHeader); // Maybe some string transformations here...
        
        $event->setCspHeaderValue($cspHeader); // Set the newly crafted csp header.
        
        // On response, the bundle will add your new CSP header!
    }
}
```

## Tests

Tests are located inside the `tests/` folder and can be run with `vendor/bin/phpunit`:

```shell
composer install

vendor/bin/phpunit       
```
