<?php
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);
// if the search query is empty, redirect to the start page
if ( empty($_GET['id']) ) {
	header('Location: index.php');
	exit;
} else {
    $film_id = test_input($_GET['id']);
}

// Include config file
require_once "config.php";

// 1 - IF THERE IS A $_POST, LET'S DO THE INSERTS AND UPDATES
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if ($_POST){
    //elements to update on the teasa_films
    $film_title = test_input($_POST['film_title']);
    $film_alt_title = test_input($_POST['film_alt_title']);
    
    $film_length_min = test_input($_POST['film_length_min']);
    $film_length_sec = test_input($_POST['film_length_sec']);
    $film_lenght = (60*$film_length_min)+$film_length_sec;

    $film_release_year = test_input($_POST['film_release_year']);
    $film_description = test_input($_POST['film_description']);
    $film_availability = test_input($_POST['film_availability']);

    $sql="UPDATE teasa_films SET film_title ='$film_title', film_alt_title ='$film_alt_title', film_duration_sec = $film_lenght, film_release_year = $film_release_year, film_description ='$film_description', film_availability ='$film_availability' WHERE film_id = $film_id";
    $conn->query($sql);
    //$updateStmt = $conn->prepare("UPDATE teasa_films SET film_title =:film_title, film_alt_title =:film_alt_title, film_duration_sec = :film_lenght, film_release_year = :film_release_year, film_description =:film_description, film_availability = :film_availability WHERE film_id = :film_id");
    ////$updateStmt = $conn->prepare("UPDATE teasa_films SET film_title=:film_title, film_alt_title=:film_alt_title, film_duration_sec=:film_duration_sec, film_release_year=:film_release_year, film_description=:film_description, film_availability=:film_availability WHERE film_id=:film_id");

    //$updateStmt->bindParam(":film_title", $film_title, PDO::PARAM_STR);
    //$updateStmt->bindParam(":film_alt_title", $film_alt_title, PDO::PARAM_STR);
    //$updateStmt->bindParam(":film_duration_sec", $film_lenght, PDO::PARAM_INT);
    //$updateStmt->bindParam(":film_release_year", $film_release_year, PDO::PARAM_INT);
    //$updateStmt->bindParam(":film_description", $film_description, PDO::PARAM_STR);
    //$updateStmt->bindParam(":film_availability", $film_availability, PDO::PARAM_STR);
    //$updateStmt->bindParam(":film_id", $film_id, PDO::PARAM_INT);
    //$updateStmt->execute();

    //image
    $film_thumbnail_image = $_POST['cropped_image'];
    $image_array_1 = explode(";", $film_thumbnail_image);
    $image_array_2 = explode(",", $image_array_1[1]);
    $data = base64_decode($image_array_2[1]);

    //print_r($_POST);
    //echo $film_thumbnail_image;
    file_put_contents("img/films/" . $film_id . "_tn.png", $data);
    if ($_FILES["film_thumbnail_image"]["name"]!=""){
        imagepng(imagecreatefromstring(file_get_contents($_FILES["film_thumbnail_image"]["tmp_name"])), "img/films/" . $film_id . ".png");
    }
    


    //existing tags to insert in the teasa_film_tag table
    // Delete old film-tag relationships
    //$deleteStmt = $conn->prepare("DELETE FROM teasa_film_tag WHERE film_id = :film_id");
    //$deleteStmt->bindParam(':film_id', $film_id);
    //$deleteStmt->execute();
    $sql="DELETE FROM teasa_film_tag WHERE film_id = $film_id";
    $conn->query($sql);


    
    $tag = $_POST['tag'];
    
    if ( !empty($tag) ){
        if ( test_input($tag[0]) != -1 && test_input($tag[0]) != "") {
            $sql = "INSERT INTO teasa_film_tag(film_id, tag_id) VALUES";
            for ( $i=0; $i<(sizeof($tag)); $i++ ) {
                $sql .= "($film_id, " . test_input($tag[$i]) . ")";
                if ( $i < ( sizeof($tag)-1) ){
                    $sql .= ",";
                }
            }
            //echo $sql;
            $conn->query($sql);
        }
    }
    //new tags created by the user
    $new_tag = $_POST['new_tag'];
    $new_tag_category = $_POST['new_tag_category'];
    $inserted_tags = array();
    if ( !empty($new_tag) ){
        for ( $i=0; $i<(sizeof($new_tag) ); $i++) {
            $conn->query("INSERT INTO teasa_tags(tag_name, tag_category_id) VALUES('" . test_input($new_tag[$i]) . "', " . test_input($new_tag_category[$i]) . ")");
            array_push($inserted_tags, $conn->insert_id );
        }
        $sql = "INSERT INTO teasa_film_tag(film_id, tag_id) VALUES";
        for ( $i=0; $i<(sizeof($inserted_tags) ); $i++) {
            $sql .= "($film_id, " . test_input($inserted_tags[$i]) . ")";
            if ( $i < ( sizeof($inserted_tags)-1) ) {
                $sql .= ",";
            }
        }
        
        $conn->query($sql);
    }
    
    
    //existing directors to insert in the teasa_film_director table
    // Delete old film-tag relationships
    //$deleteStmt = $conn->prepare("DELETE FROM teasa_film_director WHERE film_id = :film_id");
    //$deleteStmt->bindParam(':film_id', $film_id);
    //$deleteStmt->execute();
    $sql="DELETE FROM teasa_film_director WHERE film_id = $film_id";
    $conn->query($sql);
    
    $director = $_POST['director'];
    if ( !empty($director) && $director[0]!="new" ){
        $sql = "INSERT INTO teasa_film_director(film_id, director_id) VALUES";
        for ( $i=0; $i<(sizeof($director)); $i++ ) {
            $sql .= "($film_id, " . test_input($director[$i]) . ")";
            if ( $i < ( sizeof($director)-1) ){
                $sql .= ",";
            }
        }
        $conn->query($sql);
    }

    //new directors created by the user
    $new_director_name = $_POST['new_director_name'];
    $new_director_gender = $_POST['new_director_gender'];
    $new_director_country = $_POST['new_director_country'];
    
    $inserted_directors = array();
    if ( !empty($new_director_name) ){
        for ( $i=0; $i<(sizeof($new_director_name) ); $i++) {
            $conn->query("INSERT INTO teasa_directors(director_name, director_gender) VALUES('" . test_input($new_director_name[$i]) . "', " . test_input($new_director_gender[$i]) . ")");
            array_push($inserted_directors, $conn->insert_id );
        }
        $sql_film_director = "INSERT INTO teasa_film_director(film_id, director_id) VALUES";
        $sql_director_country = "INSERT INTO teasa_director_country(director_id, country_id) VALUES";
        for ( $i=0; $i<(sizeof($inserted_directors) ); $i++) {
            $sql_film_director .= "($film_id, " . test_input($inserted_directors[$i]) . ")";
            $sql_director_country .= "(" . test_input($inserted_directors[$i]) . "," . test_input($new_director_country[$i]) . ")";
            if ( $i < ( sizeof($inserted_directors)-1) ) {
                $sql_film_director .= ",";
                $sql_director_country .= ",";
            }
        }
        $conn->query($sql_film_director);
        $conn->query($sql_director_country);
    }

    

    //existing countries to insert in the teasa_film_country table
    // Delete old film-country relationships
    //$deleteStmt = $conn->prepare("DELETE FROM teasa_film_country WHERE film_id = :film_id");
    //$deleteStmt->bindParam(':film_id', $film_id);
    //$deleteStmt->execute();
    $sql="DELETE FROM teasa_film_country WHERE film_id = $film_id";
    $conn->query($sql);
    
    $country = $_POST['country'];
    if ( !empty($country) ){
        $sql = "INSERT INTO teasa_film_country(film_id, country_id) VALUES";
        for ( $i=0; $i<(sizeof($country)); $i++ ) {
            $sql .= "($film_id, " . test_input($country[$i]) . ")";
            if ( $i < ( sizeof($country)-1) ){
                $sql .= ",";
            }
        }
        $conn->query($sql);
    }

    //existing languages to insert in the teasa_film_language table
    //$deleteStmt = $conn->prepare("DELETE FROM teasa_film_language WHERE film_id = :film_id");
    //$deleteStmt->bindParam(':film_id', $film_id);
    //$deleteStmt->execute();
    $sql="DELETE FROM teasa_film_language WHERE film_id = $film_id";
    $conn->query($sql);
    
    $language = $_POST['language'];
    if ( !empty($language) ){
        $sql = "INSERT INTO teasa_film_language(film_id, language_id) VALUES";
        for ( $i=0; $i<(sizeof($language)); $i++ ) {
            $sql .= "($film_id, " . test_input($language[$i]) . ")";
            if ( $i < ( sizeof($language)-1) ){
                $sql .= ",";
            }
        }
        $conn->query($sql);
    }

    //existing studios to insert in the teasa_film_studio table
    // Delete old film-studio relationships
    //$deleteStmt = $conn->prepare("DELETE FROM teasa_film_studio WHERE film_id = :film_id");
    //$deleteStmt->bindParam(':film_id', $film_id);
    //$deleteStmt->execute();

    $sql="DELETE FROM teasa_film_studio WHERE film_id = $film_id";
    $conn->query($sql);
    
    $studio = $_POST['studio'];
    if ( !empty($studio) ){
        $sql = "INSERT INTO teasa_film_studio(film_id, studio_id) VALUES";
        for ( $i=0; $i<(sizeof($studio)); $i++ ) {
            $sql .= "($film_id, " . test_input($studio[$i]) . ")";
            if ( $i < ( sizeof($studio)-1) ){
                $sql .= ",";
            }
        }
        //echo $sql;
        $conn->query($sql);
    }

    //new studio created by the user
    $new_studio_name = $_POST['new_studio_name'];
    $new_studio_founded = $_POST['new_studio_founded'];
    $new_studio_closed = $_POST['new_studio_closed'];
    $new_studio_country = $_POST['new_studio_country'];
    $new_studio_link = $_POST['new_studio_link'];
    
    $inserted_studios = array();
    if ( !empty($new_studio_name) ){
        for ( $i=0; $i<(sizeof($new_studio_name) ); $i++) {
            $conn->query("INSERT INTO teasa_studios(studio_name, studio_founded, studio_closed, studio_link) VALUES('" . test_input($new_studio_name[$i]) . "', " . test_input($new_studio_founded[$i]) . "," . test_input($new_studio_closed[$i]) . ",'" . test_input($new_studio_link[$i]) . "')");
            array_push($inserted_studios, $conn->insert_id );
        }
        $sql_film_studio = "INSERT INTO teasa_film_studio(film_id, studio_id) VALUES";
        $sql_studio_country = "INSERT INTO teasa_studio_country(studio_id, country_id) VALUES";
        for ( $i=0; $i<(sizeof($inserted_studios) ); $i++) {
            $sql_film_studio .= "($film_id, " . test_input($inserted_studios[$i]) . ")";
            $sql_studio_country .= "(" . test_input($inserted_studios[$i]) . "," . test_input($new_studio_country[$i]) . ")";
            if ( $i < ( sizeof($inserted_studios)-1) ) {
                $sql_film_studio .= ",";
                $sql_studio_country .= ",";
            }
        }
        $conn->query($sql_film_studio);
        $conn->query($sql_studio_country);
    }
}

