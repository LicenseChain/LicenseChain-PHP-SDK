# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- README PHP badge aligned to 8.2+; added `phpunit.xml.dist`; CI also runs on `dev`.

## [1.0.3] - 2026-10-12

- Removed root composer.json `version` field so Packagist versions come from Git tags.

## [1.0.2] - 2026-10-10

- Aligned CI PHP matrix to 8.2/8.3/8.4 (matches `composer.json` `php: >=8.2`).
- Bumped `actions/checkout` to v7 in CI.

## [1.0.1] - 2026-10-10

- Relicensed public surface under Elastic License 2.0 (ELv2).
- Raised PHP requirement from `>=8.0` to `>=8.2`.
- Added Dependabot for Composer and GitHub Actions.

## [1.0.0] - 2026-04-06

- Initial public SDK surface with repository documentation kit.
