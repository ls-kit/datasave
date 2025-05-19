<?php
session_start();

function getTariffData($hsCode, &$attempts, $maxRetries = 5) {
    define('TARIFF_URL', 'https://bangladeshcustoms.gov.bd/users/search_operative_tariff?description=');
    $attempts = 0;
    while ($attempts++ < $maxRetries) {
        $url = TARIFF_URL . urlencode($hsCode);
        $html = @file_get_contents($url);

        if ($html !== false) {
            $dom = new DOMDocument(); @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $row = $xpath->query("//tr[@class='active']");

            if ($row->length > 0) {
                $cells = $row->item(0)->getElementsByTagName('td');
                $data = [
                    'hs_code'     => trim(str_replace('.', '', $cells->item(0)->nodeValue ?? '')),
                    'description' => trim($cells->item(1)->nodeValue ?? ''),
                    'cd'          => floatval(trim(str_replace('%','',$cells->item(2)->nodeValue))),
                    'sd'          => floatval(trim(str_replace('%','',$cells->item(3)->nodeValue))),
                    'vat'         => floatval(trim(str_replace('%','',$cells->item(4)->nodeValue))),
                    'ait'         => trim($cells->item(5)->nodeValue ?? ''),
                    'rd'          => floatval(trim(str_replace('%','',$cells->item(6)->nodeValue))),
                    'at'          => floatval(trim(str_replace('%','',$cells->item(7)->nodeValue)))
                ];
                return $data;
            }
        }
        sleep(1);
    }

    return [
        'hs_code'     => $hsCode,
        'description' => '',
        'cd' => 0, 'rd' => 0, 'sd' => 0, 'vat' => 15, 'ait' => '', 'at' => 5
    ];
}

if (!isset($_SESSION['rows'])) {
    $_SESSION['rows'] = [];
}

