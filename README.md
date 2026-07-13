# EventBoard Calendar

[![Build Status](https://github.com/vinny/phpbb-calendar/actions/workflows/tests.yml/badge.svg)](https://github.com/vinny/phpbb-calendar/actions)
[![ESLint](https://img.shields.io/badge/eslint-passing-brightgreen)](https://eslint.org/)
[![Stylelint](https://img.shields.io/badge/stylelint-passing-brightgreen)](https://stylelint.io/)
[![PHPCS](https://img.shields.io/badge/phpcs-passing-brightgreen)](https://github.com/squizlabs/PHP_CodeSniffer)
[![TwigCS](https://img.shields.io/badge/twigcs-passing-brightgreen)](https://github.com/friendsoftwig/twigcs)
[![PHPStan](https://img.shields.io/badge/phpstan-passing-brightgreen)](https://phpstan.org/)
[![PHPUnit](https://img.shields.io/badge/phpunit-passing-brightgreen)](https://phpunit.de/)

Calendar and event manager extension for phpBB 3.3+.

## Features

- **Public & Private Events**: Create events visible to everyone or restrict visibility to specific users using secure unique access tokens.
- **[FullCalendar.io](https://v7.fullcalendar.io/) Integration**: A beautiful, interactive calendar interface to view and manage events seamlessly.
- **Location Search & Autocomplete**: Integrated location lookup with suggestions for event venues (powered by [Geoapify](https://www.geoapify.com/)).
- **Map Image Generator**: Automatically generates static maps and images of locations for events.
- **Event Comments**: Interactive comment section on events.
- **RSVP Confirmations**: Attendees can confirm participation or leave events with a single click.
- **Event Feeds**: Discoverable RSS/Atom feeds (Atom 1.0) allowing users to subscribe to event updates.
- **User Notifications**: Real-time notifications for event reminders, comments, and registration changes.
- **Social Sharing**: Easily share events with direct links, including access tokens for private events.
- **Administration Control Panel (ACP)**: Complete control for administrators to configure API keys, manage categories, events, and moderate settings.


## Development: Quality Assurance & Testing

For development, the extension comes with pre-configured static analysis and testing tools to maintain 100% code quality.

### Prerequisites

Ensure you have [Node.js](https://nodejs.org/) and [Composer](https://getcomposer.org/) installed globally on your machine.

### Setup Dependencies

To set up Node and Composer development tools, run:

```bash
npm install
composer install
```

### Running Checks

To run the complete QA suite (ESLint, Stylelint, PHPCS, TwigCS, PHPStan, and PHPUnit unit tests), execute:

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
| **PHPUnit** | `npm run test:unit` | Executes unit test suite (`tests/`) |


## Support this project

If you find this extension useful, you can support its development by buying me a coffee!

[![Support me on Ko-fi](https://camo.githubusercontent.com/201ef269611db7eb6b5d08e9f756ab8980df3014b64492770bdf13a6ed924641/68747470733a2f2f6b6f2d66692e636f6d2f696d672f676974687562627574746f6e5f736d2e737667)](https://ko-fi.com/vinny1)


## License
[GNU General Public License v2](license.txt)


