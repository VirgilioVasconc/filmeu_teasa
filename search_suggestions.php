<?php
// Include config file
require_once "config.php";

// Get the search term from the GET request
$searchTerm = $_GET['query'];

// Get the search category from the GET request
$searchCat = $_GET['category'];

// Define an array to store the search suggestions
$suggestions = array();

$categories = array('film_title','film_release_year','country_name','director_name','studio_name','language_name','tag_name');

$category_exists=false;


foreach ($categories as $category) {
    if ( $searchCat==$category ) {
        $category_exists=true;
    }
}
if ( $category_exists ) {
    switch ( $searchCat ) {
        case "film_title":
            $query = "SELECT DISTINCT film_title FROM teasa_films WHERE film_title LIKE '%$searchTerm%'";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'title', 'text' => $row['film_title']);
            }
            break;
        case "film_release_year":
            // Search for release year
            $query = "SELECT DISTINCT film_release_year FROM teasa_films WHERE film_release_year LIKE '%$searchTerm%'";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'year', 'text' => $row['film_release_year']);
            }
            break;
        case "country_name":
            // Search for country of production
            $query = "SELECT DISTINCT country_name FROM teasa_countries INNER JOIN teasa_film_country WHERE teasa_countries.country_id=teasa_film_country.country_id HAVING country_name LIKE '%$searchTerm%';";
    
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'country', 'text' => $row['country']);
            }
            break;
        case "director_name":
            // Search for director's name
            $query = "SELECT DISTINCT director_name FROM teasa_directors WHERE director_name LIKE '%$searchTerm%'";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'director', 'text' => $row['director_name']);
            }
            break;
        case "studio_name":
            // Search for studio's name
            $query = "SELECT DISTINCT studio_name FROM teasa_studios WHERE studio_name LIKE '%$searchTerm%'";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'studio', 'text' => $row['studio_name']);
            }
            break;
        case "language_name":
            // Search for language
            $query = "SELECT DISTINCT language_name FROM teasa_languages INNER JOIN teasa_film_language WHERE teasa_languages.language_id=teasa_film_language.language_id HAVING language_name LIKE '%$searchTerm%';";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'language', 'text' => $row['language_name']);
            }
            break;
        case "tag_name":
            // Search for tags
            //$query = "SELECT DISTINCT tag_name FROM teasa_tags INNER JOIN teasa_film_tag WHERE teasa_tags.tag_id=teasa_film_tag.tag_id HAVING tag_name LIKE '%$searchTerm%';";
            $query = "SELECT DISTINCT tag_name FROM teasa_tags WHERE tag_name LIKE '%$searchTerm%';";

            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $suggestions[] = array('type' => 'tag', 'text' => $row['tag_name']);
            }
            break;
    }
}


// Return the suggestions as JSON
echo json_encode($suggestions);
?>
