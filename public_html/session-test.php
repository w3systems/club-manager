<?php
session_start();

echo "<h1>Simple Session Test</h1>";

if (isset($_POST['test'])) {
    $_SESSION['test_data'] = 'Session working!';
    $_SESSION['timestamp'] = date('Y-m-d H:i:s');
}

if (isset($_POST['clear'])) {
    session_destroy();
    session_start();
}

echo "<h2>Current Session:</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>Session Configuration:</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Cookie Params: " . print_r(session_get_cookie_params(), true);
echo "</pre>";

echo "<form method='post'>";
echo "<button type='submit' name='test'>Set Test Session</button>";
echo "<button type='submit' name='clear'>Clear Session</button>";
echo "</form>";
?>