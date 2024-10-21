<?php
session_start(); // Start the session

// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "bus_booking"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require 'vendor/autoload.php'; // Include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = ''; // Add this line to initialize the message variable

// Retrieve form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$destination = $_POST['destination'] ?? '';
$boarding_point = $_POST['boarding_point'] ?? '';
$travel_date = $_POST['travel_date'] ?? '';
$adults = $_POST['adults'] ?? '';
$children = $_POST['children'] ?? '';
$bus_id = $_POST['bus_id'] ?? '';

$currentDateTime = new DateTime(); // Get current date and time
$selectedTravelDate = new DateTime($travel_date); // Convert user-selected date


// Handle seat reservation if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seat_number'])) {
    if ($selectedTravelDate < $currentDateTime) {
        $message = 'Travel date cannot be in the past. Please select a valid date.'; // Set message
    } else {
        $seat_number = $_POST['seat_number'];
    
    // Check if the seat is already reserved
    $checkSeatSql = "SELECT * FROM reservations WHERE seat_number = ?";
    $checkStmt = $conn->prepare($checkSeatSql);
    $checkStmt->bind_param("i", $seat_number);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        // Prepare the SQL statement for insertion
        $insertSql = "INSERT INTO reservations (name, email, seat_number, destination, boarding_point, travel_date, bus_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
    
        // Check if the prepare statement was successful
        if (!$insertStmt) {
            die("Error preparing SQL: " . $conn->error);
        } else {
            // Output the travel date for debugging
            // echo "Travel Date: " . $travel_date;
    
            // Bind the parameters and execute if the prepare was successful
            $insertStmt->bind_param("ssisssi", $name, $email, $seat_number, $destination, $boarding_point, $travel_date, $bus_id);
    
            if ($insertStmt->execute()) {
                $message = "Seat $seat_number has been reserved!"; // Set message



                $mail = new PHPMailer(true);
                try {
                     $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; 
                        $mail->SMTPAuth = true;
                        $mail->Username = 'emekaisaacisreal@gmail.com'; 
                        $mail->Password = 'oxekwyjnkgpodzgb'; 
                        $mail->Port = 587;                                 


                    $mail->setFrom('emekaisaacisreal@gmail.com', 'GEO SOLUTION');
                        $mail->addAddress($email, $name);   
                        $mail->isHTML(true);                                      // Set email format to HTML
                    $mail->Subject = 'Booking Confirmation';
                    $mail->Body    = "Hello $name,<br><br>Your seat number $seat_number has been successfully reserved for $destination. <br>Boarding Point: $boarding_point<br>Travel Date: $travel_date<br><br>Thank you for choosing us!";

                    $mail->send();
                    // Redirect to Facebook after sending the email
                    header("Location: https://www.facebook.com");
                    exit();
                } catch (Exception $e) {
                    $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $message = "Error executing SQL: " . $insertStmt->error; // Set error message
            }
        }
        // Close the prepared statement outside of the if-else block to ensure it's only closed once
        $insertStmt->close();
    } else {
        $message = "Seat $seat_number is already reserved. Please choose another seat."; // Set message
    }
}    
}


