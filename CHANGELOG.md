# Scout Changelog

All notable changes to this project will be documented in this file.

## Unreleased
### Fixed
- Fixed missing deletion of orphans of split elements ([#259](https://github.com/studioespresso/craft-scout/issues/259))

## 3.3.3-beta.1 - 2023-12-06
### Fixed
- This beta release is a first try at resolving an issue with deindexing of element not working properly. ([#281](https://github.com/studioespresso/craft-scout/issues/281))

## 3.3.2 - 2023-11-15
### Fixed
- Fixed an error on the Control Panel Utility page ([#284](https://github.com/studioespresso/craft-scout/issues/284))
- Fixed an error when saving elements without a site property ([#285](https://github.com/studioespresso/craft-scout/issues/285****))

## 3.3.1 - 2023-11-08

### Added
- Added `AfterIndexImport` event, which fires after the ImportIndex job has finished. ([#283](https://github.com/studioespresso/craft-scout/issues/283))
### Fixed
- Deindexing now also takes the correct siteId into account ([#281](https://github.com/studioespresso/craft-scout/issues/281))

## 3.3.0 - 2023-10-23
### Added
- Added support for creating and configuring replica indexes ([#275](https://github.com/studioespresso/craft-scout/pull/275), thanks [@johnnynotsolucky](https://github.com/johnnynotsolucky)!)
### Fixed
- Fixed an error on the utility page when an index included multiple sites ([#276](https://github.com/studioespresso/craft-scout/issues/276))
- Fixed an issue where saving an element would not update it from the correct site ([#266](https://github.com/studioespresso/craft-scout/issues/266))

## 3.2.1 - 2023-09-22
### Fixed
- This fixes an issue where we default to the primary site when no site id is supplied through index settings.

## 3.2.0 - 2023-09-16
### Added
- This release adds lazy loading of the index's criteria function, to try and migigate "behaviour not found" errors ([#268](https://github.com/studioespresso/craft-scout/pull/268))

## 3.1.3 - 2023-09-10
### Fixed
- Indexing jobs now run for every site ([#266](https://github.com/studioespresso/craft-scout/issues/266)) 
- Use priority & ttr settings for all queue jobs ([#271](https://github.com/studioespresso/craft-scout/issues/271))
- Updated license to be MIT everywhere ([#272](https://github.com/studioespresso/craft-scout/issues/272))
- Fixed tests ([#263](https://github.com/studioespresso/craft-scout/issues/263))

## 3.1.2 - 2023-06-09
### Fixed
- Fixed missing return types in IndexElement & DeIndexElement jobs

## 3.1.1 - 2023-06-08
### Added
- Futher improvements to prevent scout from being initialized before Craft is ready ([#262](https://github.com/studioespresso/craft-scout/pull/262))


## 3.1.0 - 2023-02-15
### Added
- Scout now requires craft 4.3.5 (see [#249](https://github.com/studioespresso/craft-scout/issues/249))

### Fixed
- Fixed an issue where Element querries would fire before Scout was loaded ([#249](https://github.com/studioespresso/craft-scout/issues/249))
- Fixed a UI issue with button spacing on the utility screen ([#245](https://github.com/studioespresso/craft-scout/issues/245))

## 3.0.0 - 2022-05-03
### Added
- Craft 4 🚀

## 3.0.0-beta.1 - 2022-03-02
### Added
- Craft CMS 4 compatibility

## 2.7.2 - 2022-03-22
## Added
- Added `renderingContent` to `IndexSettings` ([#230](https://github.com/studioespresso/craft-scout/issues/230) & [#231](https://github.com/studioespresso/craft-scout/pull/231) - thanks [@joshuabaker](https://github.com/joshuabaker))

## 2.7.1 - 2022-03-21
### Added
- ``scout/index/import`` now optionally takes a ``--queue=1`` parameter to run the import(s) through the queue instead running them straigt away. 


## 2.7.0 - 2022-03-13
### Added
- Added a config setting to keep using the orginal object in case ``splittedObjects`` only contains 1 item. ([#193](https://github.com/studioespresso/craft-scout/issues/193) & [#219](https://github.com/studioespresso/craft-scout/pull/219), thanks [@gregkohn](https://github.com/gregkohn)) 


## 2.6.1 - 2021-12-21
### Fixed
- MakeSearchable job now uses the index criteria ([#177](https://github.com/studioespresso/craft-scout/pull/177))
- Disable relations on delete if so configured, should improve performance on larger installs ([#227](https://github.com/studioespresso/craft-scout/pull/227))

## 2.6.0 - 2021-12-06
### Added
- Added support for PHP 8.x
- Added support for algolia/algoliasearch-client-php 3.x

## 2.5.0 - 2021-10-07
### Added
- Added ``indexRelations`` config setting (true by default) ([#175](https://github.com/studioespresso/craft-scout/pull/175) & [#205](https://github.com/studioespresso/craft-scout/issues/205))
- Exposed options to update and dump index settings from the CP utility ([#209](https://github.com/studioespresso/craft-scout/pull/209))

## 2.4.2 - 2021-09-02

### Added
- Added `ShouldBeSearchableEvent` event, docs [here](https://github.com/studioespresso/craft-scout/tree/master#shouldbesearchableevent) ([#205](https://github.com/studioespresso/craft-scout/issues/205))

## 2.4.1 - 2021-08-13

### Fixed
- CP Utility now works for indexes using the wildcard selector for siteId ([#163](https://github.com/studioespresso/craft-scout/issues/163))
- CP Utility labels are now translatable
- Fixed compatibility with Craft Commerce ([#178](https://github.com/studioespresso/craft-scout/issues/178))

## 2.3.1 - 2020-06-16

- Don't include indices in Settings::toArray() by default 

## 2.3.0 - 2020-04-09

- Remove pro requirement for Utility

## 2.2.2 - 2020-03-09

- Allow tightenco/collect ^7.0

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
