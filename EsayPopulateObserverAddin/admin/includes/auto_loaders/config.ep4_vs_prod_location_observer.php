<?php
/* loads and instantiates observer class within the admin file structure
*/
$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/class.ep4_vs_prod_location_observer.php',
    'classPath' => DIR_WS_CLASSES,
];


$autoLoadConfig[180][] = [
    'autoType' => 'classInstantiate',
    'className' => 'ep4_vs_prod_location',
    'objectName' => 'ep4_vs_prod_location_obs',
];

