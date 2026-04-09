<?php
// ------------------------------------------------------------
// Logout page
// ------------------------------------------------------------
// This file clears the user session and redirects back to login.
// ------------------------------------------------------------

session_start();
session_unset();
session_destroy();

header("Location: index.php");
exit;
