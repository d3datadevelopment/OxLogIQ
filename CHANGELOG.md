# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://git.d3data.de/D3Public/OxLogIQ/compare/1.0.0.0...rel_1.x)

## [1.1.0.0](https://git.d3data.de/D3Public/OxLogIQ/compare/1.0.0.0...1.1.0.0) - 2025-12-10
### Added
- optional [Sentry](https://sentry.io) handler
- optional HTTP API handler e.g. for ElasticSearch using a PSR compatible client
- shutdown function to log PHP errors as well
- errors during logger creation dealt with
- extension examples

### Changed
- extensibility improved
- "mail notifification" renamed to "mail alert"
- release service extracted

## [1.0.0.0](https://git.d3data.de/D3Public/OxLogIQ/releases/tag/1.0.0.0) - 2025-10-30
### Added
- Date-separated logging (adjustable)
- File rotation (adjustable)
- Alerting of critical events by email (adjustable)
- Request ID (filter criterion)
- Session ID (filter criterion)
- Buffering (optimisation of write operations)
- Add code references to entries (errors and higher)
- Channel complemented by front end or back end
- Channel complemented by subshop
- Simple configuration via `config.inc.php` or environment variables
