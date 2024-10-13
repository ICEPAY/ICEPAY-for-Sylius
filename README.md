# ICEPAY for Sylius

## Quickstart Installation

### Traditional

1. Add this repository to composer.json

```json
{
    "repositories": [
        {
            "type": "vcs", 
            "url": "https://github.com/ICEPAY/ICEPAY-for-Sylius"
        }
    ]
}
```

2. Install the plugin using composer

```bash
composer require icepay/icepay-for-sylius:dev-master
```

3. Register the plugin into `config/bundles.php`

```php
return [
    // ...
    SyliusIcepayPlugin\SyliusIcepayPlugin::class => ['all' => true],
];
```

4. If refunds need to be processed through ICEPAY, import the following configuration:

```yaml
# config/packages/_sylius.yaml

imports:
    # ...
    - { resource: "@SyliusIcepayPlugin/config/config.yml" }
```

### Docker

1. Execute `docker compose up -d`

2. Initialize plugin `docker compose exec app make init`

3. See your browser `open localhost`

## Usage

### Running plugin tests

  - PHPUnit

    ```bash
    vendor/bin/phpunit
    ```

  - PHPSpec

    ```bash
    vendor/bin/phpspec run
    ```

  - Behat (non-JS scenarios)

    ```bash
    vendor/bin/behat --strict --tags="~@javascript"
    ```

  - Behat (JS scenarios)
 
    1. [Install Symfony CLI command](https://symfony.com/download).
 
    2. Start Headless Chrome:
    
      ```bash
      google-chrome-stable --enable-automation --disable-background-networking --no-default-browser-check --no-first-run --disable-popup-blocking --disable-default-apps --allow-insecure-localhost --disable-translate --disable-extensions --no-sandbox --enable-features=Metal --headless --remote-debugging-port=9222 --window-size=2880,1800 --proxy-server='direct://' --proxy-bypass-list='*' http://127.0.0.1
      ```
    
    3. Install SSL certificates (only once needed) and run test application's webserver on `127.0.0.1:8080`:
    
      ```bash
      symfony server:ca:install
      APP_ENV=test symfony server:start --port=8080 --dir=tests/Application/public --daemon
      ```
    
    4. Run Behat:
    
      ```bash
      vendor/bin/behat --strict --tags="@javascript"
      ```
    
  - Static Analysis
  
    - Psalm
    
      ```bash
      vendor/bin/psalm
      ```
      
    - PHPStan
    
      ```bash
      vendor/bin/phpstan analyse -c phpstan.neon -l max src/  
      ```

  - Coding Standard
  
    ```bash
    vendor/bin/ecs check
    ```

### Opening Sylius with your plugin

- Using `test` environment:

    ```bash
    (cd tests/Application && APP_ENV=test bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=test bin/console server:run -d public)
    ```
    
- Using `dev` environment:

    ```bash
    (cd tests/Application && APP_ENV=dev bin/console sylius:fixtures:load)
    (cd tests/Application && APP_ENV=dev bin/console server:run -d public)
    ```
