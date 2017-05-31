<?php
/**
 * User: Edwin Heij
 * Date: 30-1-2015
 * Time: 21:50
 */

namespace Bahjaat\Daisycon\Repository;

use Bahjaat\Daisycon\Repository\DataImportInterface;
use LaravelDoctrine\ORM\Facades\Doctrine;
use League\Csv\Reader;
use Bahjaat\Daisycon\Models\Data;
use Config;
use Bahjaat\Daisycon\Helper\DaisyconHelper;
use function Stringy\create;
use Symfony\Component\Console\Output\ConsoleOutput;
use App\Entities as Entities;

//use League\Csv\Reader;

class LeagueCsvDataImport implements DataImportInterface
{

    /**
     * @var ConsoleOutputInterface
     */
    private $console;
	private $entityManager;

    public function __construct(ConsoleOutput $console)
    {

        $this->console = $console;
    }

    /**
     * @param $url
     * @param $program_id
     * @param $feed_id
     * @param $custom_categorie
     *
     * @throws \Exception
     */
    public function importData($url, $program_id, $feed_id, $custom_categorie)
    {
        $fileLocation = storage_path() . DIRECTORY_SEPARATOR . $program_id . '.' . $feed_id . '.csv';
	    $url = str_replace('#LOCALE_ID#', 1, $url);
	    $url .= '&type=' . Config::get('daisycon.feed_type');//Set CSV
//	    $url .= '&demo'; //Set demo

        $this->downloadAndSaveFeed($url, $fileLocation);

        $this->filterBestand($fileLocation);

        $fields_wanted_from_config = DaisyconHelper::getDatabaseFieldsToImport();

        $offset = 1; // initieel op 1 om header te ontlopen
        $batchAantal = 1000;

        $csv = Reader::createFromPath($fileLocation);
        $csv->setDelimiter(';');
        $csv->setEnclosure('"');

        $creationCount = 0;

	    $this->entityManager = \App::make('Doctrine\ORM\EntityManagerInterface');

        while (true) {
            // Flushing the QueryLog anders kan de import te veel geheugen gaan gebruiken
            \DB::connection()->flushQueryLog();

            $csv->setOffset($offset)->setLimit($batchAantal);

            $csvResults = $csv->fetchAll(function ($row) use ($fields_wanted_from_config, $program_id, $feed_id, $custom_categorie, &$creationCount) {
	            if (count($row) != count($fields_wanted_from_config)) return;

                try {
	                $inserted_array = array_merge(
                        array_combine(
                            $fields_wanted_from_config,
                            $row
                        ),
                        array(
                            'program_id' => $program_id,
                            'feed_id' => $feed_id,
                            'custom_category' => $custom_categorie
                        )
                    );

	                $accommodation = new Entities\Accommodation();
	                $accommodation->setId($inserted_array['id']);
	                $accommodation->setAccommodationName($inserted_array['accommodation_name']);
	                $accommodation->setName($inserted_array['name']);
	                $accommodation->setProductCount($inserted_array['product_count']);
	                $accommodation->setSku($inserted_array['sku']);
	                $accommodation->setInStock(intval($inserted_array['in_stock']));
	                $accommodation->setInStockAmount(intval($inserted_array['in_stock_amount']));
	                $accommodation->setKeywords($inserted_array['keywords']);
	                $accommodation->setPriority(intval($inserted_array['priority']));
	                $accommodation->setTermsConditions($inserted_array['terms_conditions']);
	                $accommodation->setTitle($inserted_array['title']);
	                $accommodation->setPriceShipping(floatval($inserted_array['price_shipping']));
	                $accommodation->setDeliveryTime(intval($inserted_array['delivery_time']));
	                $accommodation->setDeliveryDescription($inserted_array['delivery_description']);
	                $accommodation->setSize($inserted_array['size']);
	                $accommodation->setSizeDescription($inserted_array['size_description']);
	                $accommodation->setEan(intval($inserted_array['ean']));

	                $daisyCon = new Entities\DaisyCon();
	                $daisyCon->setLastModified($inserted_array['last_modified']);
	                $daisyCon->setDaisyconUniqueIdSince($inserted_array['daisycon_unique_id_since']);
	                $daisyCon->setDaisyconUniqueIdModified($inserted_array['daisycon_unique_id_modified']);
	                $daisyCon->setDaisyconUniqueId($inserted_array['daisycon_unique_id']);
	                $daisyCon->setPreviousDaisyconUniqueId($inserted_array['previous_daisycon_unique_id']);
	                $daisyCon->setDataHash($inserted_array['data_hash']);
	                $daisyCon->setInsertDate($inserted_array['insert_date']);
	                $daisyCon->setUpdateDate($inserted_array['update_date']);
	                $daisyCon->setDeleteDate($inserted_array['delete_date']);
	                $daisyCon->setProgramId($inserted_array['program_id']);
	                $daisyCon->setFeedId($inserted_array['feed_id']);
	                $daisyCon->setCustomCategory($inserted_array['custom_category']);
	                $daisyCon->setAccommodation($accommodation);

	                $destinationCountry = $this->entityManager
		                ->getRepository('App\Entities\Country')
		                ->createQueryBuilder('o')
		                ->select('o')
		                ->where('o.alpha2Code = :countryCode')
		                ->setParameter('countryCode', $inserted_array['destination_country'])
		                ->setMaxResults(1)
		                ->getQuery()
		                ->getSingleResult();

	                $region = $this->getRegion($inserted_array['destination_region']);
	                $destination = new Entities\Destination();
	                $destination->setDestinationZipcode($inserted_array['destination_zipcode']);
	                $destination->setDestinationCity($inserted_array['destination_city']);
	                $destination->setDestinationContinent($inserted_array['destination_continent']);
	                $destination->setDestinationCountry($destinationCountry);
	                $destination->setDestinationLanguage($inserted_array['destination_language']);
	                $destination->setDestinationLocationDescription($inserted_array['destination_location_description']);
	                $destination->setDestinationPort($inserted_array['destination_port']);
	                $destination->setDestinationCityLink($inserted_array['destination_city_link']);
	                $destination->setDestinationLatitude($inserted_array['destination_latitude']);
	                $destination->setDestinationLongitude($inserted_array['destination_longitude']);
	                $destination->setAccommodation($accommodation);
	                $destination->setRegion($region);

	                $accommodationImage = new Entities\AccommodationImage();
	                $accommodationImage->setImageSmall($inserted_array['image_small']);
	                $accommodationImage->setImageMedium($inserted_array['image_medium']);
	                $accommodationImage->setImageLarge($inserted_array['image_large']);
	                $accommodationImage->setAccommodation($accommodation);

	                $accommodationSpecs = new Entities\AccommodationSpecs();
	                $accommodationSpecs->setStatus($inserted_array['status']);
	                $accommodationSpecs->setAdditionalCosts(floatval($inserted_array['additional_costs']));
	                $accommodationSpecs->setBrandLogo($inserted_array['brand_logo']);
	                $accommodationSpecs->setCondition($inserted_array['condition']);
	                $accommodationSpecs->setDescription($inserted_array['description']);
	                $accommodationSpecs->setDescriptionShort($inserted_array['description_short']);
	                $accommodationSpecs->setAccommodationBathrooms(intval($inserted_array['accommodation_bathrooms']));
	                $accommodationSpecs->setAccommodationBedrooms(intval($inserted_array['accommodation_bedrooms']));
	                $accommodationSpecs->setAccommodationFloors(intval($inserted_array['accommodation_floors']));
	                $accommodationSpecs->setAccommodationRooms(intval($inserted_array['accommodation_rooms']));
	                $accommodationSpecs->setAccommodationToilets(intval($inserted_array['accommodation_toilets']));
	                $accommodationSpecs->setMaxNrPeople(intval($inserted_array['max_nr_people']));
	                $accommodationSpecs->setAccommodationType($inserted_array['accommodation_type']);
	                $accommodationSpecs->setAccommodationAddress($inserted_array['accommodation_address']);
	                $accommodationSpecs->setAccommodationChildFriendly(intval($inserted_array['accommodation_child_friendly']));
	                $accommodationSpecs->setHasLivingroom($inserted_array['has_livingroom'] == 'true');
	                $accommodationSpecs->setAccommodationOnHolidayPark(intval($inserted_array['accommodation_on_holiday_park']));
	                $accommodationSpecs->setAccommodationPetsAllowed(intval($inserted_array['accommodation_pets_allowed']));
	                $accommodationSpecs->setAccommodationSmokingAllowed(intval($inserted_array['accommodation_smoking_allowed']));
	                $accommodationSpecs->setAvailableFrom($inserted_array['available_from']);
	                $accommodationSpecs->setDistanceToBakery(intval($inserted_array['distance_to_bakery']));
	                $accommodationSpecs->setDistanceToBeach(intval($inserted_array['distance_to_beach']));
	                $accommodationSpecs->setDistanceToCitycenter(intval($inserted_array['distance_to_citycenter']));
	                $accommodationSpecs->setDistanceToGolfcourse(intval($inserted_array['distance_to_golfcourse']));
	                $accommodationSpecs->setDistanceToRestaurant(intval($inserted_array['distance_to_restaurant']));
	                $accommodationSpecs->setDistanceToShopping(intval($inserted_array['distance_to_shopping']));
	                $accommodationSpecs->setDistanceToSwimwater(intval($inserted_array['distance_to_swimwater']));
	                $accommodationSpecs->setDurationDays(intval($inserted_array['duration_days']));
	                $accommodationSpecs->setDurationNights(intval($inserted_array['duration_nights']));
	                $accommodationSpecs->setHasElectricity($inserted_array['has_electricity'] == 'true');
	                $accommodationSpecs->setHasBarbecue($inserted_array['has_barbecue'] == 'true');
	                $accommodationSpecs->setHasChildChair($inserted_array['has_child_chair'] == 'true');
	                $accommodationSpecs->setHasDishwasher($inserted_array['has_dishwasher'] == 'true');
	                $accommodationSpecs->setHasGarage($inserted_array['has_garage'] == 'true');
	                $accommodationSpecs->setHasGarden($inserted_array['has_garden'] == 'true');
	                $accommodationSpecs->setHasHeating($inserted_array['has_heating'] == 'true');
	                $accommodationSpecs->setHasInternet($inserted_array['has_internet'] == 'true');
	                $accommodationSpecs->setHasMicrowave($inserted_array['has_microwave'] == 'true');
	                $accommodationSpecs->setHasPlayground($inserted_array['has_playground'] == 'true');
	                $accommodationSpecs->setHasSauna($inserted_array['has_sauna'] == 'true');
	                $accommodationSpecs->setHasSwimmingpool($inserted_array['has_swimmingpool'] == 'true');
	                $accommodationSpecs->setHasTelephone($inserted_array['has_telephone'] == 'true');
	                $accommodationSpecs->setHasTelevision($inserted_array['has_television'] == 'true');
	                $accommodationSpecs->setHasWashingmachine($inserted_array['has_washingmachine'] == 'true');
	                $accommodationSpecs->setAccommodationLowestPrice(floatval($inserted_array['accommodation_lowest_price']));
	                $accommodationSpecs->setAccommodationLowestDate($inserted_array['accommodation_lowest_date']);
	                $accommodationSpecs->setAccommodationSqmFloors(intval($inserted_array['accommodation_sqm_floors']));
	                $accommodationSpecs->setModel($inserted_array['model']);
	                $accommodationSpecs->setGenderTarget($inserted_array['gender_target']);
	                $accommodationSpecs->setDepartureDate($inserted_array['departure_date']);
	                $accommodationSpecs->setTravelTripType($inserted_array['travel_trip_type']);
	                $accommodationSpecs->setHasAirco($inserted_array['has_airco'] == 'true');
	                $accommodationSpecs->setStarRating(intval($inserted_array['star_rating']));
	                $accommodationSpecs->setBrand($inserted_array['brand']);
	                $accommodationSpecs->setCategory($inserted_array['category']);
	                $accommodationSpecs->setCategoryPath($inserted_array['category_path']);
	                $accommodationSpecs->setLink($inserted_array['link']);
	                $accommodationSpecs->setPrice($inserted_array['price']);
	                $accommodationSpecs->setPriceOld(floatval($inserted_array['price_old']));
	                $accommodationSpecs->setTripHolidayType($inserted_array['trip_holiday_type']);
	                $accommodationSpecs->setAvailable(intval($inserted_array['available']));
	                $accommodationSpecs->setArrivalDate($inserted_array['arrival_date']);
	                $accommodationSpecs->setColorPrimary($inserted_array['color_primary']);
	                $accommodationSpecs->setCurrency($inserted_array['currency']);
	                $accommodationSpecs->setCurrencySymbol($inserted_array['currency_symbol']);
	                $accommodationSpecs->setAccommodation($accommodation);

	                //Foreign key references
					$accommodation->setImages($accommodationImage);
					$accommodation->setDestination($destination);
					$accommodation->setSpecs($accommodationSpecs);
					$accommodation->setDaisyCon($daisyCon);


	                if (!$this->entityManager->isOpen()) {
		                $this->console->writeln("Re-opening entityManager");
		                $this->entityManager = $this->entityManager->create(
			                $this->entityManager->getConnection(),
			                $this->entityManager->getConfiguration()
		                );
	                }

//					$this->entityManager->persist($currency);
//					$this->entityManager->persist($destinationCountry);
					$this->entityManager->persist($daisyCon);
					$this->entityManager->persist($accommodationSpecs);
					$this->entityManager->persist($destination);
					$this->entityManager->persist($accommodationImage);
					$this->entityManager->persist($accommodation);

//	                $this->console->writeln("flushing");

	                if($creationCount % 250 == 0) {
		                $this->console->writeln("flushing 250");
		                $this->entityManager->flush();
	                }

//                    Data::create(
//                        $inserted_array
//                    );

                    $creationCount++;
                } catch (Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                } catch (\ErrorException $e) {
                    echo $e->getMessage() . PHP_EOL;
                }

            });

			//Final flush
	        $this->entityManager->flush();

            $aantalResultaten = count($csvResults);
            $this->console->writeln("Total processed: " . $creationCount);

            $offset += $aantalResultaten;

            if ($aantalResultaten != $batchAantal) break; // forceer einde

//	        echo json_encode($csvResults);
        }

//        Data::where(function ($query) {
//            $query->whereTitle('title')
//                ->orWhere('title', 'like', '#%');
//        })->delete();
//
//        Data::whereTemp(null)->update(array('temp' => 1));

        \File::delete($fileLocation);
    }

