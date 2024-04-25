<!DOCTYPE html>
<?php
// Include config file
require_once "config.php";

//get min and max year of production to populate the search field
$query = "SELECT MAX(film_release_year) as year_max, MIN(film_release_year) as year_min FROM teasa_films WHERE entry_disabled=0 ;";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $year_max = $row['year_max'];
    $year_min = $row['year_min'];
}

//get min and max film duration to populate the search field
$query = "SELECT MAX(film_duration_sec) as duration_max, MIN(film_duration_sec) as duration_min FROM teasa_films WHERE entry_disabled=0 ;";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $duration_max = (int) ( 1 + ( $row['duration_max'] / 60) );
    $duration_min = (int) ( 1 + ( $row['duration_min'] / 60) );
}

//get ids and film titles for carousel
$query = "SELECT film_id, film_title, film_release_year FROM teasa_films WHERE entry_disabled=0 ORDER BY film_id ASC;";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
	// Fetch associative array
    $carousel[] = array(
            'film_id' => $row['film_id'],
            'film_title' => $row['film_title'],
            'film_release_year' => $row['film_release_year']
    );
}

//get unique film titles, combining both original and english titles
$query = "SELECT film_title AS titles FROM teasa_films UNION SELECT film_alt_title AS titles FROM teasa_films WHERE entry_disabled=0 ORDER BY titles ASC;";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
	if (!$titles) {
		$titles = '[{film_title:"' . $row["titles"] . '"}';

	} else {
		$titles .= ',{film_title:"' . $row["titles"] . '"}';
	}
}
$titles .= ']';
$titles_json = json_encode($titles);

//get unique countries of production
$query = "SELECT DISTINCT teasa_countries.country_id, country_name FROM teasa_countries INNER JOIN teasa_film_country WHERE teasa_countries.country_id=teasa_film_country.country_id ORDER BY country_name;";
$result = $conn->query($query);
$countries = $result->fetch_all();
$countries_json = json_encode($countries);

//get unique directors' names
$query = "SELECT DISTINCT director_id, director_name FROM teasa_directors WHERE entry_disabled=0 ORDER BY director_name";
$result = $conn->query($query);
$directors = $result->fetch_all();
$directors_json = json_encode($directors);

//get unique studios' names
$query = "SELECT DISTINCT studio_id, studio_name FROM teasa_studios WHERE entry_disabled=0 ORDER BY studio_name;";
$result = $conn->query($query);
$studios = $result->fetch_all();
$studios_json = json_encode($studios);

//get unique languages
$query = "SELECT DISTINCT teasa_languages.language_id, language_name FROM teasa_languages INNER JOIN teasa_film_language WHERE teasa_languages.language_id=teasa_film_language.language_id ORDER BY language_name;";
$result = $conn->query($query);
$languages = $result->fetch_all();
$languages_json = json_encode($languages);

//get unique tags
$query = "SELECT DISTINCT t.*, c.tag_category_name FROM teasa_tags t LEFT JOIN teasa_tagcategory c ON t.tag_category_id = c.tag_category_id WHERE t.entry_disabled=0 ORDER BY t.tag_category_id, t.tag_name;";
$result = $conn->query($query);
$tags = $result->fetch_all();
$tags_json = json_encode($tags);

//get unique genders
$query = "SELECT DISTINCT gender_id, gender_name FROM teasa_genders WHERE entry_disabled=0 ORDER BY gender_id;";
$result = $conn->query($query);
$genders = $result->fetch_all();
$genders_json = json_encode($genders);


function getFilmNumbers() {
    $filmNumbers = [];

    // Directory path
    $dir = 'img/films';

    // Check if directory exists
    if (is_dir($dir)) {
        // Open directory
        if ($dh = opendir($dir)) {
            // Read directory contents
            while (($file = readdir($dh)) !== false) {
                // Extract numbers from filename
                if (preg_match('/^(\d+)_tn\.png$/', $file, $matches)) {
                    $filmNumbers[] = $matches[1];
                }
            }
            // Close directory
            closedir($dh);
        }
    }

    // Shuffle the film numbers array
    shuffle($filmNumbers);

    return $filmNumbers;
}
$randomFilmNumbers = getFilmNumbers();

function getFilmTitleById($filmData, $filmId) {
    foreach ($filmData as $film) {
        if ($film['film_id'] == $filmId) {
			$title = $film['film_title'] . " (" .  $film['film_release_year'] . ")";
            return $title;
        }
    }
    return null; // Return null if film ID is not found
}

?>
<!DOCTYPE html>
<html>
	<?php require_once "head.php"; ?>
