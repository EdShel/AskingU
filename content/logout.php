<?php

// Clear authorisation cookies
setcookie("id", "", time() - 1);
setcookie("accessToken", "", time() - 1);

header("Location: /");