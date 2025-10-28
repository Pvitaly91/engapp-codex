<?php
/**
 * Script to update the description field in outpu.csv based on symbol lookups
 * from 1.csv, 2.csv, and 3.csv.
 */

$baseDir = __DIR__;
$outputFile = $baseDir . DIRECTORY_SEPARATOR . 'outpu.csv';
$sourceFiles = [
    $baseDir . DIRECTORY_SEPARATOR . '1.csv',
    $baseDir . DIRECTORY_SEPARATOR . '2.csv',
    $baseDir . DIRECTORY_SEPARATOR . '3.csv',
];

if (!file_exists($outputFile)) {
    fwrite(STDERR, "Output file not found: {$outputFile}\n");
    exit(1);
}

/**
 * Wrapper around fgetcsv with explicit parameters to avoid deprecation warnings.
 */
function readCsvRow($handle)
{
    return fgetcsv($handle, 0, ',', '"', '\\');
}

/**
 * Wrapper around fputcsv with explicit parameters to avoid deprecation warnings.
 */
function writeCsvRow($handle, array $row)
{
    return fputcsv($handle, $row, ',', '"', '\\');
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        if ($needle === '') {
            return true;
        }

        return strpos($haystack, $needle) !== false;
    }
}

$symbolToName = [];

$manualMappingFile = $baseDir . DIRECTORY_SEPARATOR . 'asset_manual_mappings.php';
$manualMappings = [];
if (is_file($manualMappingFile)) {
    $loaded = include $manualMappingFile;
    if (is_array($loaded)) {
        $manualMappings = $loaded;
    }
}

function manualDescriptionLookup($symbol, array $manualMappings)
{
    return $manualMappings[$symbol] ?? null;
}

function getCurrencyName($code)
{
    static $currencyNames = [
        'AED' => 'United Arab Emirates Dirham',
        'AUD' => 'Australian Dollar',
        'BRL' => 'Brazilian Real',
        'CAD' => 'Canadian Dollar',
        'CLP' => 'Chilean Peso',
        'CHF' => 'Swiss Franc',
        'GHS' => 'Ghanaian Cedi',
        'CNH' => 'Chinese Yuan Offshore',
        'CNY' => 'Chinese Yuan Renminbi',
        'CZK' => 'Czech Koruna',
        'DKK' => 'Danish Krone',
        'EUR' => 'Euro',
        'GBP' => 'British Pound Sterling',
        'HKD' => 'Hong Kong Dollar',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli New Shekel',
        'INR' => 'Indian Rupee',
        'JPY' => 'Japanese Yen',
        'KES' => 'Kenyan Shilling',
        'KHR' => 'Cambodian Riel',
        'KRW' => 'South Korean Won',
        'LAK' => 'Laotian Kip',
        'MYR' => 'Malaysian Ringgit',
        'MXN' => 'Mexican Peso',
        'NOK' => 'Norwegian Krone',
        'NZD' => 'New Zealand Dollar',
        'PHP' => 'Philippine Peso',
        'PLN' => 'Polish Zloty',
        'RON' => 'Romanian Leu',
        'RUB' => 'Russian Ruble',
        'SAR' => 'Saudi Riyal',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'TZS' => 'Tanzanian Shilling',
        'UAH' => 'Ukrainian Hryvnia',
        'THB' => 'Thai Baht',
        'TRY' => 'Turkish Lira',
        'TWD' => 'New Taiwan Dollar',
        'UGX' => 'Ugandan Shilling',
        'USD' => 'US Dollar',
        'USDC' => 'USD Coin',
        'USDT' => 'Tether',
        'DAI' => 'Dai Stablecoin',
        'BUSD' => 'Binance USD',
        'VND' => 'Vietnamese Dong',
        'ZAR' => 'South African Rand',
    ];

    $code = strtoupper(trim($code));

    return $currencyNames[$code] ?? null;
}

