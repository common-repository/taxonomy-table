<?php
/*
Plugin Name: Taxonomy (Category, Tag, ...) Table by Eyga.net
Plugin URI: http://wordpress.org/extend/plugins/taxonomy-table/
Description: Creates a HTML table form subitems of a specific taxonomy (category, tag, etc.) ID.
Version: 0.6.0
Author: DSmidgy
Author URI: http://blog.slo-host.com/
*/

// Displays a HTML table
add_filter('the_content', 'taxonomy_table_display');
function taxonomy_table_display($htmlContent) {
	// For performance measurements
	$debug = false;
	if ($debug == true) {
		$timeStart = microtime();
		$memcStart = memory_get_usage(true);
		$mempStart = memory_get_peak_usage(true);
	}
	// Read options from database
	$options = taxonomy_table_options('taxonomy_table');
	if (count($options) == 0) { return; }
	$postIdFilter = $options['postIdFilter'];
	$taxType = $options['taxType'];
	$taxIdFilter = $options['taxIdFilter'];
	$numberOfCols = $options['numberOfCols'];
	$displayOrder = $options['displayOrder'];
	$renderPosition = $options['renderPosition'];
	if (!is_numeric($postIdFilter) && $postIdFilter < 1
			&& !is_numeric($taxIdFilter) && $taxIdFilter < 1
			&& !is_numeric($numberOfCols) && $numberOfCols < 1) {
		return;
	}
	// Is this the page where the content should be rendered?
	global $id;
	if ($id == $postIdFilter) {
		// Get array of categories
		$taxonomies = array();
		if ($taxType == 'category') {
			$taxonomies = taxonomy_table_categories($taxIdFilter);
		}
		// Arrange taxonomies in rows and columns
		$taxonomiesArray = taxonomy_table_array($taxonomies, $numberOfCols, $displayOrder);
		// Render the arranged taxonomies in a html table
		if ($renderPosition == 'above') {
			$htmlContent = taxonomy_table_render($taxonomiesArray) . $htmlContent;
		} else {
			$htmlContent = $htmlContent . taxonomy_table_render($taxonomiesArray);
		}
	}
	// For performance measurements
	if ($debug == true) {
		echo '<br>Taxonomy Table plugin was executed in ' . round((microtime() - $timeStart) * 1000, 0) . ' miliseconds
			and used ' . ((memory_get_peak_usage(true) - $mempStart) / 1024) . ' kibibytes of memory for execution.
			WordPress is occuping ' . ((memory_get_usage(true) - $memcStart) / 1024) . ' kibibytes of memory more since the start of the plugin.<br>';
	}
	// Display the table
	return $htmlContent;
}

// Read list of categories
function taxonomy_table_categories($taxonomyIdFilter) {
	# Define top category filter in hierarchy and get the list
	$args = array(
		'child_of' => $taxonomyIdFilter,
		'hide_empty' => 0
	);
	$categories = get_categories($args);
	# Get array of indexes of non-empty taxonomies
	$taxonomies = array();
	$j = 0;
	foreach ($categories as $category) {
		if ($category->cat_name !== null
				&& $category->cat_ID !== null
				&& $category->category_count > 0) {
			$taxonomies[$j]['name'] = $category->cat_name;
			$taxonomies[$j]['link'] = get_category_link($category->cat_ID); # Performance hog!!!
			$taxonomies[$j]['count'] = $category->category_count;
			$j++;
		}
	}
	// Return the list of categories
	return $taxonomies;
}

// Arrange taxonomies in an 2D array
function taxonomy_table_array($taxonomies, $numberOfCols, $displayOrder) {
	// Calculate the number of taxonomies
	$numOfTaxs = count($taxonomies);
	// Calculate number of rows (no. of records / no. of cols)
	$numberOfRows = ceil($numOfTaxs / $numberOfCols);
	// Order of display: horizontal = left to right, vertical = top to bottom
	if ($displayOrder == 'vertical') {
		$aMax = $numberOfCols;
		$bMax = $numberOfRows;
	} else {
		$aMax = $numberOfRows;
		$bMax = $numberOfCols;
	}
	// Fill the array of columns and rows with data
	$taxonomiesArray = array();
	$j = 0;
	for ($a = 0; $a < $aMax; $a++) {
		for ($b = 0; $b < $bMax; $b++) {
			// There can be more AxB cells then taxonomy records
			if ($j < $numOfTaxs) {
				// Store the taxonomy index in an 2D table array
				if ($displayOrder == 'vertical') {
					$taxonomiesArray[$b][$a] = $taxonomies[$j];
				} else {
					$taxonomiesArray[$a][$b] = $taxonomies[$j];
				}
			}
			// Increase taxonomy index
			$j++;
		}
	}
	// Return 2D array
	return $taxonomiesArray;
}

// Render html table
function taxonomy_table_render($taxonomiesArray) {
	$numberOfRows = count($taxonomiesArray);
	$numberOfCols = count($taxonomiesArray[0]);
	# Open html table and define the columns
	$table = '<table class="taxonomyTable">' . "\n";
	for ($i = 0; $i < $numberOfCols; $i++) {
		$table .= '<col style="width:' . (100 / $numberOfCols) . '%;" />' . "\n";
	}
	# Render html rows
	for ($r = 0; $r < $numberOfRows; $r++) {
		// Open the row
		$table .= "  <tr>\n";
		// Render the cells
		for ($c = 0; $c < $numberOfCols; $c++) {
			# Define taxonomy data
			$taxName = $taxonomiesArray[$r][$c]['name'];
			$taxLink = $taxonomiesArray[$r][$c]['link'];
			$taxTitle = 'View all posts in ' . $taxName;
			$taxCount = $taxonomiesArray[$r][$c]['count'];
			# Display cell content if it exists
			if ($taxName != null) {
				$table .= '    <td>' . "\n";
				$table .= '      <a href="' . $taxLink . '" title="' . $taxTitle . '">' . $taxName . '</a> ' . $taxCount . "\n";
				$table .= '    </td>' . "\n";
			} else {
				$table .= '    <td></td>' . "\n";
			}
		}
		// Close the row
		$table .= "  </tr>\n";
	}
	// Close html table
	$table .= '</table>';
	// Return html table
	return $table;
}

