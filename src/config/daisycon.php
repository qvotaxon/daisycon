<?php

return [

    /**
     * Important settings
     */

    'username' => '', // Should be your emailaddress registered with Daisycon

    'password' => '', // Your password which belongs to the username above

    'media_id' => 0,

    'publisher_id' => 0, // https://services.daisycon.com/publishers

    /**
     * Other settings
     */

    'sub_id' => '',

    'timeout' => '10.0', // time when a single request will time out when requesting remote data

    'encoding' => 'UTF-8', // ISO-8859-1 / ISO-8859-15 / UTF-8 / UTF-16 / ASCII

	'accept_html' => true, // (bool) true / false

	'feed_type' => 'csv', // xml / csv; xmlatt not yet available

	'chunksize' => 500, // only used where feed_type = csv

    /**
     * Here you can define the fields from the feed which you want to save to the database
     */
    'db_fields_to_import' => array(

		// Accommodation
		'title', 'link', 'description', 'accommodation_name', 'accommodation_type',
		'min_nr_people', 'location_description', 'stars',

		// Price
		'minimum_price', 'maximum_price', 'lowest_price',

		// Locations - Destination
		'continent_of_destination', 'country_of_destination', 'country_link',
		'region_of_destination', 'region_link', 'city_of_destination',
		'city_link', 'longitude', 'latitude',

		// Locations - Origin
		'continent_of_origin', 'country_of_origin', 'city_of_origin', 'port_of_departure',

		// Images
		'img_small', 'img_medium', 'img_large',

		// Trip
		'board_type', 'tour_operator', 'transportation_type',
		'departure-date', # to fix (see also 'migration')
		'departure_date', # to fix (see also 'migration')
		'end_date', # to fix (see also 'migration')
		'duration',

		// Daisycon internal
		'daisycon_unique_id', 'internal_id', 'unique_integer', 'update_hash'
	),

	'custom_db_fields_to_import' => array(
		'slug_accommodation_name',
		'slug_continent_of_destination',
		'slug_country_of_destination',
		'slug_region_of_destination',
		'slug_city_of_destination',
		'slug_continent_of_origin',
		'slug_country_of_origin',
		'slug_city_of_origin'
	)

];
