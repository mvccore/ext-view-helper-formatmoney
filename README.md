# MvcCore - Extension - View - Helper - Format Money

[![Latest Stable Version](https://img.shields.io/badge/Stable-v5.0.0-brightgreen.svg?style=plastic)](https://github.com/mvccore/ext-view-helper-formatmoney/releases)
[![License](https://img.shields.io/badge/License-BSD%203-brightgreen.svg?style=plastic)](https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md)
![PHP Version](https://img.shields.io/badge/PHP->=5.4-brightgreen.svg?style=plastic)

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