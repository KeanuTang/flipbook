<?php
namespace flipbook;

function arrayval($a, $n, $v=[]) {
  return isset($a[$n])? $a[$n]: $v;
}