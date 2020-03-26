<?php
/**
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Get the microtime on "in" as float value in seconds.
$timeMeasureIn = microtime(true);

// Start the output buffer.
ob_start();

// Run the application.
$return = require dirname(__DIR__) . '/public/index.php';

// Get the microtime on "out" as float value in seconds.
$timeMeasureOut = microtime(true);

// Clear the output buffer.
ob_clean();

// Calculate the microtime difference between "in" and "out" in seconds.
$time = $timeMeasureOut - $timeMeasureIn;
// Calculate the microtime difference between "in" and "out" in milliseconds.
$timeMs = $time * 1000;

echo "Total execution time: {$time}s (= {$timeMs}ms).";

return $return;
