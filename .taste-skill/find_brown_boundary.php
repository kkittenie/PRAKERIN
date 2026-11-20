<?php
$im = imagecreatefrompng("shots/menu/curve_inspect.png");
$w = imagesx($im);
$h = imagesy($im);

// We want to find the LOWEST point of brown in each column, from the bottom up!
// Scanning from y = $h - 1 going UP until we hit brown (r < 100, g < 70, b < 60)
$boundary = [];
for ($x = 0; $x < $w; $x += 16) {
    $brownY = 0;
    for ($y = $h - 1; $y >= 0; $y--) {
        $rgb = imagecolorat($im, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        // If it is brown
        if ($r < 120 && $g < 90 && $b < 75) {
            $brownY = $y;
            break;
        }
    }
    $boundary[] = ["x" => $x, "y" => $brownY];
}

echo "Boundary from bottom up (lowest brown edge):\n";
foreach ($boundary as $p) {
    $nx = round($p['x'] / $w * 1440);
    // map y to 0..350
    echo sprintf("x: %4d (nx: %4d) -> y: %3d\n", $p['x'], $nx, $p['y']);
}
