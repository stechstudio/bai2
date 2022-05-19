# PHP BAI2 Parser

Parse and consume [BAI2](https://www.bai.org/docs/default-source/libraries/site-general-downloads/cash_management_2005.pdf) data within your PHP 8 application.

## Installation

```
composer require stechstudio/bai2:@dev
```

## Usage

See example [`bai2json`](bin/bai2json) utility included with this project for a usage example.

This example utility is by itself a handy tool when working with BAI2 files, and pairs nicely with [`jq`](https://stedolan.github.io/jq/):

```
$ composer global require stechstudio/bai2:@dev
$ bai2json some_awesome_transaction_info.bai2.txt | jq -C | less -R
```
