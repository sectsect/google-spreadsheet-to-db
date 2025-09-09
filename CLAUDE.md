# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Google Spreadsheet to DB is a WordPress plugin that imports data from Google Sheets into WordPress database using Google's Sheets API (v4). This is a PHP/TypeScript hybrid project with a modern frontend build system.

## Development Commands

### PHP Development
```bash
# Install PHP dependencies
cd functions/composer/
composer install

# Run PHPStan static analysis
composer phpstan

# Run PHPUnit tests
./vendor/bin/phpunit

# Run PHP CodeSniffer
./vendor/bin/phpcs

# Clear PHPStan cache
composer phpstan-clear
```

### Frontend Development
```bash
# Install Node.js dependencies
pnpm install

# Development build with watch mode
pnpm dev

# Production build
pnpm build

# Type checking
pnpm type-check
pnpm type-check:watch

# Linting
pnpm lint
pnpm lint:fix
pnpm lint:css
```

## Code Architecture

### PHP Backend Structure
- **Main Plugin File**: `google-spreadsheet-to-db.php` - Entry point, handles plugin activation and includes
- **Core Classes**: Located in `includes/` directory
  - `class-google-spreadsheet-to-db-query.php` - Database query interface for retrieving stored data
  - `class-google-spreadsheet-to-db-activator.php` - Plugin activation logic
  - `admin.php` - WordPress admin interface
  - `save.php` - Data saving functionality
  - `index.php` - Core plugin functionality
- **Helper Functions**: `functions/functions.php`
- **Database**: Uses custom table `wp_google_ss2db` to store spreadsheet data as JSON

### Frontend Structure
- **TypeScript Source**: `src/assets/ts/` - Modular TypeScript components
- **CSS Source**: `src/assets/css/` - PostCSS stylesheets
- **Build Tool**: Rspack (configured in `rspack.config.ts`)

### Configuration Constants
Plugin requires WordPress constants in `wp-config.php`:
- `GOOGLE_SS2DB_CLIENT_SECRET_PATH` - Path to Google API credentials JSON file
- `GOOGLE_SS2DB_DEBUG` (optional) - Enable debug mode for detailed JSON responses

## Key Features
- Imports Google Sheets data via API and stores as JSON in WordPress database
- Provides WordPress admin interface for configuration
- Offers hooks for data manipulation before/after save (`google_ss2db_before_save`, `google_ss2db_after_save`)
- Query API for retrieving stored data with filtering and sorting capabilities

## Quality Tools
- **PHP**: PHPStan (level 9), PHPCS with WordPress Coding Standards, PHPUnit
- **TypeScript**: ESLint with Airbnb config, TypeScript strict mode
- **CSS**: Stylelint with standard config
- **Formatting**: Prettier for JS/TS/CSS

## Testing
- PHP tests located in `tests/` directory
- Uses PHPUnit with WordPress test framework
- Test configuration in `phpunit.xml.dist`

## Dependencies
- PHP 8.0+ required
- Google API Client Library for PHP
- Modern Node.js toolchain (pnpm, TypeScript, Rspack)