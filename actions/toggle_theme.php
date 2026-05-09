<?php
// Check current cookie, swap it, and save it for 30 days
$new_theme = (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') ? 'light' : 'dark';
setcookie('theme', $new_theme, time() + (86400 * 30), "/");

// Send them exactly back to the page they clicked it from
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>