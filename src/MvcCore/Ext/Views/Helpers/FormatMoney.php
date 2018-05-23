<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Views\Helpers;

/**
 * Responsibility - format money by `Intl` extension or by locale formating conventions or by explicit or default arguments.
 * - Formating processed by `Intl` extension if installed or by `\number_format()` and `\localeconv()` fallback.
 * - Possiblity to define default decimal points value to not define it every time using `FormatMoney()` call.
 * - Possiblity to define default currency value to not define it every time using `FormatMoney()` call.
 * - Possiblity to define argument to create `Intl` money formater instance in every call or globaly by default setters in parent class.
 * - Possiblity to define any argument for `number_format()` and `\localeconv()` fallback in every call or globaly by default setters in parent class.
 * - If there is used formating fallback and no locale formating conventions are defined, system locale settings is automaticly
 *   configured by request language and request locale and by system locale settings are defined locale formating conventions.
 * - Fallback result string always returned in response encoding, in UTF-8 by default.
 *
 * @see http://php.net/manual/en/numberformatter.create.php
 * @see http://php.net/manual/en/numberformatter.formatcurrency.php
 * @see http://php.net/manual/en/numberformatter.setattribute.php
 * @see http://php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute
 * @see http://php.net/manual/en/function.number-format.php
 * @see http://php.net/manual/en/function.localeconv.php
 */
class FormatMoney extends \MvcCore\Ext\Views\Helpers\FormatNumber
{
	/**
	 * MvcCore Extension - View Helper - Assets - version:
	 * Comparation by PHP function version_compare();
	 * @see http://php.net/manual/en/function.version-compare.php
	 */
	const VERSION = '5.0.0-alpha';

	/**
	 * If this static property is set - helper is possible
	 * to configure as singleton before it's used for first time.
	 * Example:
	 *	`\MvcCore\Ext\View\Helpers\FormatMoney::GetInstance()`
	 * @var \MvcCore\Ext\Views\Helpers\FormatMoney
	 */
	protected static $instance;

	/**
	 * Default currency to not define third param every time in `FormatMoney()` function.
	 * The 3-letter ISO 4217 currency code indicating the currency to use.
	 * This property is used only for `Intl` extension formating,
  * not for fallback by `\number_format()` and `\localeconv()`.
	 * @var string|NULL
	 */
	protected $defaultCurrency = NULL;

	/**
	 * System `setlocale()` category to set up system locale automaticly in `parent::SetView()` method.
	 * This property is used only for fallback if formating is not by `Intl` extension.
	 * @var \int[]
	 */
	protected $localeCategories = array(LC_NUMERIC, LC_MONETARY);

	/**
	 * Set default currency to not define third param every time in `FormatMoney()` function.
	 * The 3-letter ISO 4217 currency code indicating the currency to use.
	 * This property setter is used only for `Intl` extension formating,
	 * not for fallback by `\number_format()` and `\localeconv()`.
	 * @param string $defaultCurrency
	 * @return \MvcCore\Ext\Views\Helpers\FormatMoney
	 */
	public function & SetDefaultCurrency ($defaultCurrency) {
		$this->defaultCurrency = $defaultCurrency;
		return $this;
	}

	/**
	 * @see http://php.net/manual/en/numberformatter.create.php
	 * @see http://php.net/manual/en/numberformatter.formatcurrency.php
	 * @see http://php.net/manual/en/numberformatter.setattribute.php
	 * @see http://php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute
	 * @see http://php.net/manual/en/function.number-format.php
	 * @see http://php.net/manual/en/function.localeconv.php
	 * @param int|float|string	$number			The number being formatted.
	 * @param int|NULL			$decimalsCount	Optional, numerics count after decimal point.
	 *											If `NULL`, there is used `Intl` localized formater
	 *											default value for money, usually two - `2`.
	 *											If `NULL` for fallback `number_format()`, there is used
	 *											system locale settings and if there are used default
	  *											locale conventions for formating - usually for `en_US` two - `2`
	 * @param string|NULL		$currency		Optional, 3-letter ISO 4217 currency code indicating the currency to use.
	 * @return string
	 */
	public function FormatMoney ($number = NULL, $decimalsCount = NULL, $currency = NULL) {
		$numberIsNumeric = is_numeric($number);
		if (!$numberIsNumeric) return (string) $number;
		$valueToFormat = $numberIsNumeric && is_string($number)
			? floatval($number)
			: $number;
		if ($this->intlExtensionFormating) {
			return $this->formatByIntlMoneyFormater(
				$valueToFormat, $decimalsCount, $currency
			);
		} else {
			return $this->fallbackFormatByLocaleConventions(
				$valueToFormat, $decimalsCount
			);
		}
	}