if (isset($_GET['reset'])) {
    session_destroy();
    // header('Location: ' . $_SERVER['REQUEST_URI']);
    // exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hsCode      = $_POST['hs_code'];
    $hsCode      = str_replace('.', '', $hsCode); // Normalize input for scraping
    $assessable  = floatval($_POST['assessable']);
    $pkgs        = intval($_POST['pkgs']);
    $additionPct = floatval($_POST['addition_pct']);
    $salesPct    = floatval($_POST['sales_pct']);
    $attempts    = 0;
    $t           = getTariffData($hsCode, $attempts);

    $cdAmt       = $assessable * $t['cd'] / 100;
    $rdAmt       = $assessable * $t['rd'] / 100;
    $sdAmt       = ($assessable + $cdAmt + $rdAmt) * $t['sd'] / 100;
    $vatBase     = $assessable + $cdAmt + $rdAmt + $sdAmt;
    $impVat      = $vatBase * $t['vat'] / 100;
    $atAmt       = $vatBase * $t['at'] / 100;

    $purchasePer = $vatBase / max($pkgs, 1);
    $addPer      = $purchasePer * $additionPct / 100;
    $salePer     = $purchasePer + $addPer;
    $totalSale   = $salePer * $pkgs;
    $saleVatAmt  = $totalSale * $salesPct / 100;
    $challanVal  = $saleVatAmt - $impVat - $atAmt;

    $_SESSION['rows'][] = [
        'hs_code'     => $t['hs_code'],
        'description' => $t['description'],
        'assess'      => round($assessable, 2),
        'cd_amt'      => round($cdAmt, 2),
        'rd_amt'      => round($rdAmt, 2),
        'sd_amt'      => round($sdAmt, 2),
        'vat_base'    => round($vatBase, 2),
        'imp_vat'     => round($impVat, 2),
        'at'          => round($atAmt, 2),
        'total_duty'  => round($cdAmt + $rdAmt + $sdAmt + $impVat + $atAmt, 2),
        'purchase'    => round($purchasePer, 2),
        'add_val'     => round($addPer, 2),
        'sale_val'    => round($salePer, 2),
        'total_sale'  => round($totalSale, 2),
        'sale_vat'    => round($saleVatAmt, 2),
        'challan'     => round($challanVal, 2),
        'cd_pct'      => $t['cd'],
        'rd_pct'      => $t['rd'],
        'sd_pct'      => $t['sd'],
        'vat_pct'     => $t['vat'],
        'at_pct'      => $t['at'],
        'ait'         => $t['ait']
    ];
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>  Table</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold"> Dashboard</h1>
      <a href="?reset=1" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">Reset</a>
    </div>
    <form method="POST" class="bg-white p-4 rounded shadow grid grid-cols-1 sm:grid-cols-6 gap-4">
      <div><label class="block text-sm font-medium">HS Code</label><input name="hs_code" required class="w-full border px-2 py-1 rounded text-xs"/></div>
      <div><label class="block text-sm font-medium">Assessable Value</label><input name="assessable" type="number" step="0.01" required class="w-full border px-2 py-1 rounded text-xs"/></div>
      <div><label class="block text-sm font-medium"># PKGS</label><input name="pkgs" type="number" step="1" value="1" class="w-full border px-2 py-1 rounded text-xs"/></div>
      <div><label class="block text-sm font-medium">Addition %</label><input name="addition_pct" type="number" step="0.01" value="5" class="w-full border px-2 py-1 rounded text-xs"/></div>
      <div><label class="block text-sm font-medium">Sales VAT %</label><input name="sales_pct" type="number" step="0.01" value="15" class="w-full border px-2 py-1 rounded text-xs"/></div>
      <div class="flex items-end"><button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 w-full">Add Row</button></div>
    </form>
    <div class="bg-white mt-6 rounded shadow overflow-auto">
      <table class="min-w-full text-xs border-collapse border border-gray-300">
        <thead class="bg-gray-100">
          <tr>
            <th class="border border-gray-300 px-1 py-1">SL</th>
            <th class="border border-gray-300 px-1 py-1">HS Code</th>
            <th class="border border-gray-300 px-1 py-1">Assess Val</th>
            <th class="border border-gray-300 px-1 py-1">CD Amt</th>
            <th class="border border-gray-300 px-1 py-1">RD Amt</th>
            <th class="border border-gray-300 px-1 py-1">SD Amt</th>
            <th class="border border-gray-300 px-1 py-1">VAT Base</th>
            <th class="border border-gray-300 px-1 py-1">Imp VAT</th>
            <th class="border border-gray-300 px-1 py-1">AT</th>
            <th class="border border-gray-300 px-1 py-1">Total Duty</th>
            <th class="border border-gray-300 px-1 py-1">Pur/PKG</th>
            <th class="border border-gray-300 px-1 py-1">Add Val/PKG</th>
            <th class="border border-gray-300 px-1 py-1">Sale Val/PKG</th>
            <th class="border border-gray-300 px-1 py-1">Tot Sales</th>
            <th class="border border-gray-300 px-1 py-1">Sale VAT</th>
            <th class="border border-gray-300 px-1 py-1">Challan Val</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $totals = array_fill_keys(['assess','cd_amt','rd_amt','sd_amt','vat_base','imp_vat','at','total_duty','purchase','add_val','sale_val','total_sale','sale_vat','challan'], 0);
          foreach ($_SESSION['rows'] as $i => $row) {
              echo '<tr>';
              echo '<td class="border border-gray-200 px-1 py-1">'.($i+1).'</td>';
              echo '<td class="border border-gray-200 px-1 py-1">'.htmlspecialchars($row['hs_code']).'</td>';
              foreach ($totals as $col => &$total) {
                  $val = isset($row[$col]) ? floatval($row[$col]) : 0;
                  $total += $val;
                  echo '<td class="border border-gray-200 px-1 py-1">'.number_format($val,2).'</td>';
              }
              echo '</tr>';
          }
          ?>
        </tbody>
        <tfoot>
          <tr class="bg-gray-100">
            <td colspan="2" class="border border-gray-300 px-1 py-1 text-right font-semibold">Totals</td>
            <?php foreach ($totals as $val): ?>
            <td class="border border-gray-300 px-1 py-1 font-semibold"><?php echo number_format($val,2); ?></td>
            <?php endforeach; ?>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</body>
</html>
