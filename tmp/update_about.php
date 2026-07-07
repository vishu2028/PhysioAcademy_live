<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;

$p = Page::where('slug', 'about-us')->first();
if ($p) {
    $p->content = '<h3>Welcome to Physio Academy</h3><p>Your premier destination for mastering the art and science of physical therapy. Our platform is designed to provide students and professionals with high-quality, structured learning materials that simplify complex topics.</p><p>We believe in bridging the gap between theoretical knowledge and clinical practice through structured, easy-to-follow study units created by industry experts.</p>';
    $p->save();
    echo "About page updated.\n";
} else {
    echo "About page not found.\n";
}
