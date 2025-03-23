<?php
require 'db.php';

// ADD PIN NUMBER
if (isset($_POST['add_pin'])) {
    $newPin = $_POST['new_pin'];
    $status = $_POST['status'];

    $insertQuery = "INSERT INTO lockers_pin_hw (pin_number, status) VALUES ('$newPin', '$status')";
    if (!mysqli_query($conn, $insertQuery)) {
        echo "Error: " . mysqli_error($conn);
        exit;
    }
    header("Location: view_pins.php");
}

// UPDATE PIN STATUS
if (isset($_POST['update_pin'])) {
    $pinId = $_POST['lphw_id']; // Correct reference
    $newStatus = $_POST['status'];

    $updateQuery = "UPDATE lockers_pin_hw SET status='$newStatus' WHERE lphw_id='$pinId'";
    if (!mysqli_query($conn, $updateQuery)) {
        echo "Error: " . mysqli_error($conn);
        exit;
    }
    header("Location: view_pins.php");
}

// DELETE PIN NUMBER
if (isset($_GET['delete_pin'])) {
    $pinId = $_GET['delete_pin'];

    $deleteQuery = "DELETE FROM lockers_pin_hw WHERE lphw_id='$pinId'";
    if (!mysqli_query($conn, $deleteQuery)) {
        echo "Error: " . mysqli_error($conn);
        exit;
    }
    header("Location: view_pins.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pin Numbers - Availability</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6 text-green-600">Pin Numbers - Availability</h1>

        <!-- ADD PIN NUMBER FORM -->
        <form method="POST" class="mb-6">
            <div class="flex gap-4">
                <input type="text" name="new_pin" placeholder="New Pin Number" required
                    class="flex-1 border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-green-500">
                <select name="status" required
                    class="border border-gray-300 px-4 py-2 rounded-md focus:ring-2 focus:ring-green-500">
                    <option value="available">Available</option>
                    <option value="assigned">Assigned</option>
                </select>
                <button type="submit" name="add_pin"
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md">
                    ➕ Add Pin
                </button>
            </div>
        </form>

        <!-- PIN NUMBERS TABLE -->
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 rounded-lg">
                <thead class="bg-green-500 text-white">
                    <tr>
                        <th class="py-3 px-4">Pin Number</th>
                        <th class="py-3 px-4">Availability</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM lockers_pin_hw";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $statusClass = ($row['status'] === 'available') 
                                ? 'text-green-500 font-bold' 
                                : 'text-red-500 font-bold';
                            echo "
                            <tr class='border-b border-gray-200'>
                                <td class='py-3 px-4'>{$row['pin_number']}</td>
                                <td class='py-3 px-4 $statusClass'>{$row['status']}</td>
                                <td class='py-3 px-4 flex gap-2'>
                                    <form method='POST'>
                                        <input type='hidden' name='lphw_id' value='{$row['lphw_id']}'>
                                        <select name='status' class='border border-gray-300 px-2 py-1 rounded-md'>
                                            <option value='available'>Available</option>
                                            <option value='assigned'>Assigned</option>
                                        </select>
                                        <button type='submit' name='update_pin'
                                            class='bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md'>
                                            ✏️ Update
                                        </button>
                                    </form>
                                    <a href='?delete_pin={$row['lphw_id']}'
                                        class='bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md'>
                                        ❌ Delete
                                    </a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "
                        <tr>
                            <td colspan='3' class='py-3 px-4 text-center text-gray-500'>No pin numbers found.</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-6">
            <a href="index.php" class="inline-block bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md transition">
                🔙 Back to Home
            </a>
        </div>
    </div>

</body>
</html>
