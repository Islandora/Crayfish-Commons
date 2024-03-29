
![Crayfish](https://user-images.githubusercontent.com/2371345/48163075-11c6cf80-e2b5-11e8-8b5b-991b366014a5.png)
# Crayfish Commons

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://github.com/islandora/crayfish-commons/actions/workflows/build-2.x.yml/badge.svg)](https://github.com/Islandora/crayfish-commons/actions)
[![Contribution Guidelines](http://img.shields.io/badge/CONTRIBUTING-Guidelines-blue.svg)](./CONTRIBUTING.md)
[![LICENSE](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](./LICENSE)
[![codecov](https://codecov.io/gh/Islandora/Crayfish-Commons/branch/2.x/graphs/badge.svg?branch=2.x)](https://codecov.io/gh/Islandora/Crayfish-Commons)


## Introduction

Crayfish Commons is a library housing shared code for Crayfish microservices.
Shared Classes include:
* ApixMiddleware
* CmdExecuteService

## Requirements

* PHP 7.4+
* [Composer](https://getcomposer.org/)

## Installation
Crayfish-commons can be installed with composer like `composer require islandora/crayfish-commons`

## Configuration
To configure this bundle in your Symfony application, add a file in the `config/packages` or 
`config/packages/(prod|dev|test)` folder called `crayfish_commons.yaml`. The contents of this file will be
```yaml
crayfish_commons:
  fedora_base_uri: 'http://your-fedora-base-uri'
  apix_middleware_enabled: true
```

## Documentation

Further documentation for this module is available on the [Islandora documentation site](https://islandora.github.io/documentation/).


## Troubleshooting/Issues

Having problems or solved a problem? Check out the Islandora google groups for a solution.

* [Islandora Group](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora)
* [Islandora Dev Group](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora-dev)

## Maintainers

* [Eli Zoller](https://github.com/elizoller)

This project has been sponsored by:
* UPEI
* discoverygarden inc.
* LYRASIS
* McMaster University
* University of Limerick
* York University
* University of Manitoba
* Simon Fraser University
* PALS
* American Philosophical Society
* common media inc.

## Development

If you would like to contribute, please get involved by attending our weekly [Tech Call](https://github.com/Islandora/documentation/wiki). We love to hear from you!

If you would like to contribute code to the project, you need to be covered by an Islandora Foundation [Contributor License Agreement](http://islandora.ca/sites/default/files/islandora_cla.pdf) or [Corporate Contributor License Agreement](http://islandora.ca/sites/default/files/islandora_ccla.pdf). Please see the [Contributors](http://islandora.ca/resources/contributors) pages on Islandora.ca for more information.

We recommend using the [islandora-playbook](https://github.com/Islandora-Devops/islandora-playbook) to get started. If you want to pull down the submodules for development, don't forget to run `git submodule update --init --recursive` after cloning.

## License

[MIT](./LICENSE)