// 2 - IF THE USER WANTS TO DELETE THE IMAGE
if ( $_GET["deleteImage"]=="true" ) {
    unlink("img/films/" . $film_id . "_tn.png");
    unlink("img/films/" . $film_id . ".png");
}


// 3 - NOW TO CREATE THE PAGE

//get unique countries of production
//1	Unknown 
$query = "SELECT country_id, country_name FROM teasa_countries WHERE country_id>1 ORDER BY country_name;";
$result = $conn->query($query);
$all_countries = $result->fetch_all();
$countries_json = json_encode($countries);

//get unique directors' names
//1	Unknown
$query = "SELECT DISTINCT director_id, director_name FROM teasa_directors WHERE director_id>1 AND entry_disabled=0 ORDER BY director_name;";
$result = $conn->query($query);
$all_directors = $result->fetch_all();
$directors_json = json_encode($directors);

//get unique studios' names
//1	Unknown
$query = "SELECT DISTINCT studio_id, studio_name FROM teasa_studios WHERE studio_id>1 AND entry_disabled=0 ORDER BY studio_name;";
$result = $conn->query($query);
$all_studios = $result->fetch_all();
$studios_json = json_encode($studios);

//get unique languages
//1	Unknown
//2	Silent  No dialogs
$query = "SELECT DISTINCT language_id, language_name FROM teasa_languages ORDER BY language_id;";
$result = $conn->query($query);
$all_languages = $result->fetch_all();
$languages_json = json_encode($languages);

