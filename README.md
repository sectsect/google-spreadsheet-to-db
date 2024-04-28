# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="18" height="auto"> Google Spreadsheet to DB
[![PHP Unit Tests](https://github.com/sectsect/google-spreadsheet-to-db/actions/workflows/phpunit.yml/badge.svg)](https://github.com/sectsect/google-spreadsheet-to-db/actions/workflows/phpunit.yml) [![PHPStan](https://github.com/sectsect/google-spreadsheet-to-db/actions/workflows/phpstan.yml/badge.svg)](https://github.com/sectsect/google-spreadsheet-to-db/actions/workflows/phpstan.yml) [![PHP Coding Standards](https://github.com/sectsect/google-spreadsheet-to-db/actions/workflows/phpcs.yml/badge.svg)](https://github.com/sectsect/google-spreadsheet-to-db/actions/workflows/phpcs.yml) [![Latest Stable Version](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/v)](//packagist.org/packages/sectsect/google-spreadsheet-to-db)

### The "Google Spreadsheet to DB" plugin is designed for WordPress and facilitates the import of data from Google Sheets into a WordPress database using Google's [Sheets API](https://developers.google.com/sheets/api) (v4). It supports data manipulation before saving and is configurable via a WordPress admin interface.

## Features
- Data Import: Pulls data from Google Sheets and saves it directly into the WordPress database.
- Customization: Offers settings for defining constants, spreadsheet IDs, names, and configuring data formats.
- Admin Interface: Provides an admin page for easy management and configuration of the plugin settings.

## Requirements
- PHP version 8.0 or higher.
- [Composer](https://getcomposer.org/) for managing PHP dependencies.

## Get Started

##### 1. Clone this Repo into your `wp-content/plugins` directory.
```sh
cd /path-to-your/wp-content/plugins/
git clone git@github.com:sectsect/google-spreadsheet-to-db.git
```

##### 2. Remove `vendor/` in `.gitignore` file.
```sh
cd google-spreadsheet-to-db
nano .gitignore
```

```diff
- vendor/
```

##### 3. Install composer packages
```sh
cd functions/composer/
composer install
```

##### 4. Activate the plugin through the 'Plugins' menu in WordPress.

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

    return $something;
  }

  return $row;
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
$sheet = new Google_Spreadsheet_To_DB_Query();
$rows  = $sheet->getrow();
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
$sheet = new Google_Spreadsheet_To_DB_Query( $args );
$rows  = $sheet->getrow();
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

* Tested on WordPress v6.3.1

## For Developers

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

## Troubleshooting

> `Uncaught Error: Call to undefined method GuzzleHttp\Utils::chooseHandler()`

Some of WordPress plugins or Composer packages you are using depend on HTTP client [Guzzle](https://github.com/guzzle/guzzle) **v6** (legacy).  
You can resolve this error by updating to **v7** dependent version.

## Change log  

See [CHANGELOG](https://github.com/sectsect/google-spreadsheet-to-db/blob/master/CHANGELOG.md) file.

## License

See [LICENSE](https://github.com/sectsect/google-spreadsheet-to-db/blob/master/LICENSE) file.

<p align="center">✌️</p>
<p align="center">
<sub><sup>A little project by <a href="https://github.com/sectsect">@sectsect</a></sup></sub>
</p>