    /**
     * Haal regels weg die beginnen met een hash (#)
     *
     * @param null $file
     */
    function filterBestand($file = null)
    {
        if (is_null($file)) return;
        $fileToRead = $file;
        $fileToWrite = $file . '.tmp';

        $reading = fopen($fileToRead, 'r');
        $writing = fopen($fileToWrite, 'w');
        while (!feof($reading)) {
            $line = fgets($reading);
            if (substr($line, 0, 1) != "#") {
                fputs($writing, $line);
            }
        }
        fclose($reading);
        fclose($writing);
        rename($fileToWrite, $fileToRead);
        return;
    }

    /**
     * Download remote bestand en sla deze op als csv file
     *
     * @param $url
     * @param $fileLocation
     *
     * @return mixed
     * @throws \Exception
     */
    function downloadAndSaveFeed($url, $fileLocation)
    {
        $file = fopen($fileLocation, 'w+');
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FILE => $file,
//            CURLOPT_TIMEOUT        => 120,
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)'
        ));

	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new \Exception('Curl error: ' . curl_error($curl));
        }

        return $response;
    }

	private function getRegion( $destination_region ) {
		//Check if destination region exists
//		$region =  $this->entityManager
//			->find('App\Entities\Region', $destination_region);
		$region = $this->entityManager
			->getRepository('App\Entities\Region')
			->createQueryBuilder('o')
			->select('o')
			->where('o.r_id = :region')
			->setParameter('region', $destination_region)
			->setMaxResults(1)
			->getQuery()
			->getResult();

		if(count($region) > 0) {
			return $region[0];
		}

		$region = new Entities\Region();
		$region->setRId($destination_region);

		$this->entityManager->persist($region);
		$this->entityManager->flush();

		return $region;
	}
}