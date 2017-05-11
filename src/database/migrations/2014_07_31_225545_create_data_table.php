<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('data', function(Blueprint $table)
		{
			// Keep the same (sort of) with your config-file
			$table->engine = 'InnoDB';

			$table->string('accommodation_address', 250);
			$table->integer('accommodation_bathrooms');
			$table->integer('accommodation_bedrooms');
			$table->boolean('accommodation_child_friendly');
			$table->integer('accommodation_floors');
			$table->date('accommodation_lowest_date');
			$table->double('accommodation_lowest_price');
			$table->string('accommodation_name', 250);
			$table->boolean('accommodation_on_holiday_park');
			$table->boolean('accommodation_pets_allowed');
			$table->integer('accommodation_rooms');
			$table->boolean('accommodation_smoking_allowed');
			$table->integer('accommodation_sqm_floors');
			$table->integer('accommodation_toilets');
			$table->string('accommodation_type', 250);
			$table->double('additional_costs');
			$table->date('arrival_date');
			$table->boolean('available');
			$table->date('available_from');
			$table->string('brand', 200);
			$table->string('brand_logo', 250);
			$table->string('category', 20);
			$table->string('category_path', 250);
			$table->string('color_primary', 20);
			$table->string('condition', 100);
			$table->string('currency', 20);
			$table->string('currency_symbol', 20);
			$table->integer('daisycon_unique_id');
			$table->date('daisycon_unique_id_modified');
			$table->date('daisycon_unique_id_since');
			$table->string('data_hash', 250);
			$table->date('delete_date');
			$table->mediumText('delivery_description');
			$table->integer('delivery_time');
			$table->date('departure_date');
			$table->longText('description');
			$table->text('description_short');
			$table->string('destination_city', 200);
			$table->string('destination_city_link', 250);
			$table->string('destination_continent', 200);
			$table->string('destination_country', 200);
			$table->longText('destination_country_description');
			$table->string('destination_country_link', 250);
			$table->string('destination_language', 30);
			$table->string('destination_latitude', 30);
			$table->longText('destination_location_description');
			$table->string('destination_longitude', 30);
			$table->string('destination_port', 200);
			$table->string('destination_region', 100);
			$table->string('destination_region_link', 250);
			$table->string('destination_zipcode', 20);
			$table->integer('distance_to_bakery');
			$table->integer('distance_to_beach');
			$table->integer('distance_to_citycenter');
			$table->integer('distance_to_golfcourse');
			$table->integer('distance_to_restaurant');
			$table->integer('distance_to_shopping');
			$table->integer('distance_to_swimwater');
			$table->integer('duration_days');
			$table->integer('duration_nights');
			$table->integer('ean');
			$table->string('gender_target', 10);
			$table->boolean('has_airco');
			$table->boolean('has_barbecue');
			$table->boolean('has_child_chair');
			$table->boolean('has_dishwasher');
			$table->boolean('has_electricity');
			$table->boolean('has_garage');
			$table->boolean('has_garden');
			$table->boolean('has_heating');
			$table->boolean('has_internet');
			$table->boolean('has_livingroom');
			$table->boolean('has_microwave');
			$table->boolean('has_playground');
			$table->boolean('has_sauna');
			$table->boolean('has_swimmingpool');
			$table->boolean('has_telephone');
			$table->boolean('has_television');
			$table->boolean('has_washingmachine');
			$table->string('image_large', 250);
			$table->string('image_medium', 250);
			$table->string('image_small', 250);
			$table->boolean('in_stock');
			$table->integer('in_stock_amount');
			$table->date('insert_date');
			$table->string('keywords', 250);
			$table->date('last_modified');
			$table->string('link', 250);
			$table->integer('max_nr_people');
			$table->string('model', 50);
			$table->string('name', 250);
			$table->integer('previous_daisycon_unique_id');
			$table->double('price');
			$table->double('price_old');
			$table->double('price_shipping');
			$table->integer('priority');
			$table->integer('product_count');
			$table->string('size', 20);
			$table->mediumText('size_description');
			$table->string('sku', 25);
			$table->integer('star_rating');
			$table->string('status', 25);
			$table->longText('terms_conditions');
			$table->string('title', 250);
			$table->string('travel_trip_type', 100);
			$table->string('trip_holiday_type', 25);
			$table->date('update_date');

//			$table->string('update_hash', 100);

			$table->integer('id');

//			$table->increments('id');

//
//			$table->string('slug_accommodation_name', 100);
//			$table->string('slug_continent_of_destination', 100);
//			$table->string('slug_country_of_destination', 100);
//			$table->string('slug_region_of_destination', 100);
//			$table->string('slug_city_of_destination', 100);
//			$table->string('slug_continent_of_origin', 100);
//			$table->string('slug_country_of_origin', 100);
//			$table->string('slug_city_of_origin', 100);


			$table->integer('program_id'); // Could be used for internal use
			$table->integer('feed_id'); // Could be used for internal use

//			$table->string('board_type', 50);

//			$table->string('destination_country_link', 250);
//			$table->integer('distance_from');
//			$table->date('departure_date');
//			$table->string('destination_region', 200);

//			$table->string('region_of_destination', 100);
//			$table->string('city_of_destination', 100);
//			$table->string('continent_of_origin', 100);


//			$table->integer('min_nr_people');
//			$table->string('continent_of_destination', 100);
//			$table->string('country_of_destination', 100);
//			$table->string('country_link', 255);
//			$table->string('region_link', 255);
//			$table->string('city_link', 255);
//			$table->string('longitude', 50);
//			$table->string('latitude', 50);
//			$table->string('country_of_origin', 100);
//			$table->string('city_of_origin', 100);
//			$table->string('port_of_departure', 100);
//			$table->string('img_small', 255);
//			$table->string('img_medium', 255);
//			$table->string('img_large', 255);

//			$table->string('tour_operator', 50);
//			$table->string('transportation_type', 50);
//			$table->datetime('end_date'); // to fix (see also 'config')
//			$table->integer('duration');
//			$table->string('internal_id', 50);
//			$table->integer('unique_integer');
//			$table->string('usp', 255);
			$table->timestamps();


//			$table->datetime('departure-date'); // to fix (see also 'config')
//			$table->datetime('departure_date'); // to fix (see also 'config')
			// $table->string('priority', 50);
//			$table->string('title', 100);
//			$table->string('link', 255);
//			$table->text('description');
//			$table->string('accommodation_name', 100);
//			$table->string('accommodation_type', 50);
//			$table->string('location_description', 100);
//			$table->integer('stars');
//			$table->double('minimum_price');
//			$table->double('maximum_price');
//			$table->double('lowest_price');
//			$table->string('region_of_destination', 100);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('data');
	}

}
