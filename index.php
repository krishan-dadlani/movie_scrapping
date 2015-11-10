<?php
// Report all errors except E_WARNING
error_reporting(E_ALL & ~E_WARNING);

$movie_names = array('titanic', 'avatar', 'sanandreas', 'exodus', 'interstellar', 'gravity', 'australia', 'poseidon', 'kingkong05', 'waterforelephants', 'gonegirl');
foreach ($movie_names as $movie_name) {
	// $movie_name = "titanic";
$movie_data_array = array();
$filename = 'movie-data.csv';


$url = 'http://www.boxofficemojo.com/movies/?page=main&id=' . $movie_name . '.htm';
$html = file_get_contents($url);

$xml = new DOMDocument();
$xml->validateOnParse = true;
$xml->loadHTML($html);

$xpath = new DOMXPath($xml);
$table = $xpath->query("//table[@bgcolor='#dcdcdc']")->item(0);

$rows = $table->getElementsByTagName("tr");

array_push($movie_data_array, $movie_name);

foreach ($rows as $row) {
  $cells = $row -> getElementsByTagName('td');
  foreach ($cells as $cell) {
	
	if (strpos($cell->nodeValue,'Domestic Total Gross: ') !== false && strpos($cell->nodeValue,'Domestic Lifetime Gross: ') !== false) {
		$domestic_gross = explode("Domestic Lifetime Gross: ", $cell->nodeValue);
		$domestic_total_gross = str_replace('Domestic Total Gross: ', "", $domestic_gross[0]);
		$domestic_lifetime_gross = str_replace('Domestic Lifetime Gross: ', "", $domestic_gross[1]);
		
		echo $domestic_total_gross;
		echo "<br />";
		echo $domestic_lifetime_gross;
		array_push($movie_data_array, $domestic_total_gross);
		array_push($movie_data_array, $domestic_lifetime_gross);
	} else if (strpos($cell->nodeValue,'Domestic Total Gross: ') !== false) {
		$domestic_total_gross = str_replace('Domestic Total Gross: ', "", $cell->nodeValue);
		
		echo $domestic_total_gross;
		echo "<br />";
		array_push($movie_data_array, $domestic_total_gross);
		array_push($movie_data_array, "N.A.");
	}
	
	if (strpos($cell->nodeValue,'Distributor: ') !== false) {
		echo str_replace('Distributor: ', "", $cell->nodeValue);
		array_push($movie_data_array, str_replace('Distributor: ', "", $cell->nodeValue));
	}
	if (strpos($cell->nodeValue,'Release Date: ') !== false) {
		echo str_replace('Release Date: ', "", $cell->nodeValue);
		array_push($movie_data_array, str_replace('Release Date: ', "", $cell->nodeValue));
	}
	if (strpos($cell->nodeValue,'Genre: ') !== false) {
		echo str_replace('Genre: ', "", $cell->nodeValue);
		array_push($movie_data_array, str_replace('Genre: ', "", $cell->nodeValue));
	}
	if (strpos($cell->nodeValue,'Runtime: ') !== false) {
		echo str_replace('Runtime: ', "", $cell->nodeValue);
		array_push($movie_data_array, str_replace('Runtime: ', "", $cell->nodeValue));
	}
	if (strpos($cell->nodeValue,'MPAA Rating: ') !== false) {
		echo str_replace('MPAA Rating: ', "", $cell->nodeValue);
		array_push($movie_data_array, str_replace('MPAA Rating: ', "", $cell->nodeValue));
	}
	if (strpos($cell->nodeValue,'Production Budget: ') !== false) {
		echo str_replace('Production Budget: ', "", $cell->nodeValue);
		array_push($movie_data_array, str_replace('Production Budget: ', "", $cell->nodeValue));
	}
	
	echo "<br />";
  }
}


$table = $xpath->query("//a[text()='Actors:']/ancestor::td[1]/following-sibling::td")->item(0);
$rows = $table->getElementsByTagName("font");

foreach ($rows as $row) {
	$actors_html = str_replace('<font size="2">', "", $row->ownerDocument->saveHTML($row));
	$actors_html = str_replace("</font>", "", $actors_html);
	$actors = explode("<br>", $actors_html);
	$actor_data = "";
	foreach ($actors as $actor) {
		echo strip_tags($actor);
		$actor_data .= strip_tags($actor) . ", ";
		echo "<br />";
	}
	
	array_push($movie_data_array, $actor_data);
}

$url = 'http://www.boxofficemojo.com/oscar/movies/?id=' . $movie_name . '.htm';
$html = file_get_contents($url);

$xml = new DOMDocument();
$xml->validateOnParse = true;
$xml->loadHTML($html);

$xpath = new DOMXPath($xml);
$awards = $xpath->query("//a[contains(@href,'/oscar/chart') and contains(@href ,'catid')]");

$awards_data = "";
foreach ($awards as $award) {
	echo $award->nodeValue;
	$awards_data .= $award->nodeValue . ", ";
	echo "<br />";
}


array_push($movie_data_array, $awards_data);

if (file_exists($filename)) {
    $list = array (
		// array('movie_name', 'domestic_total_gross', 'domestic_lifetime_gross', 'distributor', 'release_date', 'genre', 'runtime', 'MPAA_Rating', 'production_budget', 'actors', 'oscars_nominations_and_wins'),
		$movie_data_array
	);
	$fp = fopen($filename, 'a');
} else {
    $list = array (
		array('movie_name', 'domestic_total_gross', 'domestic_lifetime_gross', 'distributor', 'release_date', 'genre', 'runtime', 'MPAA_Rating', 'production_budget', 'actors', 'oscars_nominations_and_wins'),
		$movie_data_array
	);
$fp = fopen($filename, 'w');
}

foreach ($list as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);
}









?>