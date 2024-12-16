<?php
// Function to clear the terminal screen
function clearTerminal() {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        system('cls');
    } else {
        system('clear');
    }
}

// Function to print messages in green color
function printSuccess($message) {
    echo "\033[1;32m$message\033[0m\n";
}

// Function to extract the user ID from the referral URL
function getReferralId($url) {
    if (preg_match('/startapp=f(\d+)/', $url, $matches)) {
        return $matches[1];
    }
    return false;
}

// Function to save the user ID to the specified file
function storeUserId($filePath, $userId) {
    $usersData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
    
    // If user ID already exists
    if (isset($usersData[$userId])) {
        printSuccess("Duplicate entry detected.");
        printSuccess("User ID: {$userId}\nPreviously saved at: {$usersData[$userId]['saved_at']}");
        return $usersData;
    }

    // Add new user entry
    $usersData[$userId] = [
        'tg_id' => $userId,
        'saved_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($filePath, json_encode($usersData, JSON_PRETTY_PRINT));
    printSuccess("User ID saved successfully!");
    return $usersData;
}

// Function to list all saved user IDs
function showAllSavedUsers($usersData) {
    if (empty($usersData)) {
        printSuccess("No user IDs saved.");
    } else {
        printSuccess("\nList of saved user IDs:");
        foreach ($usersData as $id => $data) {
            echo "User ID: $id | Saved at: {$data['saved_at']}\n";
        }
    }
}

// Script execution starts here
clearTerminal();
printSuccess(". Launch Not Pixel");
printSuccess(". Copy the Not Pixel referral link");
printSuccess(". Unlimited accounts supported");

$usersFilePath = 'users.json';

while (true) {
    printSuccess("\nEnter the Not Pixel referral link:");
    $inputLink = trim(fgets(STDIN));

    $userId = getReferralId($inputLink);
    if (!$userId) {
        printSuccess("Error: Invalid referral link! Please try again.");
        continue;
    }

    $usersData = storeUserId($usersFilePath, $userId);

    printSuccess("Would you like to add more referral links? (y/n):");
    $response = strtolower(trim(fgets(STDIN)));

    if ($response !== 'y') {
        break;
    }
}

// Display saved user IDs
clearTerminal();
$usersData = file_exists($usersFilePath) ? json_decode(file_get_contents($usersFilePath), true) : [];
showAllSavedUsers($usersData);

printSuccess("\nThank you for using this script!");
