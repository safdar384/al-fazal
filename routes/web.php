<?php

/**
 * Main web routes — ERP modules load from bootstrap/app.php (routes/erp_modules.php).
 * Keep "/" only in the auth group there so guests hit auth→login and users go to dashboard
 * without an extra redirect hop.
 */

use Illuminate\Support\Facades\Route;