	/**
	 * Format money by `Intl` extension formater. If no international three chars currency
	 * symbol is provided, there is used currency symbol from localized `Intl` formater instance.
	 * @see http://php.net/manual/en/numberformatter.create.php
	 * @see http://php.net/manual/en/numberformatter.formatcurrency.php
	 * @param int|float		$valueToFormat	Numeric value to format.
	 * @param int|NULL		$decimalsCount	Optional, numerics count after decimal point,
	 *										If `NULL`, there is used `Intl` localized formater
	 *										default value for money, usually two - `2`.
	 * @param string|NULL	$currency		Optional, 3-letter ISO 4217 currency code
	 *										indicating the currency to use.
	 * @return string
	 */
	protected function formatByIntlMoneyFormater ($valueToFormat = 0.0, $decimalsCount = NULL, $currency = NULL) {
		$formater = $this->getIntlNumberFormater(
			$this->langAndLocale,
			\NumberFormatter::CURRENCY,
			NULL,
			($decimalsCount !== NULL
				? array(\NumberFormatter::FRACTION_DIGITS => $decimalsCount)
				: array())
		);
		if ($currency === NULL) {
			if ($this->defaultCurrency !== NULL) {
				// try to get default currency
				$currency = $this->defaultCurrency;
			} else {
				// try to get currency from localized formater
				$currency = \numfmt_get_symbol($formater, \NumberFormatter::INTL_CURRENCY_SYMBOL);
				if (mb_strlen($currency) !== 3) {
					// try to get currency by system locale settings, by formating conventions
					if ($this->encodingConversion === NULL) {
						$this->setUpSystemLocaleAndEncodings();
						$this->setUpLocaleConventions();
					}
					$currency = $this->localeConventions->int_curr_symbol;
				}
			}
		}
		return \numfmt_format_currency($formater, $valueToFormat, $currency);
	}

	/**
	 * Fallback formating by PHP `\number_format()` and by system locale formating conventions.
	 * If there was not possible to define system locale formating conventions, there are used
	 * default formating conventions, usually for `en_US`.
	 * @see http://php.net/manual/en/function.localeconv.php
	 * @see http://php.net/manual/en/function.number-format.php
	 * @param int|float	$valueToFormat	The number being formatted.
	 * @param int|NULL	$decimalsCount	Optional, numerics count after decimal point for `number_format()`,
	 *									If `NULL`, there is used system locale settings and if there are
	 *									no locale system settings, there are used default locale conventions
	 *									for formating - usually for `en_US` two - `2`.
	 * @return string
	 */
	protected function fallbackFormatByLocaleConventions ($valueToFormat = 0.0, $decimalsCount = NULL) {
		if ($this->encodingConversion === NULL) {
			$this->setUpSystemLocaleAndEncodings();
			$this->setUpLocaleConventions();
		}
		$lc = & $this->localeConventions;
		// decide number to format is possitive or negative
		$negative = $valueToFormat < 0;
		// complete decimals count by given argument or by default fractal digits property
		$decimalsCount = $decimalsCount !== NULL
			? $decimalsCount
			: $lc->frac_digits;
		// format absolute value by classic PHPs `number_format()`
		$result = \number_format(
			abs($valueToFormat), $decimalsCount,
			$lc->mon_decimal_point, $lc->mon_thousands_sep
		);
		// if formated number is under zero - formating rules will be different
		if ($negative) {
			$signSymbol  = $lc->negative_sign;
			$signPosition    = $lc->n_sign_posn;
			$currencyBeforeValue  = $lc->n_cs_precedes;
			$currencySeparatedBySpace = $lc->n_sep_by_space;
		} else {
			$signSymbol  = $lc->positive_sign;
			$signPosition    = $lc->p_sign_posn;
			$currencyBeforeValue  = $lc->p_cs_precedes;
			$currencySeparatedBySpace = $lc->p_sep_by_space;
		}
		// currency symbol is always in this place
		$currency = $lc->currency_symbol;
		// decide if currency symbol precedes a negative value or not
		if ($currencyBeforeValue) {
			// if currency symbol is before formated number
			if ($signPosition == 3) {
				// sign symbol is before currency symbol
				$currency = $signSymbol . $currency;
			} elseif ($signPosition == 4) {
				// sign symbol is after currency symbol
				$currency .= $signSymbol;
			}
			// currency symbol is before formated number, sometimes separated by space or not
			if ($currencySeparatedBySpace) {
				$result = $currency . ' ' . $result;
			} else {
				$result = $currency . $result;
			}
		} else {
			// if currency symbol is after formated number, sometimes separated by space or not
			if ($currencySeparatedBySpace) {
				$result .= ' '.$currency;
			} else {
				$result .= $currency;
			}
		}
		// add brackets or sign symbol if sign symbol is out of number and currency
		if ($signPosition == 0) {
			// negative value by brackets
			$result = "($result)";
		} elseif ($signPosition == 1) {
			// sign symbol is before number and currency
			$result = $signSymbol . $result;
		} elseif ($signPosition == 2) {
			// sign symbol is after number and currency
			$result .= $signSymbol;
		}
		return $this->encode($result);
	}
}