// Read options from database
function taxonomy_table_options($optionName) {
	$optionValueRecord = get_option($optionName);
	$optionValueEntires = preg_split("/\|/", $optionValueRecord);
	$optionValueEntiresCount = count($optionValueEntires);
	// Parse options
	$options = array();
	for ($i = 0; $i < $optionValueEntiresCount; $i++) {
		$optionValueEntry = preg_split("/==/", $optionValueEntires[$i]);
		$optionKey = $optionValueEntry[0];
		$optionValue = $optionValueEntry[1];
		if ($optionKey != null && $optionValue != null) {
			$options[$optionKey] = $optionValue;
		}
	}
	// Return keys and values
	return $options;
}

// Style for the rendered table
add_action('wp_head', 'taxonomy_table_css');
function taxonomy_table_css() {
	?><style type='text/css'>
		table.taxonomyTable {
			width: 100%;
			border: none !important;
		}
		table.taxonomyTable tr {
		}
		table.taxonomyTable td {
			border: none !important;
			padding: 2px !important;
			font-size: 11px;
		}
	</style>
	<?php
}

/***********************/
/* Administration area */
/***********************/

add_action('admin_menu', 'taxonomy_table_admin');
function taxonomy_table_admin() {
	// Save options
	if (filter_input(INPUT_GET, 'page') == 'taxonomy_table_plugin' && filter_input(INPUT_POST, 'save') == 'Save Changes') {
		// Prepare string to write it into database
		$dbStr = '';
		$post = filter_input_array(INPUT_POST);
		$pluginPrefix = 'taxonomyTable_';
		foreach ($post as $key => $value) {
			if (substr($key, 0, strlen($pluginPrefix)) == $pluginPrefix) {
				if (strlen($dbStr) > 0) {
					$dbStr .= '|';
				}
				$dbStr .= str_replace('|', '', substr($key, strlen($pluginPrefix)) . '==' . $value);
			}
		}
		// Write options to database
		update_option('taxonomy_table', str_replace("'", "''", $dbStr));
		// Reload the page so "Saved" message will be displayed
		header("Location: options-general.php?page=taxonomy_table_plugin&saved=true");
		die;
	}
	// Call an options page
	add_options_page('TaxonomyTable', 'Taxonomy Table', 'administrator', 'taxonomy_table_plugin', 'taxonomy_table_settings');
}

function taxonomy_table_settings() {
	// Options have been saved
	if (filter_input(INPUT_POST, 'saved')) {
		echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
	}
	// Read options
	$options = taxonomy_table_options('taxonomy_table');
	// Display the interface
	$width = '100px';
	?><div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Taxonomy Table Options</h2>
		<div>Creates a table form a range of taxonomy (category, tag, etc.) records.<br>
			You specify one record in a hierarchy and the table gets created for its subrecords.<br>
			For now, the only supported taxonomies are categories.</div>
		<form action="" method="post">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Insert a taxonomy table into post with this ID<br>
					<td><input type="text" name="taxonomyTable_postIdFilter" value="<?php echo $options['postIdFilter'] ?>" style="width: <?php echo $width ?>;"></td>
				</tr>
				<tr>
					<th scope="row">Render a taxonomy table of this type</th>
					<td>
						<select name="taxonomyTable_taxType" style="width: <?php echo $width ?>;">
							<option value="">Choose!</option>
							<option value="category" <?php if ($options['taxType'] == 'category') echo 'selected'; ?>>Category</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">List taxonomies under this taxonomy (cat., tag, etc.) ID</th>
					<td><input type="text" name="taxonomyTable_taxIdFilter" value="<?php echo $options['taxIdFilter'] ?>" style="width: <?php echo $width ?>;"></td>
				</tr>
				<tr>
					<th scope="row">Number of columns in a taxonomy table</th>
					<td><input type="text" name="taxonomyTable_numberOfCols" value="<?php echo $options['numberOfCols'] ?>" style="width: <?php echo $width ?>;"></td>
				</tr>
				<tr>
					<th scope="row">Display records in this order</th>
					<td>
						<select name="taxonomyTable_displayOrder" style="width: <?php echo $width ?>;">
							<option value="">Choose!</option>
							<option value="horizontal" <?php if ($options['displayOrder'] == 'horizontal') echo 'selected'; ?>>Horizontal</option>
							<option value="vertical" <?php if ($options['displayOrder'] == 'vertical') echo 'selected'; ?>>Vertical</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">Where to render the table relative to page content</th>
					<td>
						<select name="taxonomyTable_renderPosition" style="width: <?php echo $width ?>;">
							<option value="">Choose!</option>
							<option value="above" <?php if ($options['renderPosition'] == 'above') echo 'selected'; ?>>Above</option>
							<option value="below" <?php if ($options['renderPosition'] == 'below') echo 'selected'; ?>>Below</option>
						</select>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="save" value="Save Changes" class="button-primary" />
				<input type="submit" name="cancel" value="Cancel" />
			</p>
		</form>
	</div>
	<?php
}
