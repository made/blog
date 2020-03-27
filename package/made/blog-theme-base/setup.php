<?php

// This file has to be executed with administrative rights when running on windows!

$target = dirname(__DIR__, 3) . '/vendor/made/blog-theme-base';
$link = dirname(__DIR__, 3) . '/theme/blog-theme-base';

return symlink($target, $link);
