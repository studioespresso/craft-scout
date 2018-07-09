# Scout Changelog

All notable changes to this project will be documented in this file.

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
