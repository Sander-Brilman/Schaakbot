<?php
function get_page_info(array $url_array = []): array
{
	/**
	 * Returns a array with info and html tags for loading the page.
     * Including what files to load and the SEO (meta) tags.
     * 
	 * Used to define pages for the website.
     * 
     * 
     * -------------------------------------
     *         READ HERE HOW TO USE
     * -------------------------------------
     * @link https://github.com/Sander-Brilman/php-website-template#how-to-use--title-and-metatags-in-pages
     * 
     * 
	 *
	 * @param array The url array formatted inside config.php
	 *
	 * @return array Returns a associative with a array of file paths and the metatags.
	*/
	$php = [];
	$css = [];
	$js  = [];

    $canonical_url = generate_canonical_url();
	$meta_tags     = generate_meta_tags();
	$title         = generate_title();

	$no_index	   = false;

	$page_info  = [
		'files' => [
			'php' => &$php,
			'css' => &$css,
			'js'  => &$js,
		],
        'canonical_url' => &$canonical_url,
		'metatags' => &$meta_tags,
		'title' => &$title,
	];

    $css[] = 'style';

	// insert your pages here.
	switch ($url_array[0]) {
        case 'welcome':
            $php[] = 'get_name';
            $css[] = 'get_name';
            $title = generate_title('Schaken');
            break;

        case 'play':
			$php[] = 'play';
            $js[] = 'play';
            $css[] = 'play';
            $title = generate_title('Spelen');
            break;

		case '':
        case 'new-game':
            $php[] = 'new_game';
            $js[] = 'new_game';
            $css[] = 'new_game';
            $title = generate_title('Nieuw spel');
            $canonical_url = generate_canonical_url('new-game');
            break;

        case 'test':
            $php[] = 'testing';
            break;

		default:
			$php[] = '404';
			$no_index = true;
			$title = generate_title('Unknown page');
			break;
	}

	// add the path and file extension
	foreach ($page_info['files'] as $file_extension => &$names) {

		foreach ($names as &$name) {
			if (strpos($name, ".$file_extension") === false) {
				$name .= ".$file_extension";
			}

			switch ($file_extension) {
				case 'php':
					$name = 'pages/'.$name;
					break;
                
                case 'js':
				case 'css':
					$name = 'assets/'.$file_extension.'/'.$name;
					break;
			}
		}
	}

	if ($no_index) {
		$meta_tags = '<meta name="robots" content="noindex"/>';
	}

	return $page_info;
}

function generate_meta_tags(string $search_title = '', string $description = '', string $path_from_root = '', string $image_alt = ''): string
{
	/**
	 * Generate the html meta tags with the given values.
	 * Meta tags will fill with default values if left empty. 
     * 
     * Used inside the get_page_info function
	 * 
	 * @param string Title for the page
	 * @param string Title search results
	 * @param string The description
	 * @param string The image path. If left empty it will pick the favicon.
	 * @param string The description for the image, 
	 * 
	 * @return string The html meta tags. 
	 */
    global $display_name;
	global $theme_color;
    global $locate;

	global $default_search_title;
	global $default_website_description;

	$search_title 	= $search_title == '' ? $default_search_title : $search_title;
	$description 	= $description 	== '' ? $default_website_description : $description;

	if ($path_from_root == '') {
		$path_from_root = 'favicon.ico';
	}

	// title
	$meta_tags 	=  '<meta name="title" 	        content="'.$search_title.'" />
                    <meta property="og:title" 	content="'.$search_title.'" />
					<meta name="twitter:title" 	content="'.$search_title.'" />';

	// description
	$meta_tags 	.= '<meta name="description"         content="'.$description.'">
					<meta property="og:description"  content="'.$description.'" />
					<meta name="twitter:description" content="'.$description.'" />';

	// image & alt text.
	$meta_tags 	.= '<meta property="og:image"  		content="'.url($path_from_root).'" />
					<meta name="twitter:image" 		content="'.url($path_from_root).'" />
					<meta property="og:image:alt" 	content="'.$image_alt.'"  />';

	// site name
	$meta_tags 	.= '<meta property="og:site_name" content="'.$display_name.'" />';

	// Other
	$meta_tags 	.= '<meta property="og:locale" content="'.$locate.'" />
					<meta property="og:type"   content="website" />
					<meta name="theme-color"   content="'.$theme_color.'" />';

	return $meta_tags;
}

function generate_title(string $title = '', bool $add_display_name = true): string
{
	/**
	 * Generate the html title tag.
	 * If no value is given it will use the display name
     * 
     * Used inside the get_page_info function
	 * 
	 * @param string Title for the page
	 * @param bool Add a vertical + the display name to the title
	 * 
	 * @return string The html meta tags. 
	 */
	global $display_name;

	if ($title == '') {
		return "<title>$display_name</title>";
	}

	return '<title>' . $title . ($add_display_name ? " | $display_name" : '') . '</title>';
}

function generate_canonical_url(string $page_url = '', bool $use_url_function = true): string
{
    /**
     * Sets the canonical url tags.
     * By default it will use the current url without parameters or # to avoid search engines seeing it as duplicated content
     * 
     * If value is empty and there are no parameters it will return a empty string
     * 
     * @param string the canonical page url. Gets automatically inserted in the url function
     * 
     * @param bool Set to false to prevent the value automatically inserting in the url function
     * 
     * @param string the link & meta tags with the canonical url 
     */
    global $site_url;
    global $url_array;
    global $site_folder;

    if ($page_url == '') {
        $stripped_page_url = substr($site_url, 0, strlen($site_url) - 1);
        $original_page_url = $site_url.str_replace($site_folder, '', $_SERVER['REQUEST_URI']);

        foreach ($url_array as $item) $stripped_page_url .=  '/'.$item;

        if ($original_page_url === $stripped_page_url) {
            return '';
        }

        $page_url = $stripped_page_url;
    }

    $page_url = $use_url_function ? url($page_url) : $page_url;

    return '<link rel="canonical" href="'.$page_url.'">
            <meta property="og:url" content="'.$page_url.'" />';
}
?>