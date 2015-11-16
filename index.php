<?php
// Report all errors except E_WARNING
error_reporting(E_ALL & ~E_WARNING);

$movie_names_imdb = array('titanic', 'avatar', 'san andreas', 'exodus', 'interstellar', 'gravity', 'australia', 'poseidon', 'king kong', 'water for elephants', 'gone girl');

// $movie_names_box_rotton_tomatoes = array('titanic', 'avatar', 'san_andreas', 'exodus_2009', 'interstellar', 'gravity', 'australia', 'poseidon', 'king_kong', 'water_for_elephants', 'gone_girl');

$movie_names_box_office_mojo = array('titanic', 'avatar', 'sanandreas', 'exodus', 'interstellar', 'gravity', 'australia', 'poseidon', 'kingkong05', 'waterforelephants', 'gonegirl');

$movie_names_box_rotton_tomatoes = array('titanic', 'avatar', 'san_andreas', 'exodus_2009', 'interstellar', 'gravity', 'australia', 'poseidon', 'king_kong', 'water_for_elephants', 'gone_girl');

for ($i = 0; $i < count($movie_names_box_office_mojo); $i++) {

	$movie_name = $movie_names_box_office_mojo[$i];
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

	echo "URL: " . $url;
	echo "\n";

	array_push($movie_data_array, $movie_name);

	foreach ($rows as $row) {
	  $cells = $row -> getElementsByTagName('td');
	  foreach ($cells as $cell) {
		
		if (strpos($cell->nodeValue,'Domestic Total Gross: ') !== false && strpos($cell->nodeValue,'Domestic Lifetime Gross: ') !== false) {
			$domestic_gross = explode("Domestic Lifetime Gross: ", $cell->nodeValue);
			$domestic_total_gross = str_replace('Domestic Total Gross: ', "", $domestic_gross[0]);
			$domestic_lifetime_gross = str_replace('Domestic Lifetime Gross: ', "", $domestic_gross[1]);
			
			array_push($movie_data_array, $domestic_total_gross);
			array_push($movie_data_array, $domestic_lifetime_gross);
		} else if (strpos($cell->nodeValue,'Domestic Total Gross: ') !== false) {
			$domestic_total_gross = str_replace('Domestic Total Gross: ', "", $cell->nodeValue);

			array_push($movie_data_array, $domestic_total_gross);
			array_push($movie_data_array, "N.A.");
		}
		
		if (strpos($cell->nodeValue,'Distributor: ') !== false) {
			array_push($movie_data_array, str_replace('Distributor: ', "", $cell->nodeValue));
		}
		if (strpos($cell->nodeValue,'Release Date: ') !== false) {
			array_push($movie_data_array, str_replace('Release Date: ', "", $cell->nodeValue));
		}
		if (strpos($cell->nodeValue,'Genre: ') !== false) {
			array_push($movie_data_array, str_replace('Genre: ', "", $cell->nodeValue));
		}
		if (strpos($cell->nodeValue,'Runtime: ') !== false) {
			array_push($movie_data_array, str_replace('Runtime: ', "", $cell->nodeValue));
		}
		if (strpos($cell->nodeValue,'MPAA Rating: ') !== false) {
			array_push($movie_data_array, str_replace('MPAA Rating: ', "", $cell->nodeValue));
		}
		if (strpos($cell->nodeValue,'Production Budget: ') !== false) {
			array_push($movie_data_array, str_replace('Production Budget: ', "", $cell->nodeValue));
		}

	  }
	}

	echo "Movie metadata fetching complete for :: " . $movie_name;
	echo "\n";

	echo "Fetching oscar data for :: " . $movie_name;
	echo "\n";

	$table = $xpath->query("//a[text()='Actors:']/ancestor::td[1]/following-sibling::td")->item(0);
	$rows = $table->getElementsByTagName("font");

	foreach ($rows as $row) {
		$actors_html = str_replace('<font size="2">', "", $row->ownerDocument->saveHTML($row));
		$actors_html = str_replace("</font>", "", $actors_html);
		$actors = explode("\n", $actors_html);
		$actor_data = "";
		foreach ($actors as $actor) {
			$actor_data .= strip_tags($actor) . ", ";
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
		$awards_data .= $award->nodeValue . ", ";
	}

	array_push($movie_data_array, $awards_data);

	echo "Oscar data complete for :: " . $movie_names_box_rotton_tomatoes[$i];
	echo "\n";

	echo "Fetching rotten tomatoes ratings :: " . $movie_names_box_rotton_tomatoes[$i];
	echo "\n";

	$rotten_tomatoes_url = 'http://www.rottentomatoes.com/m/' . $movie_names_box_rotton_tomatoes[$i];
	$html = file_get_contents($rotten_tomatoes_url);

	$xml = new DOMDocument();
	$xml->validateOnParse = true;
	$xml->loadHTML($html);

	$xpath = new DOMXPath($xml);
	$audience_score = $xpath->query("//div[contains(@class,'meter-value')]");
	foreach ($audience_score as $score) {
		array_push($movie_data_array, trim($score->nodeValue));
	}

	$avg_rating = $xpath->query("//div[contains(@class,'audience-info')]");
	foreach ($avg_rating as $rating) {
		$ratings = explode(":", $rating->nodeValue);
		array_push($movie_data_array, trim(str_replace('User Ratings', "", $ratings[1])));
		array_push($movie_data_array, trim($ratings[2]));
	}

	echo "Fetching rotten tomatoes ratings complete :: " . $movie_names_box_rotton_tomatoes[$i];
	echo "\n";
	
	echo "Fetching IMDB Data for :: " . $movie_names_box_rotton_tomatoes[$i];
	echo "\n";
	
	
	$json_url = "http://www.omdbapi.com/?t=" . urlencode($movie_names_imdb[$i]) . "&y=&plot=short&r=json";
	echo "JSON URL :: " . $json_url;
	echo "\n";
	$json = file_get_contents($json_url);
	$data = json_decode($json);
	
	array_push($movie_data_array, $data->{'Metascore'});
	array_push($movie_data_array, $data->{'imdbRating'});
	array_push($movie_data_array, $data->{'imdbVotes'});
	
	echo "Fetching IMDB Data complete.";
	echo "\n";
	echo "\n";

	if (file_exists($filename)) {
		$list = array (
			$movie_data_array
		);
		$fp = fopen($filename, 'a');
	} else {
		$list = array (
			array('movie_name', 'domestic_total_gross', 'domestic_lifetime_gross', 'distributor', 'release_date', 'genre', 'runtime', 'MPAA_Rating', 'production_budget', 'actors', 'oscars_nominations_and_wins', 'rottentomatoes_rating', 'rottentomatoes_avg_user_rating', 'rottentomatoes_user_ratings','imdb_metascore','imdb_rating','imdb_votes'),
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