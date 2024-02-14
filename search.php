<?php
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);
// if the search query is empty, redirect to the start page
if (empty($_POST["director"]) && empty($_GET['query'])) {
	header('Location: index.php');
	exit;
}
?>
<!DOCTYPE html>
<html>
<?php require_once "head.php"; ?>
<body>
	<?php require_once "header.php"; ?>
	<main>
	    <div class="main">
			<button class="back" onclick="history.back();"><i class="fa fa-arrow-left"> </i> Back</button>
			<h1 class="mb-4">Search Results</h1>
			<?php

				// Include config file
				require_once "config.php";
				// Get search parameters from the URL. This is only for searching by film name
				$film_title = filter_var($_GET['query'], FILTER_SANITIZE_STRING);

				// Get search parameters from the URL. This is only for searching by film name
				$film_title = filter_var($_GET['query'], FILTER_SANITIZE_STRING);

				// Advanced search, works for POST
				$year_start = filter_var($_POST['year_start'], FILTER_SANITIZE_STRING);
				$year_end = filter_var($_POST['year_end'], FILTER_SANITIZE_STRING);
				$duration_start = filter_var($_POST['duration_start'], FILTER_SANITIZE_STRING);
				$duration_end = filter_var($_POST['duration_end'], FILTER_SANITIZE_STRING);
				$title_criteria = $_POST['criteria'];

				//echo "year start: $year_start, year end: $year_end, duration start: $duration_start, duration end: $duration_end";

				$director = array();
				foreach ($_POST['director'] as $value ) {
					$director[] = filter_var($value, FILTER_SANITIZE_STRING);
				}
				$gender = array();
				foreach ($_POST['gender'] as $value ) {
					$gender[] = filter_var($value, FILTER_SANITIZE_STRING);
				}
				$studio = array();
				foreach ($_POST['studio'] as $value ) {
					$studio[] = filter_var($value, FILTER_SANITIZE_STRING);
				}
				$language = array();
				foreach ($_POST['language'] as $value ) {
					$language[] = filter_var($value, FILTER_SANITIZE_STRING);
				}
				$country = array();
				foreach ($_POST['country'] as $value ) {
					$country[] = filter_var($value, FILTER_SANITIZE_STRING);
				}
				$tag = array();
				foreach ($_POST['tag'] as $value ) {
					$tag[] = filter_var($value, FILTER_SANITIZE_STRING);
				}
				// Perform search based on query
				
				//Give preference to the advanced search
				if (!empty($year_start)) {
					// build the SQL query based on the search query

					//release year
					if ( $year_start=="Any" && is_numeric($year_end)) {
						$year_criteria = "f.`film_release_year` <= $year_end ";
					}
					if ( $year_end=="Any" && is_numeric($year_start)) {
						$year_criteria = "f.`film_release_year` >= $year_start ";
					}
					if ( is_numeric($year_start) && is_numeric($year_end)) {
						$year_criteria = "f.`film_release_year` BETWEEN $year_start AND $year_end ";
					}

					//duration
					if ( $duration_start=="Any" && is_numeric($duration_end)) {
						$duration_criteria = "f.`film_duration_sec` <= " . (60*$duration_end) . " ";
					}
					if ( $duration_end=="Any" && is_numeric($duration_start)) {
						$duration_criteria = "f.`film_duration_sec` >= " . (60*$duration_start) . " ";
					}
					if ( is_numeric($duration_start) && is_numeric($duration_end)) {
						$duration_criteria = "f.`film_duration_sec` >= " . (60*$duration_start) . " AND f.`film_duration_sec` <= " . (60*$duration_end) . " ";
					}

					//director
					if ( $director[0] == "Any") {
						$director_criteria = "";
					} else {
						$director_criteria = "fd.`director_id` IN (";
						for ($i=0; $i<(sizeof($director)); $i++) {
							if ( !is_numeric($director[$i]) ) {
								die("Invalid search parameter");
							}
							if ( $i ==0 ) {
								$director_criteria .= $director[$i];
							} else {
								$director_criteria .= ",".$director[$i];
							}
						}
						$director_criteria .= ") ";
					}

					//gender
					if ( $gender[0] == "Any") {
						$gender_criteria = "";
					} else {
						$gender_criteria = "d.`director_gender` IN (";
						for ($i=0; $i<(sizeof($gender)); $i++) {
							if ( !is_numeric($gender[$i]) ) {
								die("Invalid search parameter");
							}
							if ( $i ==0 ) {
								$gender_criteria .= $gender[$i];
							} else {
								$gender_criteria .= ",".$gender[$i];
							}
						}
						$gender_criteria .= ") ";
					}
					
					//studio
					if ( $studio[0] == "Any") {
						$studio_criteria = "";
					} else {
						$studio_criteria = "fs.`studio_id` IN (";
						for ($i=0; $i<(sizeof($studio)); $i++) {
							if ( !is_numeric($studio[$i]) ) {
								die("Invalid search parameter");
							}
							if ( $i ==0 ) {
								$studio_criteria .= $studio[$i];
							} else {
								$studio_criteria .= ",".$studio[$i];
							}
						}
						$studio_criteria .= ") ";
					}
					
					//language
					if ( $language[0] == "Any") {
						$language_criteria = "";
					} else {
						$language_criteria = "fl.`language_id` IN (";
						for ($i=0; $i<(sizeof($language)); $i++) {
							if ( !is_numeric($language[$i]) ) {
								die("Invalid search parameter");
							}
							if ( $i ==0 ) {
								$language_criteria .= $language[$i];
							} else {
								$language_criteria .= ",".$language[$i];
							}
						}
						$language_criteria .= ") ";
					}

					//country
					if ( $country[0] == "Any") {
						$country_criteria = "";
					} else {
						$country_criteria = "fc.`country_id` IN (";
						for ($i=0; $i<(sizeof($country)); $i++) {
							if ( !is_numeric($country[$i]) ) {
								die("Invalid search parameter");
							}
							if ( $i ==0 ) {
								$country_criteria .= $country[$i];
							} else {
								$country_criteria .= ",".$country[$i];
							}
						}
						$country_criteria .= ") ";
					}

					//tag
					if ( $tag[0] == "Any") {
						$tag_criteria = "";
					} else {
						$tag_criteria = "ft.`tag_id` IN (";
						for ($i=0; $i < (sizeof($tag)); $i++) {
							if ( !is_numeric($tag[$i]) ) {
								die("Invalid search parameter");
							}
							if ( $i ==0 ) {
								$tag_criteria .= $tag[$i];
							} else {
								$tag_criteria .= ",". $tag[$i];
							}
						}
						$tag_criteria .= ") ";
					}

					
					
					//create SQL query
					$where_or_and = "WHERE";
					$condition ="";
					//depending on the type of result, the sql QUERY would differ
					if ( $_POST["result_type"] == "chart" ) {
						$sql = "SELECT films.film_release_year AS year, COUNT(*) AS count FROM ( ";
						$sql .= "SELECT DISTINCT f.film_id, f.film_release_year ";
					} else {
						$sql = "SELECT DISTINCT f.*, ";
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
					}
					

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
					
					
					if ($director_criteria!=""){
						$condition .= $where_or_and . " " . $director_criteria;
						$where_or_and = "AND";
					}
					if ($gender_criteria!=""){
						$condition .= $where_or_and . " " . $gender_criteria;
						$where_or_and = "AND";
					}
					if ($studio_criteria!=""){
						$condition .= $where_or_and . " " . $studio_criteria;
						$where_or_and = "AND";
					}
					if ($language_criteria!=""){
						$condition .= $where_or_and . " " . $language_criteria;
						$where_or_and = "AND";
					}
					if ($country_criteria!=""){
						$condition .= $where_or_and . " " . $country_criteria;
						$where_or_and = "AND";
					}
					if ($tag_criteria!=""){
						$condition .= $where_or_and . " " . $tag_criteria;
						$where_or_and = "AND";
					}
					if ($year_criteria!=""){
						$condition .= $where_or_and . " " . $year_criteria;
						$where_or_and = "AND";
					}	
					if ($duration_criteria!=""){
						$condition .= $where_or_and . " " . $duration_criteria;
						$where_or_and = "AND";
					}
					
					$sql .= $condition;

					//if it's to generate a playlist of videos
					if ($_POST["result_type"] == "playlist") {
						//$sql .= "AND f.film_availability LIKE CONCAT( '%', 'youtube.com/', '%') OR film_alt_title LIKE CONCAT( '%', 'vimeo.com/', '%')" ;
						$sql .= "$where_or_and (f.film_availability LIKE '%youtube.com%' OR f.film_availability LIKE '%vimeo.com%' OR f.film_availability LIKE '%nfb.ca%') " ;
					}
					
					$sql .="AND f.entry_disabled=0 ";
					$sql .="GROUP BY f.film_id ORDER BY f.film_release_year ASC";

					if ( $_POST["result_type"] == "chart" ) {
						$sql .=") AS films GROUP BY films.film_release_year ORDER BY films.film_release_year ASC;";
					} 
					

					//echo $sql;
					
					$stmt = $conn->prepare($sql);
					
					$stmt->execute();
					$result = $stmt->get_result();
					
				} elseif (!empty($film_title) ) {

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
					$sql .="WHERE (film_title LIKE CONCAT( '%', ?, '%') OR film_alt_title LIKE CONCAT( '%', ?, '%')) AND f.entry_disabled=0 ";
					$sql .="GROUP BY f.film_id ORDER BY f.film_title;";

					//$criteria = "film title = '" . $film_title . "'";
					//echo $sql;
					
					// assume $conn is your database connection object
					$stmt = $conn->prepare($sql);
					$stmt->execute([$film_title, $film_title]);
					$result = $stmt->get_result();

					$title_criteria = "<strong>Film title</strong>: " . $film_title;
				}

				//echo $sql;
				
				// get the number of rows returned by the SQL query
				$num_rows = mysqli_num_rows($result);

				$row = mysqli_fetch_assoc($result);

				?>
				
				<?php
				// if no rows were returned by the SQL query, display a message
				if ($num_rows == 0) {
				?>
				  <p>Your search criteria did not have results. Please <a href='index.php'>try again with different parameters</a>.</p>
				<?php
				} else {
					?>
					<div class="search_result_item clearfix">
						<?php
						// check kind of desired result: film list, chart or playlist
						if ( $_POST["result_type"] == "list" || isset($_GET["query"] ) ) {
							echo "<p class='result_criteria'>Results for $title_criteria</p>";
							?>
							<form id="teasa_search" name="teasa_search" action="search.php" method="post">
								<input type="hidden" id="director[]" name="director[]" value="Any" />
								<input type="hidden" id="gender[]" name="gender[]" value="Any" />
								<input type="hidden" id="studio[]" name="studio[]" value="Any" />
								<input type="hidden" id="country[]" name="country[]" value="Any" />
								<input type="hidden" id="language[]" name="language[]" value="Any" />
								<input type="hidden" id="tag[]" name="tag[]" value="Any" />
								<input type="hidden" id="duration_start" name="duration_start" value="Any" />
								<input type="hidden" id="duration_end" name="duration_end" value="Any" />
								<input type="hidden" id="year_start" name="year_start" value="Any" />
								<input type="hidden" id="year_end" name="year_end" value="Any" />
								<input type="hidden" id="criteria" name="criteria" value="Any" />
								<input type="hidden" id="result_type" name="result_type" value="list" />
								<table id='tbl_results' class="stripe">
									<thead>
										<tr>
											<td>Title</td>
											<td>Release year</td>
											<td>Alt. Title</td>
											<td>Director(s)</td>
											<td>Country(ies)</td>
											<td>Language(s)</td>
											<td>Production house(s)</td>
											<td>Tag(s)</td>
										</tr>
									</thead>
									<tbody>
										<?php
										// loop through each row returned by the SQL query and display it in a table row
										$row_color = "even";
										do {
											?>
											<tr class="<?php echo $row_color; ?>">
												<td>
													<?php
													if ( file_exists("img/films/" . $row['film_id'] . ".png") ) {
														echo "<a href='details.php?id=" . $row['film_id'] . "'>";
														echo "<img class='listimg' src='/img/films/" . $row['film_id'] . "_tn.png' alt='" . $row['film_title'] . "' />";
														echo "</a>";
													}
													?>
													<a href="details.php?id=<?php echo $row['film_id']; ?>"><strong><?php echo $row['film_title'];?></strong>
												</td>
												<td>
													<?php
													if ( $row['film_release_year']!="") {
														?>
														<button onclick="doSearch(this);" class="tag" value="<?php echo $row['film_release_year']; ?>" data-teasasearch="year" alt="Search all films by this criteria"><?php echo $row['film_release_year']; ?></button>
														<?php
													}
													?>
												</td>
												<td>
													<a href="details.php?id=<?php echo $row['film_id']; ?>"><strong><?php echo $row['film_alt_title'];?></strong>
												</td>
												<td>
													<?php
													if ( $row['directors']!="") {
														$names = explode(",",$row["directors"]);
														$ids = explode(",",$row["director_ids"]);
														$count = count($names);
														for ( $i=0; $i<$count ; $i++ ) {
														?>
															<button onclick="doSearch(this);" class="tag" value="<?php echo $ids[$i]; ?>" data-teasasearch="director[]" alt="Search all films by this criteria"><?php echo $names[$i]; ?></button>
														<?php
														}
													}
													?>
												</td>
												<td>
													<?php
													if ( $row['countries']!="") {
														$names = explode(",",$row["countries"]);
														$ids = explode(",",$row["country_ids"]);
														$count = count($names);
														for ( $i=0; $i<$count ; $i++ ) {
														?>
															<button onclick="doSearch(this);" class="tag" value="<?php echo $ids[$i]; ?>" data-teasasearch="country[]" alt="Search all films by this criteria"><?php echo $names[$i]; ?></button>
														<?php
														}
													}
													?>
												</td>
												<td>
													<?php
													if ( $row['languages']!="") {
														$names = explode(",",$row["languages"]);
														$ids = explode(",",$row["language_ids"]);
														$count = count($names);
														for ( $i=0; $i<$count ; $i++ ) {
														?>
															<button onclick="doSearch(this);" class="tag" value="<?php echo $ids[$i]; ?>" data-teasasearch="language[]" alt="Search all films by this criteria"><?php echo $names[$i]; ?></button>
														<?php
														}
													}
													?>
												</td>
												<td>
													<?php
													if ( $row['studios']!="") {
														$names = explode(",",$row["studios"]);
														$ids = explode(",",$row["studio_ids"]);
														$count = count($names);
														for ( $i=0; $i<$count ; $i++ ) {
														?>
															<button onclick="doSearch(this);" class="tag" value="<?php echo $ids[$i]; ?>" data-teasasearch="studio[]" alt="Search all films by this criteria"><?php echo $names[$i]; ?></button>
														<?php
														}
													}
													?>
												</td>
												<td>
													<?php
													if ( $row['tags']!="") {
														$names = explode(",",$row["tags"]);
														$ids = explode(",",$row["tag_ids"]);
														$count = count($names);
														for ( $i=0; $i<$count ; $i++ ) {
														?>
															<button onclick="doSearch(this);" class="tag" value="<?php echo $ids[$i]; ?>" data-teasasearch="tag[]" alt="Search all films by this criteria"><?php echo $names[$i]; ?></button>
														<?php
														}
													}
													?>
												</td>
											</tr>
											<?php
											if ( $row_color == "even" ) {
												$row_color = "odd";
											} else {
												$row_color = "even";
											}
										} while ($row = mysqli_fetch_assoc($result));
										?>
									</tbody>
								</table>
							</form>
							<?php
						}

						if ($_POST["result_type"] == "chart") {
							echo "<p class='result_criteria'>Chart: animated short films by $title_criteria per year</p>";
							?>							
								<canvas id="myChart" style="width:100%;"></canvas>
							
							<?php
						}

						if ($_POST["result_type"] == "playlist") {
							echo "<p class='result_criteria'>Playlist: animated short films by $title_criteria</p>";
							echo "<span class='tip'>Only films with direct links are listed below.</span>";
							?>
								<div id="player">
									<div id="playlist">
									  <!-- Video links will be dynamically added here -->
									</div>

									<!-- HTML structure for the video player -->
									<div id="videoPlayer">
									  <!-- Video player element will be updated here -->
									</div>
								</div>
								<div align='right'>
									<button class="tag" id="exportButton">Export this playlist</button>
								</div>
							<?php
							
						}
						?>
					</div>
					<?php
				}
				?>
		</div>
	</main>
	<?php require_once "footer.php"; ?>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/v/dt/jq-3.6.0/jszip-2.5.0/dt-1.13.4/b-2.3.6/b-colvis-2.3.6/b-html5-2.3.6/fh-3.3.2/datatables.min.js"></script>
	
	
	
	<?php
	if ( $_POST["result_type"] == "list" || isset($_GET["query"] ) ) {
		?>
		<script>
		$('#tbl_results').DataTable( {
			dom: 'Bfrtip',
			buttons: [
				'excel', 'pdf'
			]
		} );

		const capitalize = str => {
			if (typeof str === 'string') {
				return str.replace(/^\w/, c => c.toUpperCase())
			} else {
				return ''
			}
		}
		

		function doSearch(object) {
			//console.log(object.dataset.teasasearch);
			//console.log(object.value);
			var content = object.innerHTML;
			var criteria ="";
			var input_type = object.dataset.teasasearch;
			
			if (input_type != "year") {
				document.getElementById(input_type).value=object.value;
			}
			if (input_type == "year"){
				document.getElementById("year_start").value=object.value;
				document.getElementById("year_end").value=object.value;
			}

			input_type = input_type.replace("[]","");
			criteria += "<strong>" + capitalize(input_type) + "</strong>: ";
			criteria += content;
			criteria += ". ";
			
			if ( criteria == "" ) {
				criteria = "All films in the database";
			}
			document.getElementById("criteria").value=criteria;

			console.log(document.getElementById(input_type).value);
			console.log(criteria);
			
			//$("teasa_search").submit();
		}
		</script>
	<?php
	}
	
	if ($_POST["result_type"] == "chart") {
		$year = "[";
		$count = "[";
		$colors = "[";
		$comma=",";

		for ($i=0; $i < mysqli_num_rows($result); $i++) {
			$row = mysqli_fetch_assoc($result);
			if ( $i == ( mysqli_num_rows($result) -1 ) ) {
				$comma = "";
			}
			$year.= "'" . $row["year"] . "'$comma";
			$count.= "'" . $row["count"] . "'$comma";
			$colors.= "getRandomColor()$comma";
		}
		$year .= "]";
		$count .= "]";
		$colors .= "]";
		
		?>
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>

		<script>
		function getRandomColor() {
			var letters = '0123456789ABCDEF'.split('');
			var color = '#';
			for (var i = 0; i < 6; i++ ) {
				color += letters[Math.floor(Math.random() * 16)];
			}
			return color;
		}
		
		var xValues = <?php echo $year; ?>;
		var yValues = <?php echo $count; ?>;
		var barColors = <?php echo $colors; ?>;

		new Chart('myChart', {
			type: "bar",
			data: {
				labels: xValues,
				datasets: [{
					backgroundColor: barColors,
					data: yValues
				}]
			},
			options: {
				legend: {display: false},
				title: {
				  display: false
				}
			}
		});
		</script>
		<?php
	}

	if ($_POST["result_type"] == "playlist") {
		?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
		<script type="text/javascript">
			
		$(".tip").click(function(){
			$(this).fadeOut();
		});
		

		function generateYouTubeEmbedCode(videoId) {
			return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
		}
		function generateVimeoEmbedCode(videoId) {
			return '<iframe src="https://player.vimeo.com/video/' + videoId + '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
		}

		// Get video links and titles
		<?php
		$links = "";
		$titles = "";
		$comma = ",";
		for ($i=0; $i < mysqli_num_rows($result); $i++) {
			$row = mysqli_fetch_assoc($result);
			if ( $i == ( mysqli_num_rows($result) -1 ) ) {
				$comma = "";
			}
			$links .= "'" . $row["film_availability"] . "'$comma";
			$titles .= "'" . $row["film_title"] . "'$comma";
		}
		?>
		const videoLinks = [<?php echo $links; ?>];

		// Array of film titles
		const videoTitles = [<?php echo $titles; ?>];

		// Get the playlist and video player elements
		const playlist = document.getElementById('playlist');
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
			// Add more conditions for other video hosting platforms if necessary
			return '';
		}
		
		// Function to display the selected video
		function displayVideo(videoUrl) {
			// Update the video player element with the embed code
			videoPlayer.innerHTML = generateEmbedCode(videoUrl);
		}

		// Create the playlist HTML by looping over the video links
		for (let i = 0; i < videoLinks.length; i++) {
			const listItem = document.createElement('li');
			const link = document.createElement('a');
			link.href = videoLinks[i];
			link.textContent = videoTitles[i];
			//console.log(videoTitles);
			listItem.appendChild(link);
			playlist.appendChild(listItem);

			// Attach an event listener to the link
			link.addEventListener('click', (event) => {
			  event.preventDefault();
			  displayVideo(videoLinks[i]);
			});
		}

		// Function to generate and download the text file
		function exportList(customLine) {
		  // Get the film titles and links from the playlist
		  const playlist = document.getElementById('playlist');
		  const items = playlist.getElementsByTagName('a');
		  const data = Array.from(items).map(item => `${item.textContent}: ${item.href}`);

		  // Prepend the custom line to the data array
		  data.unshift(customLine);

		  // Create a Blob with the data
		  const blob = new Blob([data.join('\n')], { type: 'text/plain' });

		  // Create a download link and trigger the download
		  const downloadLink = document.createElement('a');
		  downloadLink.download = 'film_list.txt';
		  downloadLink.href = URL.createObjectURL(blob);
		  downloadLink.click();
		}

		// Attach the exportList function to the button's click event
		const exportButton = document.getElementById('exportButton');
		exportButton.addEventListener('click', () => {
			title = document.getElementsByClassName("result_criteria")[0].innerHTML;
			title = title.replaceAll("<strong>","");
			title = title.replaceAll("</strong>","");

		  customLine = "TEASA: The European Archive of Short Animation\n" + title + "\n\n";
		  exportList(customLine);
		});
		</script>

		<?php
	}
	?>
</body>
</html>
