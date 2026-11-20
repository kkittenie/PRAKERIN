<?php
$im = imagecreatefrompng("shots/menu/curve_inspect.png");
$w = imagesx($im);
$h = imagesy($im);

$points = [];
// Scan across x
for ($x = 0; $x < $w; $x += 32) {
    $foundY = $h - 1;
    for ($y = 0; $y < $h; $y++) {
        $rgb = imagecolorat($im, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        // Cream is high lightness
        if ($r > 200 && $g > 180 && $b > 150) {
            $foundY = $y;
            break;
        }
    }
    $points[] = ["x" => $x, "y" => $foundY];
}

echo "Image width: $w, height: $h\n";
$minY = 9999; $maxY = 0;
foreach ($points as $p) {
    if ($p['y'] < $minY) $minY = $p['y'];
    if ($p['y'] > $maxY) $maxY = $p['y'];
    $nx = round($p['x'] / $w * 1440);
    echo sprintf("x: %4d (nx: %4d) -> y: %3d\n", $p['x'], $nx, $p['y']);
}
echo "Min Y (highest crest): $minY, Max Y (lowest dip): $maxY\n";
