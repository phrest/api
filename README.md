PhREST API
===========

A Phalcon REST API package, based on Apigees guidelines as found in http://apigee.com/about/content/web-api-design
This package is based loosely on: https://github.com/cmoore4/phalcon-rest

Please see the skeleton project for an example of the
usage: https://github.com/phrest/skeleton

To get it up and running quickly, use the vagrant box: https://github.com/phrest/box

Proposed Features
=================

Currently in development, this package will provide the following:

* Self documenting based on PHP DocBlocks
* Allows both HTTP and Internal API via PhREST SDK: http://github.com/phrest/sdk
* SDK Generator, will analyse the available calls and generate a basic SDK for request methods and response stubs

Installation
============
Include via composer "phrest/api": "dev-master"
