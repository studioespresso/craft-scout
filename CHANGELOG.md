# Scout Changelog

All notable changes to this project will be documented in this file.

## 2.2.1 - 2020-03-01
- Fix Fractal dependency to stay compatible with element-api

## 2.2.0 - 2020-03-01
- Fix dependencies, now requires PHP >= 7.1

## 2.1.4 - 2020-01-03
- Fix an issue where engine could be null - #121

## 2.1.3 - 2019-11-21
- Fix queued indexing error

## 2.1.2 - 2019-11-20
- Fix Index settings not being forwarded to replicas

## 2.1.1 - 2019-11-19
- Don't use element as a prop, as it will get serialized with the whole class int the job queue.
- Defer related element propagation to queue

## 2.1.0 - 2019-09-20

- Use a custom Serializer for Fractal (thanks @santi6291)

## 2.0.2 - 2019-09-19

- Fixed a type hinting issue

## 2.0.1 - 2019-09-17

- Fixed a bug when using `->site('*')` in your criteria, thanks @timkelty

## 2.0.0 - 2019-09-13

> {warning} This is a major release, the way you configure Scout has been changed, please read the [docs](https://github.com/riasvdv/craft-scout/blob/master/README.md#upgrading) on how to upgrade.

- Rewrite of the full plugin
- Added tests for all functionality
- Added a comprehensive syntax to configure the Indices
- Added a settings page for all settings except Indices

## 1.3.2 - 2019-09-05
- Resolve fractal conflict

## 1.3.1 - 2019-08-09
- Fixed a bug when skipping elements when splitElementIndex is also set (#81)

## 1.3.0 - 2019-07-12
- Now requires Craft 3.2
- Added a fix for Element drafts & revisions in Craft 3.2

## 1.2.3 - 2019-06-14
- Revert untested change

## 1.2.2 - 2019-06-14
- Fixed an issue with settings call 

## 1.2.1 - 2019-05-17
### Added
- Scout can now skip elements if you return an empty array from the transformer
- Documented the `--force` option

## 1.2.0 - 2019-05-01
### Changed
- Scout now uses v2 of the Algolia API in the background. Nothing has changed in Scout usage.

## 1.1.4 - 2019-04-10
### Added
- `search_api_key` setting to model
- Twig variables for accessing Algolia settings in front end templates

### Changed
- Normalised the mixture of quotes in the config example documentation

## 1.1.3 - 2019-02-27
- Fixed a regression by the previous release when deleting elements would not deIndex them.

## 1.1.2 - 2019-02-13
- Fixed an issue with deindexing elements, thanks @chrislam

## 1.1.1 - 2019-01-21
- Fixes issue with console commands on Folder capitalization, thanks @philipzaengle

## 1.1.0 - 2018-12-03
- Added an `indexSettings` option to mappings that allows for code-based index settings. Thanks to @timkelty
- Added `./craft scout/settings/update` and `./craft scout/settings/dump` commands.

## 1.0.2 - 2018-11-23
- Fixed validator int --> integer
- Added siteId to splitElementIndex

## 1.0.1 - 2018-10-29
### Fixed
- cast site ids to int as craft/yii returns int as string (#39) @larsboldt
- add connect timeout option (#38) @larsboldt
- Replace call to `deIndexElements` with `indexElements` (#37)

## 1.0.0 - 2018-10-07
### Fixed
- Fixed an issue where objects weren't being deleted from the index
- Fixed an issue where splitting indices on site or siteId wouldn't work

### Added
- Added a "refresh" command, thanks to @JorgeAnzola

## 0.4.2 - 2018-07-09
### Fixed
- Object IDs are now unique for multisites

## 0.4.1 - 2018-05-01
### Added
- Added a `sync` setting to enable/disable automatic indexing of elements.

## 0.4.0 - 2018-04-17
### Added
- Records can now be split (see: [https://www.algolia.com/doc/guides/indexing/structuring-your-data/?language=php#indexing-long-documents](https://www.algolia.com/doc/guides/indexing/structuring-your-data/?language=php#indexing-long-documents)) thanks to @larsboldt

## 0.3.0 - 2018-03-12
### Changed
- Changed how the queueing of indexing works to prevent errors and inconsistencies

## 0.2.8 - 2018-02-28
### Fixed
- Fixed an error when trying to serialize an Entry that cannot be serialized

## 0.2.7 - 2017-02-27
### Changed
- Updated the League/Fractal requirement to match that of Element API to avoid conflicts

## 0.2.4 - 2017-12-12
### Added
- Craft Scout now listens to Category events to determine if it needs to reindex elements

## 0.2.1 - 2017-12-12
### Added
- A new icon!

## 0.2.0 - 2017-12-12
### Added
- Console commands to flush & import indexes
### Changed
- Index filtering is now based on criteria instead of a function

## 0.1.1 - 2017-12-12
### Added
- Move indexing to jobs

## 0.1.0 - 2017-12-11
### Added
- Initial release