<body>
	<?php require_once "header.php"; ?>
	<main>
		<div class="tn-carousel">
			<div class="tn-slider">
				<?php
				
				// Assuming $randomFilmNumbers contains the list of film numbers
				foreach ($randomFilmNumbers as $filmNumber) {
					// Get film details from the database based on film number
					// Replace this with your actual database query
					$filmTitle = getFilmTitleById($carousel, $filmNumber);
					?>
					<div>
						<a class="carousel" href="details.php?id=<?php echo $filmNumber; ?>">
							<img src="img/films/<?php echo $filmNumber; ?>_tn.png" alt="<?php echo $filmTitle; ?>">
							<div class="carousel-film-title"><?php echo $filmTitle; ?></div>
						</a>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		
	    <div class="main clearfix">
			&nbsp;
			<p>
				TEASA is a database of animated short films from across Europe, created as a research pilot project from <a href="https://www.filmeu.eu/">FilmEU</a>. Our goal is to help animation scholars and enthusiasts to develop further academic investigations into the field of animation.
			</p>
			<p>
				You can search and create playlists, charts and custom selections of films in the form below based on various criteria. The code for this project is <a href="https://github.com/VirgilioVasconc/filmeu_teasa/" target="_blank">open and freely available to use and change on GitHub</a>.
			</p>

			<div id="main-action">
				<label for="global_result_type">Choose your search format:</label>
				<select id="global_result_type" style="width:auto;max-width:100%;height:1.5em;background-color:#84acea">
					<option value="list" selected>a detailed list</option>
					<option value="playlist">a playlist you can watch</option>
					<option value="chart">a chart of films by year</option>
					<option value="film">one specific film</option>
				</select>
			</div>
		
			<div class="form" id="advanced-form">
				Choose your criteria below: <br/>
				<input name="filter_type" value="all" id="allfilms" type="radio" checked="checked" /> <label for="allfilms">All films</label>
				|
				<input name="filter_type" value="filter" id="filter" type="radio" />  <label for="filter">Filter by... </label>
				<form class="advanced_form" name="advanced_search" action="search.php" method="post">
					<table id="tbl_advanced_form">
						<tr>
							<td class="label">
								<label for="director[]">Director(s):</label>
							</td>
							<td class="field">
								<select id="director[]" name="director[]" class="totalWidth" data-placeholder="Choose as many as you want. Leave it blank for all." multiple>
									<option value="Any">All</option>
									<?php
									foreach ($directors as $i) {
									echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="gender[]">Director's gender:</label>
							</td>
							<td class="field">
								<select id="gender[]" name="gender[]" class="totalWidth" data-placeholder="Choose as many as you want. Leave it blank for all." multiple>
									<option value="Any">Any</option>
									<?php
									foreach ($genders as $i) {
									echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
									}
									?>
								</select>
						</td>
						</tr>
						<tr>
							<td class="label">
								<label for="studio[]">Production house(s):</label>
							</td>
							<td class="field">
								<select id="studio[]" name="studio[]" class="totalWidth" data-placeholder="Choose as many as you want. Leave it blank for all." multiple>
									<option value="Any">All</option>
									<?php
									foreach ($studios as $i) {
									echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="year_start">Release year between</label>
							</td>
							<td class="field">
								<select id="year_start" name="year_start">
									<option value="Any">Any</option>
									<?php
									for ( $i=$year_min; $i<=$year_max; $i++ ) {
									echo "<option>$i</option>";
									}
									?>
								</select>
								<label for="year_end">and</label>
								<select id="year_end" name="year_end">
									<option value="Any">Any</option>
									<?php
									for ( $i=$year_min; $i<=$year_max; $i++ ) {
									echo "<option>$i</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="duration_start">Duration between</label>
							</td>
							<td class="field">
								<select id="duration_start" name="duration_start">
									<option value="Any">Any</option>
									<?php
									for ( $i=$duration_min; $i<=$duration_max; $i++ ) {
									echo "<option value='$i'>$i min</option>";
									}
									?>
								</select>
								<label for="duration_end">and</label>
								<select id="duration_end" name="duration_end">
									<option value="Any">Any</option>
									<?php
									for ( $i=$duration_min; $i<=$duration_max; $i++ ) {
									echo "<option value='$i'>$i min</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="country[]">Country of production:</label>
							</td>
							<td class="field">
								<select id="country[]" name="country[]" class="totalWidth" data-placeholder="Choose as many as you want. Leave it blank for all." multiple>
									<option value="Any">All</option>
									<?php
									foreach ($countries as $i) {
									echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="language[]">Language(s):</label>
							</td>
							<td class="field">
								<select id="language[]" name="language[]" class="totalWidth" data-placeholder="Choose as many as you want. Leave it blank for all." multiple>
									<option value="Any">Any</option>
									<?php
									foreach ($languages as $i) {
									echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class="label">
								<label for="tag[]">Tag(s):</label>
							</td>
							<td class="field">
								<select id="tag[]" name="tag[]" class="totalWidth" data-placeholder="Choose as many as you want. Leave it blank for all." multiple>
									<?php
									$tag_category=false;
									foreach ( $tags as $tag ) {
										if ( $tag_category != $tag[4]) {
											if ( $tag_category != false ) {
												echo "</optgroup>";
											}
											$tag_category = $tag[4];
											echo "<optgroup label='&#x2022; ". $tag[4] .":'>";
										}
										echo "<option value=" . $tag[0] . ">&nbsp;" . $tag[1] . "</option>";
									}    
									?>
								</select>
							</td>
						</tr>
						<tr id="tr-chart">
							<td class="label">
								<label for="chart_by">Chart results by:</label>
							</td>
							<td class="field">
								<select id="chart_by" name="chart_by" class="totalWidth" width="100%" data-placeholder="Choose one.">
									<option value="year">Year</option>
									<option value="country">Country of production</option>
									<option value="language">Language</option>
								</select>
							</td>
						</tr>
					</table>
					<hr />

					<input type="hidden" name="result_type" id="result_type" value="list" />
					<input type="hidden" name="criteria" id="criteria" />
					
					<button type="submit">Search</button>
					
				</form>
			</div>
			<div class="form" id="basic-form">
				<form class="search" name="title_search" action="search.php" method="get">
					<span id="search_box">
						<input type="search" id="query" autofocus name="query" placeholder="Search for a film..." autocomplete="off" />
					</span>
					<hr />
					<button type="submit">Search</button>
				</form>
			</div>
		</div>
	</main>
	<?php require_once "footer.php"; ?>
	
	<!-- Include any necessary JavaScript files here -->
	
	<script src="js/fuse.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/min/tiny-slider.js"></script>
	

	<script type="text/javascript">
		/* carousel */
		document.addEventListener('DOMContentLoaded', function () {
			var slider = tns({
			  container: '.tn-slider',
			  mode: "carousel",
			  items: 7,
			  slideBy: 1,
			  autoplay: true,
			  autoplayButton: false,
			  mouseDrag: false,
			  controls: false,
			  nav: false,
			  edgePadding: 5,
			  autoWidth: true,
			  responsive: {
				200: {
					gutter: 20,
					items: 1
				  },
				400: {
					gutter: 30,
					items: 2
				  },
				700: {
					items: 3
				},
				900: {
					items: 4
				  },
			  }
			});
		});
		/* end carousel */

		$(document).ready(function() {
			$('select').select2({
				    theme: 'bootstrap-5'
			});
			$("#tr-chart").hide();
			$("#tbl_advanced_form").hide();

			$("#allfilms").click(function() {
				$("#tbl_advanced_form").hide();
				$("form.advanced_form").reset();
			});
			$("#filter").click(function() {
				$("form.advanced_form").trigger("reset");
				$("#tbl_advanced_form").show();
			});
			
			$("#global_result_type").change(function() {
				$("#result_type").val($(this).val());
				if ( $(this).val() == "film" ){
					$("#advanced-form").hide();
					$("#basic-form").show();
				} else {
					$("#advanced-form").show();
					$("#basic-form").hide();
					if ( $(this).val() == "chart" ){
						$("#tr-chart").hide();
						//$("#tr-chart").show();
					} else {
						//$("#tr-chart").show();
						$("#tr-chart").hide();
					}
				}
			});
		});
		
		$(".tip").click(function(){
			$(this).fadeOut();
		});

		
		
		const jsonEncodedFilms = JSON.stringify(<?php echo $titles; ?>);
	   // Parse the JSON encoded variable
		const films = JSON.parse(jsonEncodedFilms);
		//const films = Object.values(jsonEncodedFilms);
		
		// Set up Fuse options
		const options = {
			keys: ['film_title'],
			threshold: 0.3
		};

		// Initialize Fuse with the film data and options
		const fuse = new Fuse(films, options);

		// Get DOM elements
		const searchBox = document.getElementById('query');
		const suggestionList = document.createElement('div');
		suggestionList.id = 'suggestion-list';
		searchBox.parentNode.appendChild(suggestionList);
		let selectedIndex = -1;

		searchBox.addEventListener("keydown", function(d) {
			if (d.keyCode === 13 ) { // Enter
				const suggestionItems = suggestionList.querySelectorAll('.suggestion-item');
				suggestionItems.forEach((item, index) => {
					if ( item.classList.contains("selected") ) {
						searchBox.value = item.innerHTML;
						document.forms.title_search.submit();
					} else {
						event.preventDefault();
					}
				});
					
			}
		});
		// Add event listener to the search box for keyup
		searchBox.addEventListener('keyup', function(e) {
			// Clear suggestion list
			suggestionList.innerHTML = '';

			// Get search query and search films with Fuse
			const query = e.target.value;
			const result = fuse.search(query);

			// Create suggestion list items and append them to the suggestion list
			for (let i = 0; i < Math.min(result.length, 5); i++) {
				const suggestion = result[i];
				//console.log(suggestion.item.film_title);
				const suggestionItem = document.createElement('div');
				suggestionItem.classList.add('suggestion-item');
				suggestionItem.textContent = suggestion.item.film_title;
				suggestionItem.addEventListener('click', function() {
					searchBox.value = suggestion.item.film_title;
					suggestionList.innerHTML = '';
				});
				suggestionList.appendChild(suggestionItem);
			}

			// Add CSS classes for fade in and fade out animations
			suggestionList.classList.add('fade-in');
			suggestionList.classList.remove('fade-out');

			// Use keyboard arrow keys up and down to select suggestions
			const suggestionItems = suggestionList.querySelectorAll('.suggestion-item');
			//console.log(selectedIndex);
			if (e.keyCode === 38) { // up arrow
				selectedIndex = Math.max(selectedIndex - 1, 0);
				suggestionItems.forEach((item, index) => {
					if (index === selectedIndex) {
						item.classList.add('selected');
					} else {
						item.classList.remove('selected');
					}
				});
			} else if (e.keyCode === 40) { // down arrow
				selectedIndex++;
				if (selectedIndex == suggestionItems.length) selectedIndex--;
				suggestionItems.forEach((item, index) => {
					if (index === selectedIndex) {
						item.classList.add('selected');
						//console.log("added", index, selectedIndex, suggestionItems.length);
					} else {
						item.classList.remove('selected');
						//console.log("removed", index, selectedIndex, suggestionItems.length);
					}
				});
			}
			//if (e.keyCode === 13 ) { // Enter
					//searchBox.value = result[selectedIndex].item.film_title;
					//form.title_search.submit();
					////console.log(searchBox.value);
			//}
			

		});

		// Add event listener to the document for click outside of the search box to hide the suggestion list
		document.addEventListener('click', function(e) {
			if (e.target !== searchBox && e.target !== suggestionList) {
				suggestionList.innerHTML = '';
				suggestionList.classList.add('fade-out');
				suggestionList.classList.remove('fade-in');
			}
			const selectedElement = document.querySelector('.selected');
			if ( e.target.classList.contains("suggestion-item") ) {
				searchBox.value = e.target.innerHTML;
				document.forms.title_search.submit();
			}			
		});

		const capitalize = str => {
			if (typeof str === 'string') {
				return str.replace(/^\w/, c => c.toUpperCase())
			} else {
				return ''
			}
		}
		
		/*
		function getCriteria(field){
			var e = document.getElementById(field);
			field = field.replace("[]","");
			var comma = "";
			var criteria = "";
			selectedOptions = e.selectedOptions;
			if (selectedOptions[0].label != "Any") {
				criteria += "<strong>" + capitalize(field) + "(s)</strong>: ";
				for (let i = 0; i < selectedOptions.length; i++) {
					criteria += comma + selectedOptions[i].label;
					comma = ", ";
				}
				criteria += ". ";
			}
			return criteria;
		}
		
		document.forms.advanced_search.onsubmit = function(){
			var criteria ="";
			criteria += getCriteria("director[]");
			
			criteria += getCriteria("gender[]");
			criteria += getCriteria("studio[]");
			criteria += getCriteria("country[]");
			criteria += getCriteria("language[]");
			criteria += getCriteria("tag[]");
			criteria += getCriteria("duration_start");
			criteria += getCriteria("duration_end");
			criteria += getCriteria("year_start");
			criteria += getCriteria("year_end");
			

			criteria = criteria.replace("Duration_start(s)", "Min. duration");
			criteria = criteria.replace("Duration_end(s)", "Max. duration");
			criteria = criteria.replace("Year_start(s)", "Initial year of release");
			criteria = criteria.replace("Year_end(s)", "Final year of release");

			if ( criteria == "" ) {
				criteria = "All films in the database";
			}
			document.getElementById("criteria").value=criteria;

			console.log(criteria);
			
			//return false;
		}
		*/

	</script>
</body>
</html>
