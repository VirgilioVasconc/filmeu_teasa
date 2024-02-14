-- tables of different objects: films, studios, countries, directors, languages, tags
CREATE TABLE teasa_films (
  film_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  film_title varchar(255) NOT NULL,
  film_alt_title varchar(255) DEFAULT NULL,
  film_duration_sec int(11) DEFAULT NULL,
  film_release_year int(10) UNSIGNED NOT NULL,
  film_description text NOT NULL,
  film_thumbnail_image varchar(255) DEFAULT NULL,
  film_availability text DEFAULT NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE teasa_countries (
  country_id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  country_name varchar(255) NOT NULL,
  country_abv varchar(2) NOT NULL,
  country_abv3 varchar(3) NOT NULL,
  country_abv3_alt varchar(3) DEFAULT NULL,
  country_slug varchar(100) NOT NULL
);

CREATE TABLE teasa_studios (
  studio_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  studio_name VARCHAR(255) NOT NULL,
  studio_founded INT(11) UNSIGNED NULL,
  studio_closed INT(11) UNSIGNED NULL,
  studio_link varchar(255) DEFAULT NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0
);

CREATE TABLE teasa_languages (
  language_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  language_name VARCHAR(255) NOT NULL
);

CREATE TABLE teasa_directors (
  director_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  director_name VARCHAR(255) NOT NULL,
  director_gender INT(11) UNSIGNED NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0
);

CREATE TABLE teasa_directors_aka (
  director_aka_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  director_aka_name VARCHAR(255) NOT NULL,
  director_id INT(11) UNSIGNED,
  FOREIGN KEY (director_id) REFERENCES teasa_directors(director_id)
);

CREATE TABLE teasa_tags (
  tag_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tag_name VARCHAR(255) NOT NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0

);

-- tables of the relations between the objects
CREATE TABLE teasa_film_tag (
  film_id INT(11) UNSIGNED,
  tag_id INT(11) UNSIGNED,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,

  PRIMARY KEY (film_id, tag_id),
  FOREIGN KEY (film_id) REFERENCES teasa_films(film_id),
  FOREIGN KEY (tag_id) REFERENCES teasa_tags(tag_id)
);

CREATE TABLE teasa_film_director (
  film_id INT(11) UNSIGNED,
  director_id INT(11) UNSIGNED,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (film_id, director_id),
  FOREIGN KEY (film_id) REFERENCES teasa_films(film_id),
  FOREIGN KEY (director_id) REFERENCES teasa_directors(director_id)
);

CREATE TABLE teasa_film_studio (
  film_id INT(11) UNSIGNED,
  studio_id INT(11) UNSIGNED,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (film_id, studio_id),
  FOREIGN KEY (film_id) REFERENCES teasa_films(film_id),
  FOREIGN KEY (studio_id) REFERENCES teasa_studios(studio_id)
);

CREATE TABLE teasa_film_country (
  film_id INT(11) UNSIGNED,
  country_id INT(11) UNSIGNED,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (film_id, country_id),
  FOREIGN KEY (film_id) REFERENCES teasa_films(film_id),
  FOREIGN KEY (country_id) REFERENCES teasa_countries(country_id)
);

CREATE TABLE teasa_studio_country (
  studio_id int(10) UNSIGNED NOT NULL,
  country_id int(10) UNSIGNED NOT NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (studio_id, country_id),
  FOREIGN KEY (studio_id) REFERENCES teasa_studios(studio_id),
  FOREIGN KEY (country_id) REFERENCES teasa_countries(country_id)
);

CREATE TABLE teasa_director_country (
  director_id int(10) UNSIGNED NOT NULL,
  country_id int(10) UNSIGNED NOT NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (director_id, country_id),
  FOREIGN KEY (director_id) REFERENCES teasa_directors(director_id),
  FOREIGN KEY (country_id) REFERENCES teasa_countries(country_id)
  );
  
CREATE TABLE teasa_film_language (
  film_id INT(11) UNSIGNED,
  language_id INT(11) UNSIGNED,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (film_id, language_id),
  FOREIGN KEY (film_id) REFERENCES teasa_films(film_id),
  FOREIGN KEY (language_id) REFERENCES teasa_languages(language_id)
);


-- tables for user management and logs
CREATE TABLE teasa_users (
  user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(255) NOT NULL,
  user_password VARCHAR(255) NOT NULL,
  user_email VARCHAR(255) NOT NULL UNIQUE,
  user_profile INT(11) NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
);

CREATE TABLE teasa_genders (
  gender_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gender_name VARCHAR(255) NOT NULL,
  entry_disabled tinyint(1) NOT NULL DEFAULT 0
);

CREATE TABLE teasa_profiles (
  profile_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  profile_name VARCHAR(255) NOT NULL,
  profile_description TEXT NULL
);

CREATE TABLE teasa_logs (
  log_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) UNSIGNED NOT NULL,
  log_action VARCHAR(255) NOT NULL,
  log_timestamp DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES teasa_users(user_id) ON DELETE CASCADE
);
