<?php
/**
 * User: Edwin Heij
 * Date: 30-1-2015
 * Time: 21:50
 */

namespace Bahjaat\Daisycon\Repository;

use App\Entities as Entities;
use Bahjaat\Daisycon\Helper\DaisyconHelper;
use Config;
use Illuminate\Cache\Repository;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

//use League\Csv\Reader;

class LeagueCsvDataImport implements DataImportInterface
{
	private $totalRecords = 0;

	/**
	 * @var ConsoleOutputInterface
	 */
	private $console;

	private $entityManager;
	private $progressBar;

	public function __construct(ConsoleOutput $console)
	{
		$this->console = $console;
	}

	/**
	 * @param $url
	 * @param $program_id
	 * @param $feed_id
	 * @param $custom_category
	 *
	 * @throws \Exception
	 */
	public function importData($url, $program_id, $feed_id, $custom_category)
	{
		$fileLocation = storage_path() . DIRECTORY_SEPARATOR . $program_id . '.' . $feed_id . '.csv';
//		$url = str_replace('#LOCALE_ID#', 1, $url);
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

		$this->progressBar = new ProgressBar($this->console, $this->totalRecords);
		$this->progressBar->start();

		while (true) {
			// Flushing the QueryLog anders kan de import te veel geheugen gaan gebruiken
			\DB::connection()->flushQueryLog();

			$csv->setOffset($offset)->setLimit($batchAantal);

			$csvResults = $csv->fetchAll(function ($row) use ($fields_wanted_from_config, $program_id, $feed_id, $custom_category, &$creationCount) {
//	            if (count($row) != count($fields_wanted_from_config)) return;

//				$this->console->writeln(json_encode($row));
//				$this->console->writeln(json_encode($fields_wanted_from_config));

				try {
					$inserted_array = array_merge(
						array_combine(
							$fields_wanted_from_config,
							$row
						),
						array(
							'program_id' => $program_id,
							'feed_id' => $feed_id,
							'custom_category' => $custom_category
						)
					);

					$accommodation = new Entities\Accommodation();
					$accommodation->setId($inserted_array['id']);
					$accommodation->setAccommodationName($inserted_array['accommodation_name']);
					$accommodation->setName($inserted_array['name']);
					$accommodation->setProductCount($inserted_array['product_count']);
					$accommodation->setSku($inserted_array['sku']);
					$accommodation->setTitle($inserted_array['title']);

					$daisyCon = new Entities\DaisyCon();
					$daisyCon->setLastModified($inserted_array['last_modified']);
					$daisyCon->setDaisyconUniqueIdSince($inserted_array['daisycon_unique_id_since']);
					$daisyCon->setDaisyconUniqueIdModified($inserted_array['daisycon_unique_id_modified']);
					$daisyCon->setDaisyconUniqueId($inserted_array['daisycon_unique_id']);
					$daisyCon->setPreviousDaisyconUniqueId($inserted_array['previous_daisycon_unique_id']);
					$daisyCon->setDataHash($inserted_array['data_hash']);
					$daisyCon->setInsertDate($inserted_array['insert_date']);
					$daisyCon->setUpdateDate($inserted_array['update_date']);
					$daisyCon->setProgramId($inserted_array['program_id']);
					$daisyCon->setFeedId($inserted_array['feed_id']);
					$daisyCon->setCustomCategory($inserted_array['custom_category']);
					$daisyCon->setAccommodation($accommodation);

					$destinationCountry = $this->getCountry($inserted_array['destination_country']);

					$region = $this->getRegion($inserted_array['destination_region']);
					$destination = new Entities\Destination();
					$destination->setDestinationZipcode($inserted_array['destination_zipcode']);
					$destination->setDestinationCity($inserted_array['destination_city']);
					$destination->setDestinationCountry($destinationCountry);
					$destination->setDestinationLocationDescription($inserted_array['destination_location_description']);
					$destination->setDestinationLatitude($inserted_array['destination_latitude']);
					$destination->setDestinationLongitude($inserted_array['destination_longitude']);
					$destination->setAccommodation($accommodation);
					$destination->setRegion($region);

					$accommodationImage = new Entities\AccommodationImage();
					$this->setAccommodationImages($accommodationImage, $inserted_array);
					$accommodationImage->setAccommodation($accommodation);

					$accommodationSpecs = new Entities\AccommodationSpecs();
					$accommodationSpecs->setStatus($inserted_array['status']);
					$accommodationSpecs->setDescription($inserted_array['description']);
					$accommodationSpecs->setDescriptionShort($inserted_array['description_short']);
					$accommodationSpecs->setAccommodationBedrooms(intval($inserted_array['accommodation_bedrooms']));
					$accommodationSpecs->setAccommodationRooms(intval($inserted_array['accommodation_rooms']));
					$accommodationSpecs->setMaxNrPeople(intval($inserted_array['max_nr_people']));
					$accommodationSpecs->setAccommodationType($inserted_array['accommodation_type']);
					$accommodationSpecs->setAccommodationPetsAllowed(intval($inserted_array['accommodation_pets_allowed']));
					$accommodationSpecs->setHasDishwasher($inserted_array['has_dishwasher'] == 'true');
					$accommodationSpecs->setHasSauna($inserted_array['has_sauna'] == 'true');
					$accommodationSpecs->setHasSwimmingpool($inserted_array['has_swimmingpool'] == 'true');
					$accommodationSpecs->setHasTelevision($inserted_array['has_television'] == 'true');
					$accommodationSpecs->setHasWashingmachine($inserted_array['has_washingmachine'] == 'true');
					$accommodationSpecs->setHasAirco($inserted_array['has_airco'] == 'true');
					$accommodationSpecs->setLink($inserted_array['link']);
					$accommodationSpecs->setPrice($inserted_array['price']);
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

					$this->entityManager->persist($daisyCon);
					$this->entityManager->persist($accommodationSpecs);
					$this->entityManager->persist($destination);
					$this->entityManager->persist($accommodationImage);
					$this->entityManager->persist($accommodation);

					if($creationCount % 250 == 0) {
						$this->entityManager->flush();
						$this->entityManager->clear();

						$this->progressBar->advance($creationCount);
					}

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

			$offset += $aantalResultaten;

			if ($aantalResultaten != $batchAantal) break; // forceer einde
		}

		$this->progressBar->finish();
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

		$records = 0;
		$f = fopen($file, 'rt');
		while (($row = fgetcsv($f, 4096, ';')) !== false) {
			++$records;
		}
		fclose($f);

		$this->totalRecords = $records;

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

	private function getRegion( $destinationRegion ) {
		$region = $this->entityManager
			->getRepository('App\Entities\Region')
			->findOneOrFalseByRId($destinationRegion);

		if($region !== false) {
			return $region;
		}

		$region = new Entities\Region();
		$region->setRId($destinationRegion);

		$this->entityManager->persist($region);
		$this->entityManager->flush();

		return $region;
	}

	private function getCountry( $destinationCountry ) {
		return $this->entityManager
			->getRepository('App\Entities\Country')
			->findOneByAlpha2Code($destinationCountry);
	}

	private function setAccommodationImages( Entities\AccommodationImage &$accommodationImage, $inserted_array ) {
		$imagePath = $inserted_array['image_small'];
		$imagePaths = array(
			'small' => $imagePath,
			'medium' => str_replace('medium', 'large', $imagePath),
			'large' => str_replace('medium', 'original', $imagePath)
		);

		$accommodationImage->setImageSmall($imagePaths['small']);
		$accommodationImage->setImageMedium($imagePaths['medium']);
		$accommodationImage->setImageLarge($imagePaths['large']);
	}
}