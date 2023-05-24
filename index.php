<?php
	set_time_limit(60000);

	function scrapeUrl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");

		$dom = new DOMDocument();
		@$dom->loadHTML(curl_exec($ch));

		// Debug
		// echo $dom->c14N(false,true);
		//
		// foreach ($nutritionalInfoQuery as $e) {
		// 	echo $e->nodeValue.'<br>';
		// }
		$finder = new DomXPath($dom);
		curl_close($ch);

		return $finder;
	}


	function scrapeProduct($finder) {
		$productTitleQuery = $finder->query("//html/body/div[1]/main/div/div[2]/h1");
		$nutritionalInfoQuery = $finder->query("//html/body/div[1]/main/div/div[2]/div[1]/div[1]/div/div/div[2]/table/tbody/tr");
		$productInfo = array();

		try {
			$productTitle = $productTitleQuery->item(0)?->textContent;

			$nutritionalInfo = array();

			echo 'Saving ' . $productTitle . '<br />';

			if($nutritionalInfoQuery->length) {
				for ( $i = 0; $i < $nutritionalInfoQuery->length; $i++ ) {
					$key = $nutritionalInfoQuery->item($i)->childNodes->item(1)->textContent;
					$value = $nutritionalInfoQuery->item($i)->childNodes->item(3)?->textContent;

					array_push($nutritionalInfo, array(
						'key' => $key,
						'value' => $value,
					));
				}
			} else {
				echo " - nutritional information not found for " . $productTitle . "<br />";
			}

			array_push($productInfo, array(
				'productTitle' => $productTitle,
				'nutritionalInfo' => $nutritionalInfo,

			));

		} catch(Exception $e) {
			echo "Section not found -> " . $e->getMessage() . "<br />";
		}

		echo '---------' . '<br />';

		return $productInfo;
	}

	function scrapeLinks($finder) {
		$linksQuery = $finder->query("//html/body/div[1]/div[2]/div[2]/table/tbody/tr");

		$baseUrl = "https://eatntrack.ro";
		$links = "";

		foreach ($linksQuery as $e) {
			$scrapedLink = $e->childNodes->item(1)->childNodes->item(1)->childNodes->item(3)->childNodes->item(1)->getAttribute('href');
			$links = $links.$baseUrl.$scrapedLink."\n";
		}

		return $links;
	}

	// $finder = scrapeUrl('https://www.auchan.ro/salam-sinaia-agricola-300-g/p');
	// print_r(scrapeProduct($finder));

	// $product = json_encode(scrapeProduct($finder));
	// file_put_contents($file, $product, FILE_APPEND | LOCK_EX);

	// $productUrls=simplexml_load_file('https://www.auchan.ro/sitemap/product-0.xml') or die("Error: Cannot create object");
	// Debug array
	// echo '<pre>'; print_r($productUrls); echo '</pre>';

function scrapeLinksRun() {
	$file = 'branzeturi.txt';

	// Scrape the first 50 pages
	for ( $i = 0; $i < 116; $i++ ) {
		$finder = scrapeUrl('https://eatntrack.ro/caloriialimente?p='.$i.'&cat=Branzeturi');
		$links = scrapeLinks($finder)."\n";
		file_put_contents($file, $links, FILE_APPEND | LOCK_EX);
		sleep(3);
	};
}

function scrapeProductsRun() {
	$file = './links/legume.txt';
	$newFile = './products/legume.txt';

	$handle = fopen($file, "r");

	if ($file) {
		while (($line = fgets($handle)) !== false) {
			if('' != trim($line)) {
				$finder = scrapeUrl(trim($line));
				$product = json_encode(scrapeProduct($finder));

				file_put_contents($newFile, $product, FILE_APPEND | LOCK_EX);
				sleep(3);
			}
		}

		fclose($handle);
	}
}

// scrapeLinksRun();
scrapeProductsRun();

	// foreach ($productUrls as $value) {
	// 	$finder = scrapeUrl($value->loc);
	// 	$product = json_encode(scrapeProduct($finder));
	// 	file_put_contents($file, $product, FILE_APPEND | LOCK_EX);
	// 	// echo $value->loc . "<br />";
	// 	sleep(3);
	// }

	// $finder = scrapeUrl('https://eatntrack.ro/calorii/kefir-15-muller');
	// echo json_encode(scrapeProduct($finder));

	// $finder = scrapeUrl('https://eatntrack.ro/caloriialimente?p=121&cat=Lactate');
	// echo scrapeLinks($finder);
?>