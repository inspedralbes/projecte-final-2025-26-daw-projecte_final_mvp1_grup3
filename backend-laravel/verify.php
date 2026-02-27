<?php
$passwords = [
    'admin123' => '$2y$10$V8t4bNRKScWo6pn.xz9pAOq5OuwqQzhnZ662lR.HRB58U0y.Hr.X.',
    'user123' => '$2y$10$HfOi4KLE0e15iw/D9AtpZ.WIXtyrt3CLza4tjqml9.YLsKsPccyTG'
];

foreach ($passwords as $plain => $hash) {
    echo "Verifying $plain vs $hash: " . (password_verify($plain, $hash) ? "MATCH" : "NO MATCH") . "\n";
}