//get unique tags
$query = "SELECT DISTINCT t.*, c.tag_category_name FROM teasa_tags t LEFT JOIN teasa_tagcategory c ON t.tag_category_id = c.tag_category_id WHERE t.entry_disabled=0 ORDER BY t.tag_category_id, t.tag_name;";
$result = $conn->query($query);
$all_tags = $result->fetch_all();

//get tag categories
$query = "SELECT tag_category_id, tag_category_name FROM teasa_tagcategory WHERE entry_disabled=0 ORDER BY tag_category_id;";
$result = $conn->query($query);
$all_tag_categories = $result->fetch_all();

//get genders
$query = "SELECT gender_id, gender_name FROM teasa_genders WHERE entry_disabled=0 ORDER BY gender_id;";
$result = $conn->query($query);
$all_genders = $result->fetch_all();




?>
<!DOCTYPE html>
<html>
<!DOCTYPE html>
<html>
<?php require_once "head.php"; ?>
<body>
	<?php require_once "header.php"; ?>
	<main>
	    <div class="main">
            <button class="back" onclick="history.back();"><i class="fa fa-arrow-left"> </i> Back</button>
			<h1 class="mb-4">Film details</h1>
			<?php
				// Get search parameters from the URL. This is only for searching by film name
				$film_id = filter_var($_GET['id'], FILTER_SANITIZE_STRING);

				if (!empty($film_id && is_numeric($film_id) ) ) {

					$sql = "SELECT f.*, ";
					$sql .="GROUP_CONCAT(DISTINCT d.director_id ORDER BY d.director_name ASC SEPARATOR ',') AS director_ids, ";
                    $sql .="GROUP_CONCAT(DISTINCT d.director_name ORDER BY d.director_name ASC SEPARATOR ',') AS directors, ";
                    $sql .="GROUP_CONCAT(DISTINCT s.studio_id ORDER BY s.studio_name ASC SEPARATOR ',') AS studio_ids, ";
					$sql .="GROUP_CONCAT(DISTINCT s.studio_name ORDER BY s.studio_name ASC SEPARATOR ',') AS studios, ";
					$sql .="GROUP_CONCAT(DISTINCT l.language_id ORDER BY l.language_name ASC SEPARATOR ',') AS language_ids, ";
                    $sql .="GROUP_CONCAT(DISTINCT l.language_name ORDER BY l.language_name ASC SEPARATOR ',') AS languages, ";
                    $sql .="GROUP_CONCAT(DISTINCT c.country_id ORDER BY c.country_name ASC SEPARATOR ',') AS country_ids, ";
                    $sql .="GROUP_CONCAT(DISTINCT c.country_name ORDER BY c.country_name ASC SEPARATOR ',') AS countries, ";
					$sql .="GROUP_CONCAT(DISTINCT t.tag_id ORDER BY t.tag_name ASC SEPARATOR ',') AS tag_ids, ";
                    $sql .="GROUP_CONCAT(DISTINCT t.tag_name ORDER BY t.tag_name ASC SEPARATOR ',') AS tags ";
					$sql .="FROM teasa_films f ";
					$sql .="LEFT JOIN teasa_film_director fd ON f.film_id = fd.film_id ";
					$sql .="LEFT JOIN teasa_directors d ON fd.director_id = d.director_id ";
					$sql .="LEFT JOIN teasa_film_country fc ON f.film_id = fc.film_id ";
					$sql .="LEFT JOIN teasa_countries c ON fc.country_id = c.country_id ";
					$sql .="LEFT JOIN teasa_film_language fl ON f.film_id = fl.film_id ";
					$sql .="LEFT JOIN teasa_languages l ON fl.language_id = l.language_id ";
					$sql .="LEFT JOIN teasa_film_studio fs ON f.film_id = fs.film_id ";
					$sql .="LEFT JOIN teasa_studios s ON fs.studio_id = s.studio_id ";
					$sql .="LEFT JOIN teasa_film_tag ft ON f.film_id = ft.film_id ";
					$sql .="LEFT JOIN teasa_tags t ON ft.tag_id = t.tag_id ";
					$sql .="WHERE f.film_id= ? AND f.entry_disabled=0 ";
					$sql .="GROUP BY f.film_id;";

					//echo $sql;
					
					// assume $conn is your database connection object
					$stmt = $conn->prepare($sql);
					$stmt->bind_param("i", $film_id); // bind $name to the prepared statement
					$stmt->execute();
					$result = $stmt->get_result();

				}
				
				// get the number of rows returned by the SQL query
				$num_rows = mysqli_num_rows($result);

				$row = mysqli_fetch_assoc($result);

				//echo "<h3>Your search criteria: $title_criteria</h3>";

				// if no rows were returned by the SQL query, display a message
				if ($num_rows == 0) {
				?>
				  <p>Your search criteria did not have results. Please <a href='index.php'>try again with different parameters</a>.</p>
				<?php
				} else {
					// loop through each row returned by the SQL query and display it in a table row
						?>
						
						<div class="search_result_item">
                            <form class="details" method="POST" id="film_details" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>?id=<?php echo $film_id;?>">

                            <!-- TITLE -->
                            <div class='item_field clearfix'>
                                <label for="film_title">Title:</label>
                                <input class='filmtitle' type="text" id="film_title" name="film_title" value="<?php echo $row['film_title'];?>" />
                            </div>

                            <!-- IMAGE -->
                            <div class='item_field clearfix'>
                                <?php
                                    if ( file_exists("img/films/" . $film_id . ".png") ) {
                                        echo "<img style='max-width:90%' src='/img/films/" . $film_id . ".png' alt='" . $row['film_title'] . "' />";
                                        echo "<a href='/details.php?id=$film_id&deleteImage=true' title='Delete image' class='delete fa-solid fa-circle-minus fa-2xl' style='color: #ff0000;'></a>";
                                        $label_img="Replace image";
                                    } else {
                                        $label_img="Upload image";
                                    }
                                ?>
                                <label for="film_thumbnail_image"><?php echo $label_img ?>:</label>
                                <input type="file" id="film_thumbnail_image" name="film_thumbnail_image" accept="image/*"/>
                                <img id="preview-image"></img>
                            </div>

                            <!-- ALT TITLE -->
                            <div class='item_field clearfix'>
                                <label for="film_alt_title">Alternate title:</label>
                                <input type="text" id="film_alt_title" name="film_alt_title" value="<?php echo $row['film_alt_title'];?>" />
                            </div>

                            <!-- DURATION -->
                            <div class='item_field clearfix'>
                                <label for="film_duration">Film length:</label>
                                <?php
                                $minutes = (int) ( $row['film_duration_sec'] / 60);
                                $seconds = (int) ( $row['film_duration_sec'] % 60);
                                ?>
                                <input type="number" min="0" id="film_length_min" name="film_length_min" value="<?php echo $minutes;?>" />min &nbsp;&nbsp;
                                <input type="number" min="0" max="59" id="film_length_sec" name="film_length_sec" value="<?php echo $seconds;?>" />sec
                            </div>

                            <!-- YEAR -->
                            <div class='item_field clearfix'>
                                <label for="film_release_year">Year of release:</label>
                                <select  id="film_release_year" name="film_release_year">
                                    <option value="0">Unknown</option>
                                    <?php
                                    for ( $i=1892; $i<=date('Y'); $i++ ) {
                                        if ($row['film_release_year']==$i){
                                            $selected="selected";
                                        } else {
                                            $selected="";
                                        }
                                        echo "<option $selected>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- DESCRIPTION -->
                            <div class='item_field clearfix'>
                                <label for="film_description">Synopsis:</label>
                                <textarea id="film_description" name="film_description"><?php echo $row['film_description'];?></textarea>
                            </div>

                            <!-- TAGS -->
                            <div class='item_field clearfix'>
                                <label for="tag[]">Tag(s):</label>

                                <?php
                                $film_tags = explode(",",$row["tag_ids"]);
                                foreach ( $film_tags as $film_tag  ) {
                                    ?>
                                    <select  id="tag[]" name="tag[]">
                                        <option value="-1">Select one</option>
                                        <optgroup label="If not in this list">
                                            <option value='new'>Create new entry</option>
                                        </optgroup>
                                        <optgroup label="Select a Tag by:">
                                            <?php
                                            $tag_category=false;
                                            foreach ( $all_tags as $tag ) {
                                                if ( $tag_category != $tag[4]) {
                                                    if ( $tag_category != false ) {
                                                        echo "</optgroup>";
                                                    }
                                                    $tag_category = $tag[4];
                                                    echo "<optgroup label='&nbsp;&nbsp;&nbsp;&nbsp;&#x2022; ". $tag[4] ."'>";
                                                }
                                                if ($tag[0]==$film_tag){
                                                    $selected="selected";
                                                } else {
                                                    $selected="";
                                                }
                                                echo "<option value=" . $tag[0] . " $selected>" . $tag[1] . "</option>";
                                            }
                                            ?>
                                        </optgroup>
                                    </select>
                                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                                    <?php
                                }
                                ?>
                                <br /><a href="#" data-template="tag" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>
                            </div>

                            <!-- DIRECTORS -->
                            <div class='item_field clearfix'>
                                <label for="directors[]">Director(s):</label>

                                <?php
                                $film_directors = explode(",",$row["director_ids"]);
                                foreach ( $film_directors as $film_director  ) {
                                    ?>
                                    <select  id="director[]" name="director[]">
                                        <optgroup label="If not in this list">
                                            <option value='new'>Create new entry</option>
                                        </optgroup>
                                        <optgroup label="Select a director">
                                            <option value="1">Unknown</option>
                                            <?php
                                            foreach ( $all_directors as $director ) {
                                                if ($director[0]==$film_director){
                                                    $selected="selected";
                                                } else {
                                                    $selected="";
                                                }
                                                echo "<option value=" . $director[0] . " $selected>" . $director[1] . "</option>";
                                            }
                                            
                                            ?>
                                        </optgroup>
                                    </select>
                                    <?php
                                }
                                ?>
                                <br /><a href="#" data-template="director" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>
                            </div>


                            <!-- COUNTRIES -->
                            <div class='item_field clearfix'>
                                <label for="country[]">Country(ies):</label>

                                <?php
                                $film_countries = explode(",",$row["country_ids"]);
                                foreach ( $film_countries as $film_country  ) {
                                    ?>
                                    <select  id="country[]" name="country[]">
                                            <option value="1">Unknown</option>
                                            <?php
                                            foreach ( $all_countries as $country ) {
                                                if ($country[0]==$film_country){
                                                    $selected="selected";
                                                } else {
                                                    $selected="";
                                                }
                                                echo "<option value=" . $country[0] . " $selected>" . $country[1] . "</option>";
                                            }
                                            
                                            ?>
                                    </select>
                                    <?php
                                }
                                ?>
                                <br /><a href="#" data-template="country" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>
                            </div>

                            <!-- LANGUAGES -->
                            <div class='item_field clearfix'>
                                <label for="language[]">Language(s):</label>

                                <?php
                                $film_languages = explode(",",$row["language_ids"]);
                                foreach ( $film_languages as $film_language  ) {
                                    ?>
                                    <select  id="language[]" name="language[]">
                                            <?php
                                            foreach ( $all_languages as $language ) {
                                                if ($language[0]==$film_language){
                                                    $selected="selected";
                                                } else {
                                                    $selected="";
                                                }
                                                echo "<option value=" . $language[0] . " $selected>" . $language[1] . "</option>";
                                            }
                                            
                                            ?>
                                    </select>
                                    <?php
                                }
                                ?>
                                <br /><a href="#" data-template="language" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>
                            </div>

                            <!-- PRODUCTION HOUSES -->
                            <div class='item_field clearfix'>
                                <label for="studio[]">Production house(s):</label>

                                <?php
                                $film_studios = explode(",",$row["studio_ids"]);
                                foreach ( $film_studios as $film_studio  ) {
                                    ?>
                                    <select  id="studio[]" name="studio[]">
                                        <optgroup label="If not in this list">
                                            <option value='new'>Create new entry</option>
                                        </optgroup>
                                        <optgroup label="Select a Production house">
                                            <option value="1">Unknown</option>
                                            <?php
                                            foreach ( $all_studios as $studio ) {
                                                if ($studio[0]==$film_studio){
                                                    $selected="selected";
                                                } else {
                                                    $selected="";
                                                }
                                                echo "<option value=" . $studio[0] . " $selected>" . $studio[1] . "</option>";
                                            }
                                            
                                            ?>
                                        </optgroup>
                                    </select>
                                    <?php
                                }
                                ?>
                                <br /><a href="#" data-template="studio" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>
                            </div>

                            <!-- AVAILABILITY -->
                            <div class='item_field clearfix'>
                                <label for="film_availability">Link:</label>
                                <input type="url" id="film_availability" placeholder="https://www.example.com/" pattern="http.://.*" name="film_availability" value="<?php echo $row['film_availability'];?>" />
                            </div>
                            <div id="videoPlayer" class="videoPlayerDetail"></div>

                            <!-- BUTTONS -->
                            <div class='item_field clearfix'>
                                <button type="submit">Save</button>
                                <button type="reset">Cancel</button>
                            </div>

                            <!--
                                <a href="#" title="Update entry" class="update fa-solid fa-circle-check fa-2xl" style="color: #2ec27e;" onclick="updateField()"></a>
-->

                            </form>
						</div>
					<?php
				}
				?>

<!--            TEMPLATES FOR THE DYNAMIC GENERATION OF FIELDS -->
                <div style="display:none" id="template_director">
                    <select  id="director[]" name="director[]">
                        <option value="-1">Select one</option>
                        <optgroup label="If not in this list">
                            <option value='new'>Create new entry</option>
                        </optgroup>
                        <optgroup label="Select a Director">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_directors as $director ) {
                                echo "<option value=" . $director[0] . ">" . $director[1] . "</option>";
                            }
                            
                            ?>
                        </optgroup>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>
                <div style="display:none" id="template_country">
                    <select  id="country[]" name="country[]">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_countries as $country ) {
                                echo "<option value=" . $country[0] . ">" . $country[1] . "</option>";
                            }
                            
                            ?>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>
                <div style="display:none" id="template_new_studio_country">
                    <select  id="new_studio_country[]" name="new_studio_country[]">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_countries as $country ) {
                                echo "<option value=" . $country[0] . ">" . $country[1] . "</option>";
                            }
                            
                            ?>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>
                <div style="display:none" id="template_new_director_country">
                    <select  id="new_director_country[]" name="new_director_country[]">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_countries as $country ) {
                                echo "<option value=" . $country[0] . ">" . $country[1] . "</option>";
                            }
                            
                            ?>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>
                <div style="display:none" id="template_language">
                    <select  id="language[]" name="language[]">
                            <option value="1">Unknown</option>
                            <option value="2">Silent / No dialogs</option>
                            <?php
                            foreach ( $all_languages as $language ) {
                                echo "<option value=" . $language[0] . ">" . $language[1] . "</option>";
                            }
                            
                            ?>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>
                <div style="display:none" id="template_studio">
                    <select  id="studio[]" name="studio[]">
                        <option value="-1">Select one</option>
                        <optgroup label="If not in this list">
                            <option value='new'>Create new entry</option>
                        </optgroup>
                        <optgroup label="Select a Production house">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_studios as $studio ) {
                                echo "<option value=" . $studio[0] . ">" . $studio[1] . "</option>";
                            }
                            
                            ?>
                        </optgroup>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>
                
                <div style="display:none" id="template_tag">
                    <select  id="tag[]" name="tag[]">
                        <option value="-1">Select one</option>
                        <optgroup label="If not in this list">
                            <option value='new'>Create new entry</option>
                        </optgroup>
                        <optgroup label="Select a Tag by:">
                            <?php
                            $tag_category=false;
                            foreach ( $all_tags as $tag ) {
                                if ( $tag_category != $tag[4]) {
                                    if ( $tag_category != false ) {
                                        echo "</optgroup>";
                                    }
                                    $tag_category = $tag[4];
                                    echo "<optgroup label='&nbsp;&nbsp;&nbsp;&nbsp;&#x2022; ". $tag[4] ."'>";
                                }
                                echo "<option value=" . $tag[0] . ">&nbsp;&nbsp;&nbsp;&nbsp;" . $tag[1] . "</option>";
                            }    
                            ?>
                        </optgroup>
                    </select>
                    <a href="#" title="Delete entry" class="delete" style="color: #ff0000;" onclick="removeInfo(this);return false;"><i class=" fa-solid fa-circle-minus fa-2xl"></i> Remove field</a>
                </div>

                <!-- TEMPLATES FOR CREATING NEW ENTRIES -->
                <div style="display:none" id="new_tag">
                    <div class="newEntry clearfix">
                        <h3>Create a new tag:</h4>
                        <span class="tip">Your tag can have more than one word. Ex: "2D Animation" or "Social commentary". If you want to create new tags, click on "Add new".</span>
                        <label>Tag name</label>
                        <input type='text' name='new_tag[]' required placeholder="New tag"/>
                        <label>Tag category</label>
                        <select  name='new_tag_category[]' required>
                            <?php
                            foreach ( $all_tag_categories as $tag_category ) {
                                echo "<option value=" . $tag_category[0] . ">" . $tag_category[1] . "</option>";
                            }
                            
                            ?>
                        </select>
                    </div>
                </div>

                <div style="display:none" id="new_studio">
                    <div class="newEntry clearfix">
                        <h3>New production house details:</h4>
                        <label>Production house's name</label>
                        <input type='text' name='new_studio_name[]' required placeholder="Studio name"/>
                        
                        <label>Founded in (year)</label> 
                        <input type='number' name='new_studio_founded[]' min="1850" max="<?php echo date("Y"); ?>" /> (leave it blank if unknown)
                        
                        <label>Closed in (year)</label>
                        <input type='number' name='new_studio_closed[]' min="1850" max="<?php echo date("Y"); ?>" /> (leave it blank if unknown or not applicable)
                        
                        <label>Country(ies)</label>
                        <select  id="new_studio_country[]" name="new_studio_country[]">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_countries as $country ) {
                                echo "<option value=" . $country[0] . ">" . $country[1] . "</option>";
                            }
                            
                            ?>
                        </select>
                        <br />
                        <a href="#" data-template="new_studio_country" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>
                        

                        <label>Studio's page</label>
                        <input type="url" name="new_studio_link[]" placeholder="https://www.example.com/" pattern="http.://.*" />
                    </div>
                </div>

                <div style="display:none" id="new_director">
                    <div class="newEntry clearfix">
                        <h3>New director details:</h4>
                        <label>Director's name</label>
                        <input type='text' name='new_director_name[]' required placeholder="Director name"/>
                        
                        <label>Nationality</label>
                        <select  id="new_director_country[]" name="new_director_country[]">
                            <option value="1">Unknown</option>
                            <?php
                            foreach ( $all_countries as $country ) {
                                echo "<option value=" . $country[0] . ">" . $country[1] . "</option>";
                            }
                            
                            ?>
                        </select>
                        <br />
                        <a href="#" data-template="new_director_country" class="add" title="Add new entry"><i class="add fa-solid fa-circle-plus fa-2xl" style="color: #ffa348;" ></i> Add new</a>

                        <label>Gender</label>
                        <select  name="new_director_gender[]">
                            <?php
                            foreach ( $all_genders as $gender ) {
                                echo "<option value=" . $gender[0] . ">" . $gender[1] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
		</div>
	</main>
    <!-- JavaScript code -->
    <script src="js/cropper.min.js"></script>
    <script>

      const imageInput = document.getElementById('film_thumbnail_image');
      const previewImage = document.getElementById('preview-image');

      imageInput.addEventListener('change', function () {
        const file = this.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
          previewImage.src = e.target.result;

          // Initialize Cropper.js on the preview image
          const cropper = new Cropper(previewImage, {
            aspectRatio: 3 / 4, // Set the desired aspect ratio
            viewMode: 2, // Enable "crop" mode
            background: false
          });

          // Capture the cropped data on form submission
          const form = document.getElementById('film_details');
          form.addEventListener('submit', function (e) {
            e.preventDefault();

            const canvas = cropper.getCroppedCanvas({
                minWidth: 120,
                minHeight: 160,
                maxWidth: 675,
                maxHeight: 900
                });
            const croppedDataUrl = canvas.toDataURL();

            // Attach the cropped data to a hidden input field
            const croppedInput = document.createElement('input');
            croppedInput.type = 'hidden';
            croppedInput.name = 'cropped_image';
            croppedInput.value = croppedDataUrl;
            form.appendChild(croppedInput);

            // Submit the form
            form.submit();
          });
        };

        reader.readAsDataURL(file);
      });
    
        //listener and function to add a new select based on the hidden templates, for studios, directors, tags, countries and languages
        var addLink = document.querySelectorAll(".add");
        addLink.forEach(e => { e.addEventListener("click", addExisting, false); });

        function addExisting(event) {
            event.preventDefault();
            var clickedElement = event.target;
            var templateName = "template_" + clickedElement.dataset.template;
            var templateHTML = document.getElementById(templateName).innerHTML;
            clickedElement.insertAdjacentHTML("beforebegin", templateHTML);
            return false;
        }
      

      //listener and function to replace a select with a text input, to add a new entry
      document.querySelector('body').addEventListener('click', function(event) {
          var mySelect = document.querySelectorAll("select");
          mySelect.forEach(e => { e.addEventListener("click", addNew, false); });
          
          function addNew(event) {
            event.preventDefault();
            var clickedElement = event.target;
            if ( clickedElement.value == "new" ) {
                var inputType = clickedElement.parentElement.parentElement.name;
                var newInput="";
                if (inputType == "tag[]") {
                    newInput = document.getElementById("new_tag").innerHTML;
                }
                if (inputType == "studio[]") {
                    newInput = document.getElementById("new_studio").innerHTML;
                }
                if (inputType == "director[]") {
                    newInput = document.getElementById("new_director").innerHTML;
                }
                if (newInput != "") {
                    clickedElement.parentElement.parentElement.insertAdjacentHTML("beforebegin", newInput);
                    clickedElement.parentElement.parentElement.remove();
                    
                    //console.log(inputType, newInput, clickedElement.parentElement.parentElement);
                }
                var addLink = document.querySelectorAll(".add");
                addLink.forEach(e => { e.addEventListener("click", addExisting, false); });
                
                return false;
            }
            
          }
      });
      
      function removeInfo(field) {
        field.previousSibling.previousElementSibling.remove();
        field.remove();
      }

        function generateYouTubeEmbedCode(videoId) {
			return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
		}
		function generateVimeoEmbedCode(videoId) {
			return '<iframe src="https://player.vimeo.com/video/' + videoId + '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
		}

        
        
		// Get the video player element
		const videoPlayer = document.getElementById('videoPlayer');

		function generateYouTubeEmbedCode(videoId) {
			return '<iframe width="640" height="360" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
		}
		function generateVimeoEmbedCode(videoId) {
			return '<iframe src="https://player.vimeo.com/video/' + videoId + '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
		}
		function generateNfbEmbedCode(videoId) {
			return '<iframe src="https://www.nfb.ca/film/' + videoId + '/embed/player/" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" width="560" height="315" frameborder="0"></iframe><p style="width:560px">';
		}

		function generateEmbedCode(videoLink) {
			if (videoLink.includes('youtube.com')) {
				var videoId = videoLink.split('v=')[1];
				return generateYouTubeEmbedCode(videoId);
			} else if (videoLink.includes('vimeo.com')) {
				var videoId = videoLink.split('vimeo.com/')[1];
				return generateVimeoEmbedCode(videoId);
			} else if (videoLink.includes('nfb.ca')) {
				var videoId = videoLink.split('nfb.ca/film/')[1];
				var videoId = videoId.split('/')[0];
				return generateNfbEmbedCode(videoId);
			}
            console.log(videoLink);
			// Add more conditions for other video hosting platforms if necessary
			return '';
		}
		
		// Function to display the selected video
		function displayVideo(videoUrl) {
			// Update the video player element with the embed code
			videoPlayer.innerHTML = generateEmbedCode(videoUrl);
		}

        document.addEventListener("DOMContentLoaded", function(event) {
            displayVideo(document.getElementById('film_availability').value);
        });
		// Get video links and titles
		document.getElementById('film_availability').addEventListener('blur', function(element) {
            displayVideo(this.value);
        });

    </script>
</body>
</html>