// Fetch bus data based on destination
$sql = "SELECT * FROM buses WHERE destination = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $destination);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Booking Confirmation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
        .bus-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            gap: 40px;
            padding: 10px;
        }
        .bus-image img {
            height: 120px;
            width: 120px;
            border-radius: 5px;
        }
        .bus-details {
            flex-grow: 2;
            margin-left: 20px;
            text-align: left;
        }
        .bus-name {
            font-weight: bold;
            font-size: 30px;
            text-transform: uppercase;
        }
        .price {
            font-size: 1.5em;
        }
        .view-seats {
            background-color: red;
            color: white;
            border: none;
            padding: 17px 32px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .view-seats:hover {
            background-color: darkred;
        }
        .cash {
            color: red;
            font-weight: bold;
            font-size: 15px;
        }  
        .seat-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            position: relative;
            width: 300px;
        }
        .seat {
            display: inline-block;
            width: 40px;
            height: 40px;
            margin: 5px;
            background: green;
            color: white;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
        }
        .reserved {
            background: red;
        }
        .selected {
            background: black; /* Change color for selected seat */
        }
        .close-modal {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
            color: red;
        }
        .reservation-form {
            display: none; /* Initially hidden */
            margin-top: 20px;
        }
        .message {
            color: white; /* Change to your desired color */
            margin-left: 170px;
            border: 2px solid black;
            padding : 5px;
            width: 450px;
            background-color: red;
           
        }
        .header {
            display: flex;
            /* justify-content: space-between; */
            align-items: center;
            gap: 20px;
            padding: 20px 40px;
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            font-size: 16px;
        }
        .header img {
            height: 40px;
        }
        .header nav {
            display: flex;
            align-items: center;
            padding-top: 10px;
            margin-left: auto; 
        }
        .header nav .dropdown {
            position: relative;
            display: inline-block;
        }
        .header nav .dropdown-content {
            display: none;
            position: absolute;
            top: 100%; /* Places the dropdown below the trigger */
            left: 0; /* Aligns it with the left side of the parent */
            background-color: white;
            min-width: 160px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            z-index: 1;
            font-size: 13px;
            
}

        .header nav .dropdown-content a {
            color: blue;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-weight: 500;
            
        }
        .header nav .dropdown-content a:hover {
            background-color: #f5f5f5;
        }
        .header nav .dropdown:hover .dropdown-content {
            display: block;
        }
        .header nav a {
            margin: 0 10px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        .header .sign-in {
            background-color: #ff3b30;
            color: white;
            padding: 8px 13px;
            border-radius: 5px;
            text-decoration: none;
            padding-top: 10px;
        }
        .containers {
            display: flex;
            padding: 50px;
        }
        .sidebar {
            width: 300px;
            height: 320px;
            background-color: white;
            /* border-radius: 10px; */
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
            margin-right: -50px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li {
            padding: 30px 25px;
            border-bottom: 1px solid white;
        }
        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            cursor: pointer;
        }
        .sidebar ul li.active {
            background-color: #ffe6e6;
            border-left: 5px solid #ff3b30;
        }
        .content {
            flex: 1;
        }
        .content h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            display: flex;
           margin-left: 120px;
            align-items: center;
            border-top: 1px solid #e0e0e0;
            margin-top: 80px;
            font-weight: 400;
        }
        .footer .column {
            flex: 1;
        }
        .footer .column h4 {
            font-size: 18px;
            font-weight: 700;
        }
        .footer .column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer .column ul li {
            margin-bottom: 16px;
        }
        .footer .column ul li a {
            text-decoration: none;
            color: #333;
        }
        .footer .social-icons {
            display: flex;
            align-items: center;
        }
        .footer .social-icons a {
            margin: 0 10px;
            color: #333;
            font-size: 20px;
        }
    </style>
</head>
<body>

<div class="header">
   <img alt="GIGM Logo" height="40" src="https://storage.googleapis.com/a1aa/image/CaDZ5MBc9MZpDV1T56Zo3YA3ztlf42eARsIk803eZEYpb8JnA.jpg" width="100"/>
   <nav>
    <div class="dropdown" >
     <a href="#">
      Move Freely
     </a>
     <div class="dropdown-content">
      <a href="#">
      Pick up a Service
      </a>
      <a href="#">
       Hire a bus
      </a>
     </div>
    </div>
    <div class="dropdown">
     <a href="#">
      Do Freely
     </a>
     <div class="dropdown-content">
      <a href="#">
       Bills Payment
      </a>
      <a href="#">
       Enterprise Partner
      </a>
     </div>
    </div>
    <a href="#">
     Suggest Route
    </a>
   </nav>
   <a class="sign-in" href="#">
    Sign In / Sign Up
   </a>
  </div>
  <div class="containers">
   <div class="sidebar">
    <ul>
     <li class="active">
      <a href="#">
       Bookings
      </a>
     </li>
     <li>
      <a href="#">
       Help &amp; Support
      </a>
     </li>
     <li>
      <a href="#">
       Referral
      </a>
     </li>
     <li>
      <a href="#">
       Setting
      </a>
     </li>
    </ul>
   </div>
   <div class="content">
   
<h1>Bus Booking Confirmation</h1>

<div class="container">

<?php if (!empty($message)): ?>
        <div class="message" id ="message-container"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="bus-card">
                <div class="bus-image">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Bus Image">
                </div>
                <div class="bus-details">
                    <p class="bus-name"><?php echo htmlspecialchars($row['name']); ?></p>
                    <b><p>Destination: <?php echo htmlspecialchars($row['destination']); ?> Boarding Point: <?php echo htmlspecialchars($boarding_point); ?></p></b>
                    <p>Departure Time: <?php echo date('H:i', strtotime($row['departure_time'])); ?></p>
                </div>
                <div class="bus-price">
                    <p class="price">₦<?php echo htmlspecialchars($row['price']); ?></p>
                    <p class="cash">Cashback : ₦610</p>
                    <button class="view-seats" onclick="showSeatModal(<?php echo $row['id']; ?>)">View Seats</button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No buses available for this destination.</p>
    <?php endif; ?>