function resolveForexDescription($symbol)
{
    $parts = explode('/', $symbol);
    if (count($parts) === 2) {
        $base = strtoupper(trim($parts[0]));
        $quote = strtoupper(trim($parts[1]));
        $baseName = getCurrencyName($base);
        $quoteName = getCurrencyName($quote);
        if ($baseName !== null && $quoteName !== null) {
            return $baseName . ' / ' . $quoteName;
        }
    } elseif (strlen($symbol) === 6) {
        $base = strtoupper(substr($symbol, 0, 3));
        $quote = strtoupper(substr($symbol, 3));
        $baseName = getCurrencyName($base);
        $quoteName = getCurrencyName($quote);
        if ($baseName !== null && $quoteName !== null) {
            return $baseName . ' / ' . $quoteName;
        }
    }

    return null;
}

function formatTitleCase(string $value)
{
    $value = str_replace(['-', '_'], ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim(ucwords(strtolower($value)));
}

function resolveCryptoDescription($symbol)
{
    static $cryptoNames = [
        '1INCH' => '1inch Network Token',
        'AAVE' => 'Aave Token',
        'ADA' => 'Cardano (ADA)',
        'AERGO' => 'Aergo Token',
        'AIOZ' => 'AIOZ Network Token',
        'AKT' => 'Akash Network Token',
        'ALGO' => 'Algorand (ALGO)',
        'AMP' => 'Amp Token',
        'ANKR' => 'Ankr Network Token',
        'APT' => 'Aptos (APT)',
        'AR' => 'Arweave (AR)',
        'ARB' => 'Arbitrum (ARB)',
        'ASR' => 'AS Roma Fan Token',
        'ATOM' => 'Cosmos (ATOM)',
        'AVAX' => 'Avalanche (AVAX)',
        'AXS' => 'Axie Infinity Shard',
        'BAKE' => 'BakeryToken',
        'BAL' => 'Balancer (BAL)',
        'BAND' => 'Band Protocol',
        'BAT' => 'Basic Attention Token',
        'BCH' => 'Bitcoin Cash',
        'BEL' => 'Bella Protocol',
        'BICO' => 'Biconomy Token',
        'BIT' => 'BitDAO Token',
        'BLZ' => 'Bluzelle Token',
        'BNB' => 'BNB (Binance Coin)',
        'BNT' => 'Bancor Network Token',
        'BTC' => 'Bitcoin',
        'BTG' => 'Bitcoin Gold',
        'BTT' => 'BitTorrent Token',
        'CELO' => 'Celo Token',
        'CHZ' => 'Chiliz Token',
        'CKB' => 'Nervos Network Token',
        'COMP' => 'Compound',
        'COTI' => 'COTI Token',
        'CRV' => 'Curve DAO Token',
        'CSPR' => 'Casper Network Token',
        'CTSI' => 'Cartesi Token',
        'CVC' => 'Civic Token',
        'DASH' => 'Dash',
        'DOGE' => 'Dogecoin',
        'DOT' => 'Polkadot (DOT)',
        'DYDX' => 'dYdX Token',
        'EGLD' => 'MultiversX (EGLD)',
        'ENJ' => 'Enjin Coin',
        'ENS' => 'Ethereum Name Service',
        'EOS' => 'EOS Token',
        'ETC' => 'Ethereum Classic',
        'ETH' => 'Ethereum',
        'FET' => 'Fetch.ai Token',
        'FIL' => 'Filecoin',
        'FLOW' => 'Flow Token',
        'FTM' => 'Fantom Token',
        'FTT' => 'FTX Token',
        'GALA' => 'Gala Token',
        'GRT' => 'The Graph Token',
        'HBAR' => 'Hedera Hashgraph',
        'HNT' => 'Helium Network Token',
        'HOT' => 'HoloToken',
        'ICP' => 'Internet Computer (ICP)',
        'ICX' => 'ICON Token',
        'IMX' => 'Immutable X Token',
        'JST' => 'JUST Token',
        'KAVA' => 'Kava Token',
        'KLAY' => 'Klaytn Token',
        'KNC' => 'Kyber Network Crystal',
        'KSM' => 'Kusama (KSM)',
        'LINK' => 'Chainlink Token',
        'LRC' => 'Loopring Token',
        'LTC' => 'Litecoin',
        'LUNA' => 'Terra (LUNA)',
        'MANA' => 'Decentraland (MANA)',
        'MASK' => 'Mask Network Token',
        'MATIC' => 'Polygon (MATIC)',
        'MKR' => 'Maker',
        'MTL' => 'Metal DAO Token',
        'NEAR' => 'NEAR Protocol Token',
        'NEO' => 'NEO Token',
        'NEXO' => 'Nexo Token',
        'NMR' => 'Numeraire',
        'OGN' => 'Origin Protocol',
        'OMG' => 'OMG Network Token',
        'ONE' => 'Harmony (ONE)',
        'ONT' => 'Ontology Token',
        'OP' => 'Optimism Token',
        'PAXG' => 'PAX Gold',
        'QNT' => 'Quant Token',
        'QTUM' => 'Qtum Token',
        'REN' => 'Ren Token',
        'RNDR' => 'Render Token',
        'ROSE' => 'Oasis Network Token',
        'RSR' => 'Reserve Rights Token',
        'RUNE' => 'THORChain (RUNE)',
        'SAND' => 'The Sandbox Token',
        'SHIB' => 'Shiba Inu Token',
        'SKL' => 'SKALE Network Token',
        'SNX' => 'Synthetix Network Token',
        'SOL' => 'Solana (SOL)',
        'STG' => 'Stargate Finance Token',
        'STORJ' => 'Storj Token',
        'STX' => 'Stacks Token',
        'SUSHI' => 'SushiSwap Token',
        'TRX' => 'TRON (TRX)',
        'UMA' => 'UMA Protocol Token',
        'UNI' => 'Uniswap Token',
        'USDC' => 'USD Coin',
        'VET' => 'VeChain Token',
        'WAVES' => 'Waves Token',
        'WAXP' => 'WAX Token',
        'XEC' => 'eCash Token',
        'XLM' => 'Stellar Lumens',
        'XMR' => 'Monero',
        'XRP' => 'Ripple (XRP)',
        'XTZ' => 'Tezos Token',
        'YFI' => 'yearn.finance Token',
        'ZEC' => 'Zcash',
        'ZIL' => 'Zilliqa Token',
    ];

    $basePart = $symbol;
    $quotePart = null;

    if (str_contains($symbol, '-')) {
        [$basePart, $quotePart] = explode('-', $symbol, 2);
    } elseif (str_contains($symbol, '/')) {
        [$basePart, $quotePart] = explode('/', $symbol, 2);
    }

    $baseKey = strtoupper(trim($basePart));

    if (isset($cryptoNames[$baseKey])) {
        $baseName = $cryptoNames[$baseKey];
    } else {
        $pretty = formatTitleCase($baseKey);
        if ($pretty === '') {
            return null;
        }
        $baseName = $pretty . ' Token';
    }

    if ($quotePart === null) {
        return $baseName;
    }

    $quoteKey = strtoupper(trim($quotePart));
    $quoteName = getCurrencyName($quoteKey);

    if ($quoteName === null && isset($cryptoNames[$quoteKey])) {
        $quoteName = $cryptoNames[$quoteKey];
    }

    if ($quoteName === null) {
        $quoteName = formatTitleCase($quoteKey);
        if ($quoteName === '') {
            $quoteName = $quoteKey;
        }
    }

    return $baseName . ' / ' . $quoteName;
}

function resolveCommodityDescription($symbol)
{
    static $commodityNames = [
        'WTICO/USD' => 'West Texas Intermediate Crude Oil',
        'XAU/USD' => 'Gold Spot Price',
        'NATGAS/USD' => 'Natural Gas Spot Price',
        'XPD/USD' => 'Palladium Spot Price',
        'XAG/USD' => 'Silver Spot Price',
        'XPT/USD' => 'Platinum Spot Price',
        'ALUMINUM' => 'Aluminum Futures',
        'BRENTOIL' => 'Brent Crude Oil Futures',
        'COCOA' => 'Cocoa Futures',
        'COFFEE' => 'Coffee Futures',
        'COPPER' => 'Copper Futures',
        'COTTON' => 'Cotton Futures',
        'LIVECATTLE' => 'Live Cattle Futures',
        'NICKEL' => 'Nickel Futures',
        'ORANGEJUICE' => 'Orange Juice Futures',
        'RBOB' => 'RBOB Gasoline Futures',
        'RICE' => 'Rough Rice Futures',
        'SBEAN' => 'Soybean Futures',
        'SUGAR' => 'Sugar Futures',
        'WHEAT' => 'Wheat Futures',
        'SOYBEAN' => 'Soybean Futures',
        'KE.CBOT.H2023' => 'Kansas City Wheat Futures March 2023',
        'KE.CBOT.K2023' => 'Kansas City Wheat Futures May 2023',
        'KE.CBOT.N2023' => 'Kansas City Wheat Futures July 2023',
        'KE.CBOT.U2023' => 'Kansas City Wheat Futures September 2023',
    ];

    return $commodityNames[$symbol] ?? null;
}

function resolveIndexDescription($symbol)
{
    static $indexNames = [
        'IMOEX' => 'MOEX Russia Index',
        'RTSI' => 'RTS Index',
        'NASDAQ' => 'NASDAQ Composite Index',
        'SP500' => 'S&P 500 Index',
        'CAC40' => 'CAC 40 Index',
        'DAX30' => 'DAX 30 Index',
        'NIKKEI' => 'Nikkei 225 Index',
        'FTSE100' => 'FTSE 100 Index',
        'DOW' => 'Dow Jones Industrial Average',
        'AU200' => 'S&P/ASX 200 Index',
        'ESP35' => 'IBEX 35 Index',
        'EUR50' => 'Euro Stoxx 50 Index',
        'HSI' => 'Hang Seng Index',
        'NIFTY50' => 'NIFTY 50 Index',
        'NL25' => 'AEX Index',
        'OBX' => 'OBX Total Return Index',
        'OMX' => 'OMX Stockholm 30 Index',
        'OMXC25' => 'OMX Copenhagen 25 Index',
        'RUT' => 'Russell 2000 Index',
        'TX60' => 'S&P/TSX 60 Index',
        'USDX' => 'US Dollar Index',
        'GPW' => 'WIG Index',
        'WIG' => 'Warsaw Stock Exchange WIG Index',
        'MTA' => 'FTSE Italia All-Share Index',
        'FTSEMIB.MI' => 'FTSE MIB Index',
        'NSEI' => 'NSE Nifty 50 Index',
        'SIX' => 'Swiss Market Index',
        'SWISS20' => 'Swiss Market Index 20',
        'TADAWUL' => 'Tadawul All Share Index',
    ];

    return $indexNames[$symbol] ?? null;
}

function resolveNftDescription($symbol)
{
    static $nftNames = [
        'cool-cats-nft' => 'Cool Cats NFT Collection',
        'cyberkongz' => 'CyberKongz NFT Collection',
        'cryptoadz-by-gremplin' => 'CryptoAdz by Gremplin Collection',
        'world-of-women-nft' => 'World of Women NFT Collection',
        'art-blocks-playground' => 'Art Blocks Playground Collection',
        'parallelalpha' => 'Parallel Alpha NFT Collection',
        'clonex' => 'CloneX NFT Collection',
        'azuki' => 'Azuki NFT Collection',
        'bored-ape-kennel-club' => 'Bored Ape Kennel Club Collection',
        'bored-ape-chemistry-club' => 'Bored Ape Chemistry Club Collection',
        'bored-ape-yacht-club' => 'Bored Ape Yacht Club Collection',
        'captainz' => 'Captainz NFT Collection',
        'doodles' => 'Doodles NFT Collection',
        'goblintown' => 'Goblintown NFT Collection',
        'lilpudgys' => 'Lil Pudgys NFT Collection',
        'mayc' => 'Mutant Ape Yacht Club Collection',
        'moonbirds' => 'Moonbirds NFT Collection',
        'otherdeed-for-otherside' => 'Otherdeed for Otherside Collection',
        'pudgypenguins' => 'Pudgy Penguins NFT Collection',
        'rektguy' => 'Rektguy NFT Collection',
        'sandbox' => 'The Sandbox LAND Collection',
        'sappy-seals' => 'Sappy Seals NFT Collection',
        'sewer-passes' => 'Sewer Passes NFT Collection',
        'thepotatoz' => 'The Potatoz NFT Collection',
        'thecaptains' => 'The Captainz NFT Collection',
        'veefriends' => 'VeeFriends NFT Collection',
        'wolf-game' => 'Wolf Game NFT Collection',
        'y00ts' => 'y00ts NFT Collection',
        'degods' => 'DeGods NFT Collection',
        'mutant-hound-collars' => 'Mutant Hound Collars Collection',
        'mutant-hounds' => 'Mutant Hounds Collection',
        'terraforms' => 'Terraforms by Mathcastles Collection',
        'onchainmonkey' => 'OnChainMonkey NFT Collection',
        'rektguy-wrapped' => 'Rektguy Wrapped Collection',
        'apes-r-us' => 'Apes R Us NFT Collection',
        'apes-r-us-genesis' => 'Apes R Us Genesis NFT Collection',
        'beanz' => 'BEANZ Official NFT Collection',
        'cool-pets-nft' => 'Cool Pets NFT Collection',
        'meebits' => 'Meebits NFT Collection',
        'mfers' => 'mfers NFT Collection',
        'nouns' => 'Nouns DAO NFT Collection',
        'cryptopunks' => 'CryptoPunks Collection',
        'moonbirds-oddities' => 'Moonbirds Oddities Collection',
        'chromie-squiggle' => 'Chromie Squiggle by Snowfro',
        'clone-x-murakami' => 'Clone X x Takashi Murakami Collection',
        'renegade-apes' => 'Renegade Apes NFT Collection',
        'quixotic-azuki' => 'Quixotic Azuki Collection',
        'quixotic-apes' => 'Quixotic Apes Collection',
        'degentoonz' => 'DegenToonz NFT Collection',
        'mfer-derivatives' => 'mfer Derivatives Collection',
        'rektguy-derivatives' => 'Rektguy Derivatives Collection',
    ];

    $base = $symbol;
    $quote = null;
    if (str_contains($symbol, '/')) {
        [$base, $quote] = explode('/', $symbol, 2);
    }

    $name = $nftNames[$base] ?? null;
    if ($name === null) {
        $name = formatTitleCase($base) . ' NFT Collection';
    }

    if ($quote !== null) {
        $quote = strtoupper($quote);
        if ($quote === 'USDT') {
            return $name . ' / Tether';
        }
        return $name . ' / ' . $quote;
    }

    return $name;
}

foreach ($sourceFiles as $filePath) {
    if (!file_exists($filePath)) {
        continue;
    }

    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        continue;
    }

    $header = readCsvRow($handle);
    if ($header === false) {
        fclose($handle);
        continue;
    }

    $indexMap = [];
    foreach ($header as $index => $columnName) {
        $normalized = strtolower(trim($columnName));
        $indexMap[$normalized] = $index;
    }

    while (($row = readCsvRow($handle)) !== false) {
        if ($row === [null] || $row === false) {
            continue;
        }

        $symbol = null;
        $name = null;

        foreach (['nasdaq symbol', 'act symbol', 'symbol'] as $key) {
            if (isset($indexMap[$key])) {
                $value = isset($row[$indexMap[$key]]) ? trim($row[$indexMap[$key]]) : '';
                if ($value !== '') {
                    $symbol = $value;
                    break;
                }
            }
        }

        if ($symbol === null || $symbol === '') {
            continue;
        }

        foreach (['security name', 'company name', 'name'] as $key) {
            if (isset($indexMap[$key])) {
                $value = isset($row[$indexMap[$key]]) ? trim($row[$indexMap[$key]]) : '';
                if ($value !== '') {
                    $name = $value;
                    break;
                }
            }
        }

        if ($name === null || $name === '') {
            continue;
        }

        if (!isset($symbolToName[$symbol])) {
            $symbolToName[$symbol] = $name;
        }
    }

    fclose($handle);
}

