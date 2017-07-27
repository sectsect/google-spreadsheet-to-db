# <img src="https://github-sect.s3-ap-northeast-1.amazonaws.com/logo.svg" width="28" height="auto"> Google Spreadsheet to DB
[![Build Status](https://travis-ci.org/sectsect/google-spreadsheet-to-db.svg?branch=master)](https://travis-ci.org/sectsect/google-spreadsheet-to-db) [![PHP-Eye](https://php-eye.com/badge/sectsect/google-spreadsheet-to-db/tested.svg?style=flat)](https://php-eye.com/package/sectsect/google-spreadsheet-to-db) [![Latest Stable Version](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/v/stable)](https://packagist.org/packages/sectsect/google-spreadsheet-to-db)  [![License](https://poser.pugx.org/sectsect/google-spreadsheet-to-db/license)](https://packagist.org/packages/sectsect/google-spreadsheet-to-db)

### "Google Spreadsheet to DB" pulls Google Spreadsheet data via Google’s API and saves it in your wordpress database.

## Requirements

- PHP 5.5+
- Composer

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
3. Click Enable API. Search for and enable the Google Drive API.
4. Create credentials for a Web Server to access Application Data.
5. Name the service account and grant it a Project Role of Editor.
6. Download the JSON file.
7. Copy the JSON file to your app directory and rename it to `client_secret.json`
8. :warning: Set `client_secret.json` in a location to deny web access on your server.

We now have a big chunk of authentication information, including what Google calls a `client_email`, which uniquely represents this OAuth service account.  
Grab the value of `client_email` from your `client_secret.json`, and head back to your spreadsheet. Click the Share button in the top right, and paste the `client_email` value into the field to give it edit rights.  
Hit send. That’s it! :ok_hand:

1. Go to `Settings` -> `Google Spreadsheet to DB` on your wordpress admin panel.
2. Set the following values and save it once.
  - The absolute path to `client_secret.json`
  - Spreadsheet name
  - Single Sheet name
  - Data format to be stored in database
    - json_encode
	- json_encode (JSON_UNESCAPED_UNICODE)
3. Click the `Data import from Google Spreadsheet` button. :tada:

## Filters

### Filtering the Array

You can edit the array got from Google API with `add_filter( 'google_ss2db_before_save', $function_to_add )` in your functions.php before saving to database.

```php
add_filter( 'google_ss2db_before_save', function ( $array ) {
	// Do something...

	return $return;
} );
```

## Functions

### Get the rows
```php
new Google_Spreadsheet_To_DB_Query();
```

#### Parameters

| Parameter |      | Type | Notes  | Default Value |
| -------- | ----- | ------- | ------ | ------ |
| where    |       | array  | `id` or `date`  | `array()` |
|          | key   | string | `id` or `date`  |  `false` |
|          | value | int    | e.g. `3` / `2017-07-24 18:46:23` |  `false` |
| orderby  |       | string | `date` or `id`  | `date` |
| order    |       | string | `DESC` or `ASC` | `DESC` |
| limit    |       | int    | number of row to get  | All Data<br>:memo: You can also use `-1` to get all data. |
| offset   |       | int    | number of row to displace or pass over | `0` |

## Usage Example

#### Get all rows
```php
$sheets = new Google_Spreadsheet_To_DB_Query();
$rows = $sheets->getrow();
foreach ( $rows as $row ) {
	$id = $row->id;
	$date = $row->date;
	$val = json_decode( $row->value );
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
$rows = $sheets->getrow();
foreach ( $rows as $row ) {
	$id = $row->id;
	$date = $row->date;
	$val = json_decode( $row->value );
}
```

#### Get the row with specified ID
```php
$args = array(
	'where' => array(
		'key'   => 'id',
		'value' => 3
	),
);
$sheets = new Google_Spreadsheet_To_DB_Query( $args );
$rows = $sheets->getrow();
foreach ( $rows as $row ) {
	$id = $row->id;
	$date = $row->date;
	$val = json_decode( $row->value );
}
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
  <th>title</th>
  <th>value</th>
  </tr>
  </thead>
  <tbody>
  <tr>
  <td>1</td>
  <td>2017-12-31 00:00:00</td>
  <td>My Spreadsheet</td>
  <td><code style="word-break: break-all;">{"area":{"a":["brooklyn","bronx","Queens","Manhattan"],"b":["brooklyn","bronx","Queens","Manhattan"]}}</code></td>
  </tr></tbody></table>

* This Plugin does not hosting on the [wordpress.org](https://wordpress.org/) repo in order to prevent a flood of support requests from wide audience.

## Change log  

See [CHANGELOG](https://github.com/sectsect/google-spreadsheet-to-db/blob/master/CHANGELOG.md) file.

## License

See [LICENSE](https://github.com/sectsect/google-spreadsheet-to-db/blob/master/LICENSE) file.