</div>
  <div class="footer">
   <div class="column">
    <h4>
     Company
    </h4>
    <ul>
     <li>
      <a href="#" style="font-size: 16px;">
       About Us
      </a>
     </li>
     <li>
      <a href="#" style="font-size: 16px;">
       Team
      </a>
     </li>
    </ul>
   </div>
   <div class="column">
    <h4>
     Experience
    </h4>
    <ul>
     <li>
      <a href="#" style="font-size: 16px;">
       Contact Us
      </a>
     </li>
     <li>
      <a href="#" style="font-size: 16px;">
       FAQs
      </a>
     </li>
     <li>
      <a href="#" style="font-size: 16px;">
       Find a Terminal
      </a>
     </li>
     <li>
      <a href="#" style="font-size: 16px;">
       Blog
      </a>
     </li>
    </ul>
   </div>
   <div class="column">
    <h4>
     Terms
    </h4>
    <ul>
     <li>
      <a href="#" style="font-size: 16px;">
       Privacy Policy
      </a>
     </li>
     <li>
      <a href="#" style="font-size: 16px;">
       Terms &amp; Conditions
      </a>
     </li>
    </ul>
   </div>
   <div class="column">
    <h4>
     Connect With Us
    </h4>
    <div class="social-icons">
     <a href="#">
      <i class="fab fa-twitter">
      </i>
     </a>
     <a href="#">
      <i class="fab fa-facebook-f">
      </i>
     </a>
     <a href="#">
      <i class="fab fa-instagram">
      </i>
     </a>
    </div>
   </div>
  </div>


<!-- Seat Modal -->
<div id="seat-modal" class="seat-modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeSeatModal()">×</span>
        <h2>Select Your Seats</h2>
        <div id="seat-selection"></div>
        
        <div class="reservation-form" id="reservation-form">
            <h3>Reservation Details</h3>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="destination" value="<?php echo htmlspecialchars($destination); ?>" readonly>
                <input type="text" name="boarding_point" value="<?php echo htmlspecialchars($boarding_point); ?>" readonly>
                <input type="hidden" name="seat_number" id="seat_number" value="">
                <input type="hidden" name="bus_id" id="bus_id" value="">
                <input type="date" name="travel_date" required>

                <button type="submit">Reserve Now</button>
            </form>
        </div>
    </div>
</div>

<script>
    let reservedSeats = []; // Populate this from your database.


    function hideMessage() {
            const messageContainer = document.getElementById('message-container');
            if (messageContainer) {
                messageContainer.style.display = 'none'; // Hide the message
            }
        }

        // Hide the message after 10 seconds
        window.onload = function() {
            setTimeout(hideMessage, 5000); // 10000 milliseconds = 10 seconds
        };

    function showSeatModal(busId) {
        // For demo, assume seat numbers 1-20 are available
        let seatSelection = document.getElementById('seat-selection');
        seatSelection.innerHTML = '';

        for (let i = 1; i <= 20; i++) {
            let seat = document.createElement('div');
            seat.className = 'seat';
            seat.textContent = i;
            seat.onclick = function() {
                selectSeat(i, busId);
            };

            // Check if the seat is reserved and apply the class if so
            if (reservedSeats.includes(i)) {
                seat.classList.add('reserved');
            }
            seatSelection.appendChild(seat);
        }

        document.getElementById('seat-modal').style.display = 'flex';
    }

    function selectSeat(seatNumber, busId) {
        // Mark the seat as selected and show the reservation form
        document.getElementById('seat_number').value = seatNumber;
        document.getElementById('bus_id').value = busId;

        // Change the seat color
        const seats = document.querySelectorAll('.seat');
        seats.forEach(seat => {
            if (seat.textContent == seatNumber) {
                seat.classList.add('selected');
            } else {
                seat.classList.remove('selected');
            }
        });

        document.getElementById('reservation-form').style.display = 'block'; // Show form
    }

    function closeSeatModal() {
        document.getElementById('seat-modal').style.display = 'none';
        document.getElementById('reservation-form').style.display = 'none'; // Hide form
        const seats = document.querySelectorAll('.seat');
        seats.forEach(seat => {
            seat.classList.remove('selected'); // Reset selected seat
        });
    }
</script>
</body>
</html>
