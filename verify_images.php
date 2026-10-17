<?php
// Verifica que los photo IDs de Unsplash devuelvan 200 con content-type imagen.
$ids = [
    'deportes' => [
        'photo-1461896836934-ffe607ba8211', 'photo-1517649763962-0c623066013b',
        'photo-1571019613454-1cb2f99b2d8b', 'photo-1517836357463-d25dfeac3438',
        'photo-1544367567-0f2fcb009e0b', 'photo-1552674605-db6ffd4facb5',
        'photo-1546519638-68e109498ffc', 'photo-1522778119026-d647f0596c2',
        'photo-1476480862126-209bfaa8edc8', 'photo-1530549387789-4c1017266635',
    ],
    'tecnologia' => [
        'photo-1518770660439-4636190af475', 'photo-1496181133206-80ce9b88a853',
        'photo-1511707171634-5f897ff02aa9', 'photo-1587829741301-dc798b83add3',
        'photo-1526170375885-4d8ecf77b99f', 'photo-1547082299-de196ea013d6',
        'photo-1593642632823-8f785ba67e45', 'photo-1592750475338-74b7b21085ab',
        'photo-1583394838336-acd977736f90', 'photo-1546868871-7041f2a55e12',
    ],
    'hogar' => [
        'photo-1586023492125-27b2c045efd7', 'photo-1567016432779-094069958ea5',
        'photo-1555041469-a586c61ea9bc', 'photo-1522708323590-d24dbb6b0267',
        'photo-1505691938895-1758d9785115', 'photo-1522771739844-6a9f6b5f14c5',
        'photo-1585515320310-259814833e62', 'photo-1466637574441-749b8f19452f',
        'photo-1600585152220-90363fe7e115', 'photo-1524484485831-a92ffc0de03f',
    ],
    'moda' => [
        'photo-1434389677669-e08b4cac3105', 'photo-1445205170230-053b83016050a',
        'photo-1521572163474-6864f9cf17ab', 'photo-1551028719-00167b16eac5',
        'photo-1507679799987-c73779587ccf', 'photo-1524592094714-0f0654e20314',
        'photo-1547996160-81dfa63595aa', 'photo-1511499767150-a48a237f0083',
        'photo-1600185365483-26d7a4cc7515', 'photo-1543163521-1bf539c55dd2',
        'photo-1549298916-b41d501d3772', 'photo-1560343090-f0409e92791a',
    ],
    'outdoor' => [
        'photo-1501554728187-ce583db33af7', 'photo-1496545672447-f699b503d402',
        'photo-1470071459604-3dc1c37a7558', 'photo-1441974231531-c6227fac2edc',
        'photo-1501785888041-af3ef285b470', 'photo-1469474968028-56623f02e42e',
        'photo-1519681393784-d120267933ba', 'photo-1476514525535-07fb3b4ae5f1',
        'photo-1523987355523-c7b5b0dd90a7', 'photo-1517824806704-9040b037703b',
    ],
];

$mh = curl_multi_init();
$handles = [];
foreach ($ids as $cat => $list) {
    foreach ($list as $id) {
        $ch = curl_init("https://images.unsplash.com/{$id}?q=80&w=100&auto=format&fit=crop");
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[(int) $ch] = [$cat, $id, $ch];
    }
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh, 1);
} while ($running > 0);

$ok = [];
foreach ($handles as [$cat, $id, $ch]) {
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $good = ($code === 200 && str_starts_with((string) $type, 'image'));
    echo ($good ? 'OK   ' : 'FAIL ') . "$cat $id (HTTP $code, $type)" . PHP_EOL;
    if ($good) $ok[$cat][] = $id;
    curl_multi_remove_handle($mh, $ch);
}
curl_multi_close($mh);

file_put_contents(
    'C:\\GSW\\gsw-ecommerce\\storage\\app\\verified_images.json',
    json_encode($ok, JSON_PRETTY_PRINT)
);
echo 'Guardado verified_images.json' . PHP_EOL;
