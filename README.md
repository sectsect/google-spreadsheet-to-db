# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="18" height="auto"> Google Spreadsheet to DB
[![Build Status](https://travis-ci.com/sectsect/google-spreadsheet-to-db.svg?branch=master)](https://travis-ci.com/sectsect/google-spreadsheet-to-db) [![Latest Stable Version](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/v)](//packagist.org/packages/sectsect/google-spreadsheet-to-db)  [![Total Downloads](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/downloads)](//packagist.org/packages/sectsect/google-spreadsheet-to-db) [![Latest Unstable Version](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/v/unstable)](//packagist.org/packages/sectsect/google-spreadsheet-to-db) [![License](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/license)](//packagist.org/packages/sectsect/google-spreadsheet-to-db)

### Fetch Google Sheets with [Google Sheets API](https://developers.google.com/sheets/api) (v4) and store it to your WordPress database. You can also filter that data before saving.

## Requirements

- PHP 5.5+
- [Composer](https://getcomposer.org/)

## Installation

##### 1. Clone this Repo into your `wp-content/plugins` directory.
```sh
$ cd /path-to-your/wp-content/plugins/
$ git clone git@github.com:sectsect/google-spreadsheet-to-db.git
```

##### 2. Install composer packages
```sh
$ cd google-spreadsheet-to-db/functions/composer/
$ composer install
```

##### 3. Activate the plugin through the 'Plugins' menu in WordPress.<br>
That's it:ok_hand:

## Settings

### Getting Your Spreadsheet Ready for Programmatic Access

By default, a new spreadsheet cannot be accessed via Google’s API. We’ll need to go to your Google APIs console and create a new project and set it up to expose your Spreadsheets’ data.

1. Go to the [Google APIs Console](https://console.developers.google.com/).
2. Create a new project.
3. Click Enable API. Search for and enable the Google Sheets API.
4. Create credentials for a Web Server to access Application Data.
5. Name the service account and grant it a Project Role of Editor.
6. Download the JSON file.
7. Copy the JSON file to your app directory and rename it to `client_secret.json`
8. :warning: Set `client_secret.json` in a location to deny web access on your server.

We now have a big chunk of authentication information, including what Google calls a `client_email`, which uniquely represents this OAuth service account.  
Grab the value of `client_email` from your `client_secret.json`, and head back to your spreadsheet. Click the Share button in the top right, and paste the `client_email` value into the field to give it edit rights.  
Hit send. That’s it! :ok_hand:


1. Set the `define()` constants for client_secret.json in <code>wp-config.php</code>.
  ```php
  define( 'GOOGLE_SS2DB_CLIENT_SECRET_PATH', '/path/to/your/client_secret.json' );
  ```
2. Go to `Settings` -> `Google Spreadsheet to DB` on your WordPress Admin-Panel.
3. Set the following values and save it once.
  - Data format to be stored in database
    - json_encode
    - json_encode (JSON_UNESCAPED_UNICODE)
4. Click the `Import from Google Spreadsheet` button. :tada:
  - Spreadsheet ID
  - Spreadsheet name (Optional)
  - Single Sheet name
  - Top Header Row
  - Title (Optional)

## Filters

### Filtering the Array

You can edit the array got from Google API with `add_filter( 'google_ss2db_before_save', $function_to_add )` in your functions.php before saving to database.

```php
add_filter( 'google_ss2db_before_save', function ( $row, $worksheetid, $worksheetname, $sheetname ) {
  // Example
  if ( $worksheetname === 'My Spreadsheet' && $sheetname === 'Sheet1' ) {
    // Do something.
    $return = $something;
  } else {
    $return = $row;
  }

  return $return;
}, 10, 3 );
```

And also use `add_filter('google_ss2db_after_save', $return_array )` to perform any processing with the return value.
```php
add_filter( 'google_ss2db_after_save', function ( $array ) {
  if ( 'My Spreadsheet' === $worksheetname ) {
    // $id              = $array['id'];
    // $date            = $array['date'];
    // $title           = $array['title'];
    // $value           = $array['value'];
    // $work_sheet_id   = $array['worksheet_id'];
    // $work_sheet_name = $array['worksheet_name'];
    // $sheet_name      = $array['sheet_name'];
    // $result          = $array['result'];
    
    // Do something...
    $return = $something;
  } else {
    $return = $array;
  }

  return $return;
} );
```

## APIs

```php
new Google_Spreadsheet_To_DB_Query();
```

#### Parameters

| Parameter |          |         | Type   | Notes                                      | Default Value |
| --------- | -------- | ------- | ------ | ------------------------------------------ | ------------- |
| where     |          |         | array  |                                            | `array()`     |
|           | relation |         | string | `AND` or `OR`                              |  `AND`        |
|           | [array]  |         | array  |                                            |               |
|           |          | key     | string | `id` or `date` or `worksheet_id` or `worksheet_name` or `sheet_name` or `title` |  `false`      |
|           |          | value   | int    | e.g. `3` / `2020-09-01 12:00:00`           |  `false`      |
|           |          | compare | string | e.g. `=`  `>`  `<`  `>=`  `<=`  `<>`  `!=` |  `=`          |
| orderby   |          |         | string | `id` or `date` or `worksheet_id` or `worksheet_name` or `sheet_name` or `title` | `date`        |
| order     |          |         | string | `DESC` or `ASC`                            | `DESC`        |
| limit     |          |         | int    | number of row to get                       | All Data<br>:memo: You can also use `-1` to get all data. |
| offset    |          |         | int    | number of row to displace or pass over     | `0`           |

## Usage Example

#### Get all rows
```php
$sheets = new Google_Spreadsheet_To_DB_Query();
$rows   = $sheets->getrow();
foreach ( $rows as $row ) {
  $id   = $row->id;
  $date = $row->date;
  $val  = json_decode( $row->value );
}
```

#### Get 3 rows from the 4th in ascending order by ID
```php
$args = array(
  'orderby' => 'id',
  'order'   => 'ASC',
  'limit'   => 3,
  'offset'  => 3,
);
$sheets = new Google_Spreadsheet_To_DB_Query( $args );
$rows   = $sheets->getrow();
foreach ( $rows as $row ) {
  $id   = $row->id;
  $date = $row->date;
  $val  = json_decode( $row->value );
}
```

#### Get the row with specific ID
```php
$args = array(
  'where' => array(
    array(
      'key'   => 'id',
      'value' => 3,
    )
  ),
);
```

#### Get 3 rows with specific Worksheet ordered by ID
```php
$args = array(
  'orderby' => 'id',
  'order'   => 'ASC',
  'limit'   => 3,
  'where'   => array(
    array(
      'key'     => 'worksheet_name',
      'value'   => 'My Spreadsheet',
      'compare' => '='
    ),
  ),
);
```

#### Get the rows larger than or equal the specified datetime
```php
$args = array(
  'where' => array(
    array(
      'key'     => 'date',
      'value'   => '2020-08-01 12:34:56',
      'compare' => '>=',
    )
  ),
);
```

#### Get the rows with multiple conditions
```php
$args = array(
  'orderby' => 'id',
  'order'   => 'DESC',
  'limit'   => 10,
  'offset'  => 10,
  'where'   => array(
    'relation' => 'AND', // or 'OR'
    array(
      'key'     => 'date',
      'value'   => '2020-08-01 12:34:56',
      'compare' => '>='
    ),
    array(
      'key'     => 'worksheet_name',
      'value'   => 'My Spreadsheet',
      'compare' => '='
    ),
  ),
);
```

## Notes

* Tested on WP v4.8.0

## Notes for Developer

* This plugin saves Spreadsheet's data to the global area, not to each post. If you want to have Spredsheet data for individual posts, you can link data `ID` with custom fields.
* The data is added and stored in the `wp_google_ss2db` table as a JSON-encoded array.

  <table>
  <thead>
  <tr>
  <th>id</th>
  <th>date</th>
  <th>worksheet_id</th>
  <th>worksheet_name</th>
  <th>sheet_name</th>
  <th>title</th>
  <th>value</th>
  </tr>
  </thead>
  <tbody>
  <tr>
  <td>1</td>
  <td>2021-08-27 00:00:00</td>
  <td>1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms</td>
  <td>My Spreadsheet</td>
  <td>Sheet1</td>
  <td>Data-01</td>
  <td><code style="word-break: break-all;">{"area":{"a":["brooklyn","bronx","Queens","Manhattan"],"b":["brooklyn","bronx","Queens","Manhattan"]}}</code></td>
  </tr></tbody></table>

* This Plugin does not hosting on the [wordpress.org](https://wordpress.org/) repo in order to prevent a flood of support requests from wide audience.

## Change log  

See [CHANGELOG](https://github.com/sectsect/google-spreadsheet-to-db/blob/master/CHANGELOG.md) file.

## License

See [LICENSE](https://github.com/sectsect/google-spreadsheet-to-db/blob/master/LICENSE) file.

## Known Issues

#### `Call to undefined method GuzzleHttp\Utils::chooseHandler()`

- `google/apiclient` [v2.6.0](https://github.com/googleapis/google-api-php-client/releases/tag/v2.6.0) or later used inside Google Sheets API (v4) has **v7** in `guzzlehttp/guzzle` added as a dependency.
- `guzzlehttp/guzzle` v7 throws the following error.
  
  ```
  Call to undefined method GuzzleHttp\Utils::chooseHandler()
  ```
- So force downgrade below to v6 of `guzzlehttp/guzzle` because rollback and errors are resolved
  ```
  composer require guzzlehttp/guzzle:~6.0 --with-all-dependencies
  ```
  
  <details>
  <summary>OUTPUT</summary>

  ```
  % composer require guzzlehttp/guzzle:~6.0 --with-all-dependencies
  ./composer.json has been updated
  Running composer update guzzlehttp/guzzle --with-all-dependencies
  Loading composer repositories with package information
  Updating dependencies
  Lock file operations: 3 installs, 2 updates, 3 removals
    - Removing psr/http-client (1.0.1)
    - Removing psr/http-factory (1.0.1)
    - Removing symfony/deprecation-contracts (v2.5.2)
    - Downgrading guzzlehttp/guzzle (7.5.0 => 6.5.8)
    - Downgrading guzzlehttp/psr7 (2.4.1 => 1.9.0)
    - Locking symfony/polyfill-intl-idn (v1.26.0)
    - Locking symfony/polyfill-intl-normalizer (v1.26.0)
    - Locking symfony/polyfill-php72 (v1.26.0)
  Writing lock file
  Installing dependencies from lock file (including require-dev)
  Package operations: 3 installs, 2 updates, 3 removals
    - Downloading guzzlehttp/psr7 (1.9.0)
    - Downloading symfony/polyfill-php72 (v1.26.0)
    - Downloading symfony/polyfill-intl-normalizer (v1.26.0)
    - Downloading symfony/polyfill-intl-idn (v1.26.0)
    - Downloading guzzlehttp/guzzle (6.5.8)
    - Removing symfony/deprecation-contracts (v2.5.2)
    - Removing psr/http-factory (1.0.1)
    - Removing psr/http-client (1.0.1)
    - Downgrading guzzlehttp/psr7 (2.4.1 => 1.9.0): Extracting archive
    - Installing symfony/polyfill-php72 (v1.26.0): Extracting archive
    - Installing symfony/polyfill-intl-normalizer (v1.26.0): Extracting archive
    - Installing symfony/polyfill-intl-idn (v1.26.0): Extracting archive
    - Downgrading guzzlehttp/guzzle (7.5.0 => 6.5.8): Extracting archive
  Generating autoload files
  8 packages you are using are looking for funding.
  Use the `composer fund` command to find out more!
  No security vulnerability advisories found

  % composer show
  firebase/php-jwt                 v6.3.0    A simple library to encode and decode JSON Web Tokens (JWT) in PHP. Should conform to the current s...
  google/apiclient                 v2.12.6   Client library for Google APIs
  google/apiclient-services        v0.271.0  Client library for Google APIs
  google/auth                      v1.23.0   Google Auth Library for PHP
  guzzlehttp/guzzle                6.5.8     Guzzle is a PHP HTTP client library
  guzzlehttp/promises              1.5.2     Guzzle promises library
  guzzlehttp/psr7                  1.9.0     PSR-7 message implementation that also provides common utility methods
  monolog/monolog                  2.8.0     Sends your logs to files, sockets, inboxes, databases and various web services
  paragonie/constant_time_encoding v2.6.3    Constant-time Implementations of RFC 4648 Encoding (Base-64, Base-32, Base-16)
  paragonie/random_compat          v9.99.100 PHP 5.x polyfill for random_bytes() and random_int() from PHP 7
  phpseclib/phpseclib              3.0.16    PHP Secure Communications Library - Pure-PHP implementations of RSA, AES, SSH2, SFTP, X.509 etc.
  psr/cache                        1.0.1     Common interface for caching libraries
  psr/http-message                 1.0.1     Common interface for HTTP messages
  psr/log                          1.1.4     Common interface for logging libraries
  ralouphie/getallheaders          3.0.3     A polyfill for getallheaders.
  symfony/polyfill-intl-idn        v1.26.0   Symfony polyfill for intl's idn_to_ascii and idn_to_utf8 functions
  symfony/polyfill-intl-normalizer v1.26.0   Symfony polyfill for intl's Normalizer class and related functions
  symfony/polyfill-php72           v1.26.0   Symfony polyfill backporting some PHP 7.2+ features to lower PHP versions
  ```
  </details>




<p align="center">✌️</p>
<p align="center">
<sub><sup>A little project by <a href="https://github.com/sectsect">@sectsect</a></sup></sub>
</p>
