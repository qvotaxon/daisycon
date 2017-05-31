<?php

// Composer: "fzaninotto/faker": "v1.3.0"
// use Faker\Factory as Faker;

use Bahjaat\Daisycon\Models\Countrycode;
use Illuminate\Database\Seeder;

use Prewk\XmlStringStreamer;
use Prewk\XmlStringStreamer\Parser;
use Prewk\XmlStringStreamer\Stream;

class CountrycodesTableSeeder extends Seeder {

	private $entityManager;

	public function run()
	{

		$this->entityManager = \App::make('Doctrine\ORM\EntityManagerInterface');

//		Countrycode::truncate();

		$CHUNK_SIZE = 1024;
		$streamProvider = new Stream\File(dirname(__FILE__) . "/countrycodes.xml", $CHUNK_SIZE);

		$config = array(
		    "uniqueNode" => "row"
		);

		$parser = new Parser\UniqueNode($config);
        $streamer = new XmlStringStreamer($parser, $streamProvider);

		while ($node = $streamer->getNode()) {
			$country = new App\Entities\Country();
		    $simpleXmlNode = simplexml_load_string($node);
			$country->setAlpha2Code($simpleXmlNode->field[0]);
			$country->setName($simpleXmlNode->field[1]);

			$this->entityManager->persist($country);
		}

		$this->entityManager->flush();

		$this->command->info('Countrycode table seeded!');
	}

}