# MvcCore - Extension - View - Helper - Format Money

[![Latest Stable Version](https://img.shields.io/badge/Stable-v4.3.1-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-view-helper-formatmoney/releases)
[![License](https://img.shields.io/badge/Licence-BSD-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/4.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.3-brightgreen.svg?style=plastic)

Format money by `Intl` extension or by locale formating conventions or by explicit or default arguments.

## Installation
```shell
composer require mvccore/ext-view-helper-formatmoney
```

## Example
```php
<b><?php echo $this->FormatMoney(123456.789); ?></b>
```
```html
<b>$ 123,456.789</b>
```