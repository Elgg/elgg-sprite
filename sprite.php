<?php
/**
 * php sprite.php -d ~/my_sprite_dir/
 *
 * Will write elgg_sprites.png to current directory along with CSS snippet (sprite.css)
 */

function get_icon_name($filename) {
	$filename = substr($filename, (int)strpos($filename, '/'));
	$segments = explode('_', $filename);
	array_pop($segments);
	$name = implode('-', $segments);
	$name = str_replace('-hover', ':hover', $name);

	// ughh - special cases where we are doing double duty with an icon
	switch ($name) {
		case 'thumbs-down-alt';
			$name = ".elgg-icon-thumbs-down:hover,\n.elgg-icon-$name";
			break;
		case 'thumbs-up-alt';
			$name = ".elgg-icon-thumbs-up:hover,\n.elgg-icon-$name";
			break;
		default:
			$name = ".elgg-icon-$name";
			break;
	}
	return $name;
}

function get_css($filename, $offset) {
	$class = get_icon_name($filename);
	$css = <<<CSS
$class {
	background-position: 0 -{$offset}px;
}

CSS;
	return $css;
}

$spacing = 2;

$params = getopt('d:');

$directory = $params['d'];

$directory = rtrim($directory, "/");
$image_filenames = array();
$largest_width = 0;
$total_height = 0;

$handle = opendir($directory);
if ($handle) {
	while (($filename = readdir($handle)) !== false) {
		if (strpos($filename, ".png") !== false) {
			$image_filenames[] = $filename;
			list($width, $height) = getimagesize("$directory/$filename");
			if ($width > $largest_width) {
				$largest_width = $width;
			}
			$total_height += ($height + $spacing);
		}
	}
	closedir($handle);

	$image = imagecreatetruecolor($largest_width, $total_height);
	$background = imagecolorallocatealpha($image, 255, 255, 255, 127);
	imagefill($image, 0, 0, $background);
	imagealphablending($image, false);
	imagesavealpha($image, true);

	sort($image_filenames);
	$css = '';
	$offset = 0;
	foreach ($image_filenames as $filename) {
		list($width, $height) = getimagesize("$directory/$filename");
		$sprite = imagecreatefrompng("$directory/$filename");

		imagecopy($image, $sprite, 0, $offset, 0, 0, $width, $height);

		$css .= get_css($filename, $offset);
		
		$offset += ($height + $spacing);
		imagedestroy($sprite);
	}
	
	imagepng($image, "elgg_sprites.png");
	imagedestroy($image);

	file_put_contents('sprites.css', $css);
}
