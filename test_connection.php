<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    print_r($_POST);
} else {
    echo '<form method="POST">
            <input type="text" name="username">
            <input type="password" name="password">
            <button type="submit">Submit</button>
          </form>';
}
?>
