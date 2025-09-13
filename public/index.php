<?php

use Entity\Collection\GroupCollection;
use Entity\Collection\UserCollection;

$a = UserCollection::getAllUsers();

foreach ($a as $group) {
    echo "". $group->getPassword() ."<br>";
}