$handle = fopen($outputFile, 'r');
if ($handle === false) {
    fwrite(STDERR, "Unable to open output file for reading: {$outputFile}\n");
    exit(1);
}

$rows = [];
$header = readCsvRow($handle);
if ($header === false) {
    fclose($handle);
    fwrite(STDERR, "Output file appears to be empty: {$outputFile}\n");
    exit(1);
}

$rows[] = $header;
$symbolIndex = null;
$descriptionIndex = null;
$symbolTypeIndex = null;
$exchangeIndex = null;

foreach ($header as $index => $columnName) {
    $normalized = strtolower(trim($columnName));
    if ($normalized === 'symbol') {
        $symbolIndex = $index;
    } elseif ($normalized === 'description') {
        $descriptionIndex = $index;
    } elseif ($normalized === 'symbol_type') {
        $symbolTypeIndex = $index;
    } elseif ($normalized === 'exchange_name') {
        $exchangeIndex = $index;
    }
}

if ($symbolIndex === null || $descriptionIndex === null) {
    fclose($handle);
    fwrite(STDERR, "Output file must contain 'symbol' and 'description' columns.\n");
    exit(1);
}

while (($row = readCsvRow($handle)) !== false) {
    if ($row === [null]) {
        continue;
    }

    $symbol = isset($row[$symbolIndex]) ? trim($row[$symbolIndex]) : '';
    if ($symbol !== '') {
        $description = $symbolToName[$symbol] ?? null;
        if ($description === null) {
            $description = manualDescriptionLookup($symbol, $manualMappings);
        }

        $symbolType = ($symbolTypeIndex !== null && isset($row[$symbolTypeIndex])) ? trim($row[$symbolTypeIndex]) : '';
        $exchangeName = ($exchangeIndex !== null && isset($row[$exchangeIndex])) ? trim($row[$exchangeIndex]) : '';

        if ($description === null) {
            $lowerType = strtolower($symbolType);
            switch ($lowerType) {
                case 'forex':
                    $description = resolveForexDescription($symbol);
                    break;
                case 'crypto':
                    $description = resolveCryptoDescription($symbol);
                    break;
                case 'commodity':
                    $description = resolveCommodityDescription($symbol);
                    break;
                case 'index':
                    $description = resolveIndexDescription($symbol);
                    break;
                case 'nft':
                    $description = resolveNftDescription($symbol);
                    break;
                default:
                    break;
            }
        }

        if ($description === null && strtolower($symbolType) === 'etf') {
            $description = manualDescriptionLookup($symbol, $manualMappings);
        }

        if ($description === null && strtolower($symbolType) === 'moex') {
            $description = manualDescriptionLookup($symbol, $manualMappings);
        }

        if ($description === null && strtolower($symbolType) === 'tsx') {
            $description = manualDescriptionLookup($symbol, $manualMappings);
        }

        if ($description !== null) {
            $row[$descriptionIndex] = $description;
        }
    }

    $rows[] = $row;
}

fclose($handle);

$handle = fopen($outputFile, 'w');
if ($handle === false) {
    fwrite(STDERR, "Unable to open output file for writing: {$outputFile}\n");
    exit(1);
}

foreach ($rows as $row) {
    writeCsvRow($handle, $row);
}

fclose($handle);

echo "Descriptions updated successfully.\n";
