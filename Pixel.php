<?php

// Check the operating system and clear the screen accordingly
function clearScreenBasedOnOS() {
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    system($isWindows ? 'cls' : 'clear');
}

// Generate a random User-Agent string for making requests
function generateRandomAgent() {
    $operatingSystems = ['Windows', 'Linux', 'iOS', 'Android'];
    $versions = ['8', '9', '10', '11', '12', '13', '14'];
    $brands = ['Samsung', 'Motorola', 'Xiaomi', 'Huawei', 'OnePlus'];
    
    $os = $operatingSystems[array_rand($operatingSystems)];
    
    if ($os === 'Android') {
        $version = $versions[array_rand($versions)];
        $brand = $brands[array_rand($brands)];
        return "Mozilla/5.0 (Linux; Android $version; $brand) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Mobile Safari/537.36";
    } else {
        return "Mozilla/5.0 ($os NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36";
    }
}

// Display a message in a specified color
function displayMessage($message, $color) {
    return "\033[$color" . "m" . $message . "\033[0m\n";
}

// Color codes
$green = "32";
$red = "31";
$yellow = "33";
$blue = "34";

// Display a welcome banner
function showBanner() {
    global $green;
    $banner = "
     - SAFE SCRIPT -

- Made by : TITANIC
- Telegram: @Titanic_Helper
- Channel: https://t.me/Titanic_Helpers

- Please be patient.

-------------------------------------------------
";
    echo displayMessage($banner, $green);
}

// Verify if the user data file exists
$userFile = 'users.json';
if (!file_exists($userFile)) {
    echo displayMessage("Error: No users found. Please add users by running: php adduser.php\n", $red);
    exit;
}

// Read and decode user data
$userData = json_decode(file_get_contents($userFile), true);
if (!$userData) {
    echo displayMessage("Error: Could not parse users.json.\n", $red);
    exit;
}

// Initialize points for each user
$userPoints = array_fill_keys(array_keys($userData), 0);

// Function to generate a random chat instance ID
function generateChatInstance() {
    return strval(rand(10000000000000, 99999999999999));
}

// Send an API request to the service
function sendRequest($userId, $telegramId) {
    $url = "https://api.adsgram.ai/adv?blockId=4853&tg_id=$telegramId&tg_platform=android&platform=Linux%20aarch64&language=en&chat_type=sender&chat_instance=" . generateChatInstance() . "&top_domain=app.notpx.app";
    
    $userAgent = generateRandomAgent();
    $baseUrl = "https://app.notpx.app/";
    
    $headers = [
        'Host: api.adsgram.ai',
        'Connection: keep-alive', 
        'Cache-Control: max-age=0',
        'sec-ch-ua-platform: "Android"',
        "User-Agent: $userAgent",
        'sec-ch-ua: "Android WebView";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
        'sec-ch-ua-mobile: ?1',
        'Accept: */*',
        'Origin: https://app.notpx.app',
        'X-Requested-With: org.telegram.messenger',
        'Sec-Fetch-Site: cross-site',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Dest: empty',
        "Referer: $baseUrl",
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: en,en-US;q=0.9'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$response, $httpCode, $headers];
}

// Extract reward from the API response
function getRewardFromResponse($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['banner']['trackings'])) {
        foreach ($data['banner']['trackings'] as $tracking) {
            if ($tracking['name'] === 'reward') {
                return $tracking['value'];
            }
        }
    }
    return null;
}

$totalPoints = 0;
$isFirstRun = true;

while (true) {
    clearScreenBasedOnOS();
    showBanner();

    // Show the points if not the first run
    if (!$isFirstRun) {
        foreach ($userData as $userId => $info) {
            echo displayMessage("---> $userId +{$userPoints[$userId]} PX", $green);
        }
        echo displayMessage("Total Pixel Points: +$totalPoints\n", $green);
    }

    // Initialize arrays for rewards and request headers
    $rewardLinks = [];
    $headers = [];

    foreach ($userData as $userId => $info) {
        $telegramId = $info['tg_id'];
        
        echo displayMessage("[ INFO ] Starting process for user $userId...\n", $yellow);
        echo displayMessage("[ PROCESS ] Request sent to $userId...\n", $blue);
        
        sleep(3);
        
        // Send the request and capture the response
        list($response, $httpCode, $reqHeaders) = sendRequest($userId, $telegramId);
        
        if ($httpCode === 200) {
            $reward = getRewardFromResponse($response);
            if ($reward) {
                $rewardLinks[$userId] = $reward;
                $headers[$userId] = $reqHeaders;
                echo displayMessage("[ SUCCESS ] Points added for $userId.\n", $green);
            } else {
                echo displayMessage("[ ERROR ] Limit reached for $userId.\n", $red);
                echo displayMessage("[ SOLUTION ] Use VPN.\n", $yellow);
                continue;
            }
        } elseif ($httpCode === 403) {
            echo displayMessage("[ ERROR ] IP Blocked for $userId.\n", $red);
            echo displayMessage("[ SOLUTION ] Try using VPN.\n", $yellow);
            exit;
        } else {
            if ($httpCode === 400 && strpos($response, 'block_error') !== false) {
                echo displayMessage("[ ERROR ] Ads block detected, ignoring...\n", $red);
                continue;
            }
            echo displayMessage("[ ERROR ] HTTP Code: $httpCode\n", $red);
            continue;
        }
    }

    // Wait for 20 seconds before processing the rewards
    for ($i = 20; $i > 0; $i--) {
        echo "\rWaiting... $i seconds left...";
        sleep(1);
    }
    echo "\n";

    // Process the rewards
    foreach ($rewardLinks as $userId => $rewardLink) {
        echo displayMessage("[ PROCESS ] Sending reward to $userId...\n", $yellow);
        
        $reqHeaders = $headers[$userId];
        
        // Send reward request to the link
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rewardLink);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeaders);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Update the points if successful
        if ($httpCode === 200) {
            $totalPoints += 16;
            $userPoints[$userId] += 16;
            echo displayMessage("[ SUCCESS ] Reward added for $userId (+16 PX)\n", $green);
        } else {
            echo displayMessage("[ ERROR ] Failed for $userId. HTTP Code: $httpCode\n", $red);
        }
    }

    $isFirstRun = false;
}

?>
