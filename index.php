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

?>
<!DOCTYPE html>
<html>
	<?php require_once "head.php"; ?>
<body>
	<?php require_once "header.php"; ?>
	<main>
	    <div class="main clearfix">
		<span class="tip">TEASA is a database of animated short films from across Europe. You can search for films based on various criteria.</span>
		<h2>Search for a film's title:</h2>
		<div class="form">
			<form class="search" name="title_search" action="search.php" method="get">
				<span id="search_box">
					<input type="search" id="query" autofocus name="query" placeholder="Search for a film..." autocomplete="off" />
				</span>
				<hr />
				<button type="submit">Search</button>
			</form>
		</div>

		<h2>Or try an advanced search:</h2>
		<div class="form">
			<span class="tip">Tip: Hold down the Ctrl (PC) or Command (Mac) button to select multiple options.</span>
			<form class="advanced_form" name="advanced_search" action="search.php" method="post">			

				<div class="advanced_form" id="director">
					<label for="director[]">Director(s):</label>
					<select id="director[]" name="director[]" multiple>
						<option value="Any" selected>Any</option>
						<?php
						foreach ($directors as $i) {
						echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
						}
						?>
					</select>
				</div>

				<div class="advanced_form" id="director_gender">
					<label for="gender[]">Director's gender:</label>
					<select id="gender[]" name="gender[]" multiple>
						<option value="Any" selected>Any</option>
						<?php
						foreach ($genders as $i) {
						echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
						}
						?>
					</select>
				</div>

					
				<div class="advanced_form" id="studio">
					<label for="studio[]">Production house(s):</label>
					<select id="studio[]" name="studio[]" multiple>
						<option value="Any" selected>Any</option>
						<?php
						foreach ($studios as $i) {
						echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
						}
						?>
					</select>
				</div>

				
				
				<div class="advanced_form" id="film_release_year">
					<label for="year_start">Release year</label>
					<div class="from_to">
						<div>
						<label for="year_start">From:</label>
						<select id="year_start" name="year_start">
							<option value="Any">Any</option>
							<?php
							for ( $i=$year_min; $i<=$year_max; $i++ ) {
							echo "<option>$i</option>";
							}
							?>
						</select>
						</div>
						<div>
						<label for="year_end">To:</label>
						<select id="year_end" name="year_end">
							<option value="Any">Any</option>
							<?php
							for ( $i=$year_min; $i<=$year_max; $i++ ) {
							echo "<option>$i</option>";
							}
							?>
						</select>
						</div>
					</div>
				</div>
				
				<div class="advanced_form" id="film_duration">
					<label for="duration_start">Duration</label>
					<div class="from_to">
						<div>
						<label for="duration_start">From:</label>
						<select id="duration_start" name="duration_start">
							<option value="Any">Any</option>
							<?php
							for ( $i=$duration_min; $i<=$duration_max; $i++ ) {
							echo "<option value='$i'>$i min</option>";
							}
							?>
						</select>
						</div>
						<div>
						<label for="duration_end">To:</label>
						<select id="duration_end" name="duration_end">
							<option value="Any">Any</option>
							<?php
							for ( $i=$duration_min; $i<=$duration_max; $i++ ) {
							echo "<option value='$i'>$i min</option>";
							}
							?>
						</select>
						</div>
					</div>
				</div>
				
				<div class="advanced_form" id="country">
					<label for="country[]">Country of production:</label>
					<select id="country[]" name="country[]" multiple>
						<option value="Any" selected>Any</option>
						<?php
						foreach ($countries as $i) {
						echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
						}
						?>
					</select>
				</div>

				
				
				<div class="advanced_form" id="language">
					<label for="language[]">Language(s):</label>
					<select id="language[]" name="language[]" multiple>
						<option value="Any" selected>Any</option>
						<?php
						foreach ($languages as $i) {
						echo "<option value='" . $i[0] . "'>" . $i[1] . "</option>";
						}
						?>
					</select>
				</div>
				
				<div class="advanced_form" id="tag">
					<label for="tag[]">Attributed tag(s):</label>
					<select id="tag[]" name="tag[]" multiple>
						<option value="Any" selected>Any</option>
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
				</div>

				<hr />
					<div align="center">
						<label for="result_type">Desired result:</label><br />
						<select name="result_type">
							<option value="list" selected>List of films</option>
							<option value="chart">Chart by year</option>
							<option value="playlist">Playlist</option>
						</select>
					</div>
				<hr />
				
				<button type="submit">Search</button>
				<input type="hidden" name="criteria" id="criteria" />
			</form>
			</div>
	    </div>
	</main>
	<?php require_once "footer.php"; ?>
	
	<!-- Include any necessary JavaScript files here -->
	
	<script src="js/fuse.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
	<script type="text/javascript">
		
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
			
			
		//});
		//form.action = `search.php?query=${query}&category=${category}`;
		//form.title_search.submit();
		
	</script>
</body>
</html>
