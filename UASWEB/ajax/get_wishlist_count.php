<?php
require_once '../config/database.php';

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    echo getWishlistCount($user_id);
} else {
    echo '0';
}
?>
