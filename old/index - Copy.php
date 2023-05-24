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
		$finder = new DomXPath($dom);
		curl_close($ch);

		return $finder;
	}


	function scrapeProduct($finder, $key) {
		$productImageQuery = $finder->query("//html/body/div[2]/div/div[1]/div/div/div/div[3]/div/div[1]/div[2]/section/div/div[1]/div/div/div/div[1]/div/div/div[2]/div/div[1]/div/div/div/div/img");
		$productTitleQuery = $finder->query("//html/body/div[2]/div/div[1]/div/div/div/div[3]/div/div[1]/div[2]/section/div/div[2]/div/div[2]/h1/span[2]");
		$nutritionalInfoQuery = $finder->query("//html/body/div[2]/div/div[1]/div/div/div/div[3]/div/div[1]/div[8]/section/div/div[1]/div/div[2]/div/div/div/div/div[2]/div/div[2]");
		$generalInfoQuery = $finder->query("//html/body/div[2]/div/div[1]/div/div/div/div[3]/div/div[1]/div[8]/section/div/div[1]/div/div[2]/div/div/div/div/div[1]/div/div[2]");
		$productInfo = array();

		try {
			$productTitle = $productTitleQuery->item(0)?->textContent;
			$productImage = $productImageQuery->item(0)?->getAttribute('src');

			$nutritionalInfo = array();
			$generalInfo = array();

			echo 'Saving ' . $productTitle . '<br />';

			if($nutritionalInfoQuery->length) {
				for ( $i = 0; $i < $nutritionalInfoQuery->item(0)->childNodes->length; $i++ ) {
					$key = $nutritionalInfoQuery->item(0)->childNodes->item($i)->childNodes->item(0)->childNodes->item(0)->textContent;
					$value = $nutritionalInfoQuery->item(0)->childNodes->item($i)->childNodes->item(0)->childNodes->item(1)->textContent;

					array_push($nutritionalInfo, array(
						'key' => $key,
						'value' => $value,
					));
				}
			} else {
				echo " - nutritional information not found for " . $productTitle . "<br />";
			}

			if($generalInfoQuery->length) {
				for ( $i = 0; $i < $generalInfoQuery->item(0)->childNodes->length; $i++ ) {
					$key = $generalInfoQuery->item(0)->childNodes->item($i)->childNodes->item(0)->childNodes->item(0)->textContent;
					$value = $generalInfoQuery->item(0)->childNodes->item($i)->childNodes->item(0)->childNodes->item(1)->textContent;

					array_push($generalInfo, array(
						'key' => $key,
						'value' => $value,
					));
				}
			} else {
				echo " - general information not found for " . $productTitle . "<br />";				
			}

			array_push($productInfo, array(
				'productImage' => $productImage,
				'productTitle' => $productTitle,
				'nutritionalInfo' => $nutritionalInfo,
				'generalInfo' => $generalInfo

			));

		} catch(Exception $e) {
			echo "Section not found -> " . $e->getMessage() . "<br />";
		}

		echo '---------' . '<br />';

		return $productInfo;
	}

	// $finder = scrapeUrl('https://www.auchan.ro/salam-sinaia-agricola-300-g/p');
	// print_r(scrapeProduct($finder));

	$file = 'products.txt';
	// $product = json_encode(scrapeProduct($finder));
	// file_put_contents($file, $product, FILE_APPEND | LOCK_EX);

	$productUrls=simplexml_load_file('https://www.auchan.ro/sitemap/product-0.xml') or die("Error: Cannot create object");
	// Debug array
	// echo '<pre>'; print_r($productUrls); echo '</pre>';

	// Scrape the first 50 products
	// for ( $i = 0; $i < 100; $i++ ) {
	// 	$finder = scrapeUrl($productUrls->url[$i]->loc);
	// 	$product = json_encode(scrapeProduct($finder, $i));
	// 	file_put_contents($file, $product, FILE_APPEND | LOCK_EX);
	// 	sleep(3);
	// };

function parseKeyName($key) {
	return str_replace(" ", "_", strtolower(trim(preg_replace('~\([^()]*\)~', '', $key))));
}

$products = ["Tip Produs",
"Tip ambalaj",
"Kcal pe 100g sau 100ml",
"Termen de valabilitate",
"Conditii de pastrare",
"Tara de origine",
"Acizi grasi saturati (g sau ml)",
"Proteine (g sau ml)",
"Continut alcool (% vol)",
"Cantitate",
"Greutate",
"Culoare",
"Ingrediente",
"Grasimi (g sau ml)",
"Glucide (g sau ml)",
"KJ pe 100g sau 100ml",
"Zaharuri (g sau ml)",
"Sare (g sau ml)",
"Fibre (g sau ml)",
"Procent grasime (%)",
"Procent de grasime",
"Dieta si lifestyle",
"Sare (g sau ml)",
"Tip vin"];

foreach ($products as $value) {
	echo parseKeyName($value) . "<br/>";
};


	// foreach ($productUrls as $value) {
	// 	$finder = scrapeUrl($value->loc);
	// 	$product = json_encode(scrapeProduct($finder));
	// 	file_put_contents($file, $product, FILE_APPEND | LOCK_EX);
	// 	// echo $value->loc . "<br />";
	// 	sleep(3);
	// }

?>