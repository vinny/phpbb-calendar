# EventBoard Calendar

[![Build Status](https://github.com/vinny/phpbb-calendar/actions/workflows/tests.yml/badge.svg)](https://github.com/vinny/phpbb-calendar/actions)
[![ESLint](https://img.shields.io/badge/eslint-passing-brightgreen)](https://eslint.org/)
[![Stylelint](https://img.shields.io/badge/stylelint-passing-brightgreen)](https://stylelint.io/)
[![PHPCS](https://img.shields.io/badge/phpcs-passing-brightgreen)](https://github.com/squizlabs/PHP_CodeSniffer)
[![TwigCS](https://img.shields.io/badge/twigcs-passing-brightgreen)](https://github.com/friendsoftwig/twigcs)
[![PHPStan](https://img.shields.io/badge/phpstan-passing-brightgreen)](https://phpstan.org/)
[![PHPUnit](https://img.shields.io/badge/phpunit-passing-brightgreen)](https://phpunit.de/)


EventBoard Calendar is a calendar and event manager extension for phpBB. It allows community members to create, view, and interact with public or private events directly on the forum.

## Features

- **Public & Private Events:** Create events visible to everyone or restrict visibility to specific users using secure unique access tokens.
- **FullCalendar Integration:** View and manage events through an interactive calendar interface powered by FullCalendar.
- **Location Search & Autocomplete:** Search for event venues with automatic address suggestions powered by Geoapify.
- **Map Image Generator:** Generate static maps and location previews automatically for events.
- **Event Comments:** Add and read comments on event pages to discuss details with attendees.
- **RSVP Confirmations:** Confirm participation or leave events with a single click.
- **Event Feeds:** Subscribe to event updates using discoverable Atom 1.0 feeds.
- **User Notifications:** Receive real-time alerts for event reminders, new comments, and registration changes.
- **Social Sharing:** Share events with direct links, including access tokens for private events.
- **Administration Control Panel (ACP):** Configure API keys, moderate settings, and manage categories and events from the ACP.

## Requirements

- **PHP:** `7.2.0` or higher
- **phpBB:** `3.3.1-RC1` or higher

## Installation

1. Download the extension and extract the files.
2. Upload the contents to the `ext/vinny/calendar/` directory of your phpBB forum.
3. Navigate to the Admin Control Panel (ACP) > **Customise** > **Manage extensions**.
4. Locate **EventBoard Calendar** under the disabled list and click **Enable**.

## Development

Pre-configured linting and testing tools are available to help maintain code quality during development.

### Setup

Install the Node.js and Composer development dependencies:

```bash
npm install
composer install
```

### Running Checks

To run the complete QA suite (ESLint, Stylelint, PHPCS, TwigCS, PHPStan, and PHPUnit unit tests), run:

```bash
npm test
```

You can also run specific checks individually:

| Script | Command | Purpose |
| :--- | :--- | :--- |
| **ESLint** | `npm run lint` | Lints Javascript files (`styles/all/template/*.js`) |
| **Stylelint** | `npm run stylelint` | Lints CSS stylesheets (`styles/**/*.css`) |
| **PHPCS** | `npm run lint:php` | Checks PHP coding standards (PSR-12 with phpBB conventions) |
| **TwigCS** | `npm run lint:twig` | Lints HTML/Twig templates (`styles/**/*.html`) |
| **PHPStan** | `npm run phpstan` | Performs strict PHP static analysis |
| **PHPUnit** | `npm run test:unit` | Executes the unit test suite (`tests/`) |

## Support

If you find this extension useful, you can support its development on [Ko-fi](https://ko-fi.com/vinny1).

## License

[![License](https://img.shields.io/badge/license-GPL--2.0-blue.svg)](license.txt)
