<?php
/**
 * php sprite -d ~/my_sprite_dir/
 *
 * Will write elgg-sprite.png to current directory along with CSS snippet (sprite.css)
 */

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
	$offset = 0;
	foreach ($image_filenames as $filename) {
		list($width, $height) = getimagesize("$directory/$filename");
		$sprite = imagecreatefrompng("$directory/$filename");

		imagecopy($image, $sprite, 0, $offset, 0, 0, $width, $height);
		$offset += ($height + $spacing);
		imagedestroy($sprite);
	}
	
	imagepng($image, "elgg-sprite.png");
	imagedestroy($image);
}
