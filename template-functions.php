<?php
function pw_search_form() {
	$user_query = null;
	if (isset($_GET['q'])) {
		$user_query = urldecode($_GET['q']);
	}
?>
<h2 class="entry-title search-title"><?php pw_search_title(); ?></h2>
<div id="search-form">
	<form action="" method="get">
		<input type="text" name="q" id="search-input" value="<?php print $user_query; ?>" /> 
		<input type="submit" value="<?php pw_search_button(); ?>" />
	</form>
</div>
<?php
}

function pw_search() {
	global $pw_search_results;
	
	//print "<pre style='text-align:left'>".print_r($pw_search_results,true)."</pre>";
	if (is_pw_search()) {
?>
	<hr />
	<div class="result-info"><?php pw_search_result_info(); ?></div>

	<div id="search-results">
<?php pw_search_results(); ?>
	</div>
	
	<hr />
	<div id="search-paginate">
<?php pw_search_paginate(); ?>
	</div>
	
<?php
	}
}

function pw_search_title() {
	$search_title = __('Custom Search', 'pw_yahoo_boss');
	print apply_filters('pw_search_title', $search_title);
}

function pw_search_button() {
	$button_text = __('Search', 'pw_yahoo_boss');
	print apply_filters('pw_search_button', $button_text);
}

function pw_search_result_info() {
	global $pw_search_results;
	
	if (!empty($pw_search_results->results)) {
		$search = $pw_search_results;
		$result_info = ($search->start + 1) . ' - ' . (($search->start + 1) + $search->count - 1);
		$result_info .= ' '.__('of', 'pw_yahoo_boss').' ' . $search->totalhits . ' '.__('for', 'pw_yahoo_boss').' <b>' . urldecode($search->query) . '</b>';
		
		print apply_filters('pw_boss_result_info', $result_info, $search);
	}
}

function pw_search_results() {
	global $pw_search_results;
	
	if (empty($pw_search_results->results)) {
		// no search results
		pw_search_empty_results();
		return;
	}
	
	foreach ($pw_search_results->results as $key => $result) {
?>
	<div class="result" id="result_<?php print ($key + 1); ?>">
		<h3 class="result-title"><a href="<?php print $result['clickurl']; ?>"><?php print $result['title']; ?></a></h3>
		<div class="result-abstract"><?php print $result['abstract']; ?></div>
		<div class="result-meta">
			<span class="result-url" title="<?php print $result['url']; ?>"><?php print $result['dispurl']; ?></span> - 
			<span class="result-size"><?php print $result['size_f']; ?></span>
		</div>
	</div>
<?php }
}

function is_pw_search() {
	global $pw_search;
	
	return $pw_search->is_search;
}

function pw_search_paginate() {
	global $pw_search_results;
	
	if (!empty($pw_search_results->results)) {
		$search = $pw_search_results;
		$current_url = get_permalink();
		
		if ($search->page > 1) {
			echo '<span class="result-page"><a href="' . $current_url . '?q=' . $search->query . '&amp;page=' . ($search->page - 1) . '">'.__('&lt; Prev', 'pw_yahoo_boss').'</a></span> ';
		}
		
		$start = $search->page - (($search->page <= 5) ? ($search->results_per_page - $search->page) : 10);
		if ($start < 1) 
			$start = 1;
		
		$end = $search->page + (($search->page <= 5) ? ($search->results_per_page - $search->page) : 5);
		if ($end > ($search->totalhits / $search->results_per_page)) 
			$end = floor($search->totalhits / $search->results_per_page) + 1;
		
		for ($i = $start; $i <= $end; $i++) {
			if ($i == $search->page) {
				print '<span class="result-page-current">' . $i . "</span> ";
			} else {
				print '<span class="result-page"><a href="' . $current_url . '?q=' . $search->query . '&amp;page=' . $i . '">'.$i.'</a></span> ';
			}
		}
		if ($search->result_counter - 1 < $search->totalhits) {
			echo '<span class="result-page"><a href="' . $current_url . '?q=' . $search->query . '&amp;page=' . ($search->page + 1) . '">'.__('Next &gt;', 'pw_yahoo_boss').'</a></span>';
		}
	}
}

function pw_search_empty_results() {
	global $pw_search_results;
?>
	<p><?php _e('No results found searching for:', 'pw_yahoo_boss') ?> <b><?php echo urldecode($pw_search_results->query) ?></b>. <?php _e('Try the suggestions below or type a new query above.', 'pw_yahoo_boss') ?></p>
	<p><?php _e('Suggestions:', 'pw_yahoo_boss') ?></p>
	<ul>
		<li><?php _e('Check your spelling.', 'pw_yahoo_boss') ?></li>
		<li><?php _e('Try more general keywords.', 'pw_yahoo_boss') ?></li>
		<li><?php _e('Try different keywords.', 'pw_yahoo_boss') ?></li>
		<li><?php _e('Try fewer keywords.', 'pw_yahoo_boss') ?></li>
	</ul>
<?php
}
?>